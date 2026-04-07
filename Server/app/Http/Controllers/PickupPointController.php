<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PickupPoint;
use App\Models\PickupPointPayoutRequest;
use App\Models\PickupPointTranslation;
use App\Services\PickupPointPayoutService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PickupPointController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:pickup_point_setup'])->only('index','create','store','show','edit','update','destroy','processPayoutRequest');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $pickup_points = PickupPoint::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $pickup_points = $pickup_points->where('name', 'like', '%'.$sort_search.'%');
        }
        $pickup_points = $pickup_points->paginate(10);
        return view('backend.setup_configurations.pickup_point.index', compact('pickup_points','sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.setup_configurations.pickup_point.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'staff_id' => 'required|exists:staff,id',
            'commission_type' => 'required|in:percent,flat',
            'commission_amount' => 'required|numeric|min:0',
            'return_commission_type' => 'required|in:percent,flat',
            'return_commission_amount' => 'required|numeric|min:0',
            'internal_code' => 'nullable|string|max:50',
            'opening_time' => 'nullable|string|max:50',
            'closing_time' => 'nullable|string|max:50',
            'pickup_hold_days' => 'required|integer|min:1|max:30',
            'payout_frequency_days' => 'required|integer|in:7,15,30',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $pickup_point = new PickupPoint;
        $pickup_point->name = $request->name;
        $pickup_point->address = $request->address;
        $pickup_point->phone = $request->phone;
        $pickup_point->pick_up_status = $request->has('pick_up_status') ? 1 : 0;
        $pickup_point->staff_id = $request->staff_id;
        $pickup_point->commission_type = $request->commission_type;
        $pickup_point->commission_amount = $request->commission_amount;
        $pickup_point->return_commission_type = $request->return_commission_type;
        $pickup_point->return_commission_amount = $request->return_commission_amount;
        $pickup_point->internal_code = $request->internal_code;
        $pickup_point->opening_time = $request->opening_time;
        $pickup_point->closing_time = $request->closing_time;
        $pickup_point->pickup_hold_days = $request->pickup_hold_days;
        $pickup_point->payout_frequency_days = $request->payout_frequency_days;
        $pickup_point->latitude = $request->latitude;
        $pickup_point->longitude = $request->longitude;
        $pickup_point->instructions = $request->instructions;
        $pickup_point->supports_return = $request->has('supports_return') ? 1 : 0;
        $pickup_point->supports_cod = $request->has('supports_cod') ? 1 : 0;
        if ($pickup_point->save()) {

            $pickup_point_translation = PickupPointTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'pickup_point_id' => $pickup_point->id]);
            $pickup_point_translation->name = $request->name;
            $pickup_point_translation->address = $request->address;
            $pickup_point_translation->save();

            flash(translate('PicupPoint has been inserted successfully'))->success();
            return redirect()->route('pick_up_points.index');

        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pickup_point = PickupPoint::with(['staff.user'])->findOrFail($id);
        $ordersQuery = Order::with(['user', 'pickup_point'])
            ->where('shipping_type', 'pickup_point')
            ->where('pickup_point_id', $pickup_point->id);

        $statusCounts = [
            'pending' => (clone $ordersQuery)->where('delivery_status', 'pending')->count(),
            'confirmed' => (clone $ordersQuery)->where('delivery_status', 'confirmed')->count(),
            'picked_up' => (clone $ordersQuery)->where('delivery_status', 'picked_up')->count(),
            'on_the_way' => (clone $ordersQuery)->where('delivery_status', 'on_the_way')->count(),
            'reached' => (clone $ordersQuery)->where('delivery_status', 'reached')->count(),
            'delivered' => (clone $ordersQuery)->where('delivery_status', 'delivered')->count(),
            'returned' => (clone $ordersQuery)->where('delivery_status', 'returned')->count(),
            'cancelled' => (clone $ordersQuery)->where('delivery_status', 'cancelled')->count(),
        ];

        $totalOrders = array_sum($statusCounts);
        $activeOrders = $statusCounts['pending']
            + $statusCounts['confirmed']
            + $statusCounts['picked_up']
            + $statusCounts['on_the_way']
            + $statusCounts['reached'];

        $returnDueOrders = $this->staleReachedOrdersQuery($pickup_point)->count();
        $recentOrders = (clone $ordersQuery)
            ->latest('updated_at')
            ->limit(15)
            ->get();
        $payoutSummary = app(PickupPointPayoutService::class)->payoutSummary($pickup_point);
        $payoutRequests = PickupPointPayoutRequest::where('pickup_point_id', $pickup_point->id)
            ->with('processor')
            ->latest('id')
            ->limit(15)
            ->get();

        $latestActivityAt = (clone $ordersQuery)->max('updated_at');
        $latestReachedAt = (clone $ordersQuery)->where('delivery_status', 'reached')->max('delivery_history_date');
        $latestDeliveredAt = (clone $ordersQuery)->where('delivery_status', 'delivered')->max('delivered_date');
        $deliveredOrReturned = $statusCounts['delivered'] + $statusCounts['returned'];

        $workflowSteps = [
            [
                'key' => 'upcoming',
                'label' => translate('Upcoming'),
                'description' => translate('Pending and confirmed orders waiting for pickup processing'),
                'count' => $statusCounts['pending'] + $statusCounts['confirmed'],
            ],
            [
                'key' => 'picked_up',
                'label' => translate('Picked Up'),
                'description' => translate('Orders already collected by the pickup point'),
                'count' => $statusCounts['picked_up'],
            ],
            [
                'key' => 'on_the_way',
                'label' => translate('On The Way'),
                'description' => translate('Orders being moved to final pickup readiness'),
                'count' => $statusCounts['on_the_way'],
            ],
            [
                'key' => 'reached',
                'label' => translate('Reached'),
                'description' => translate('Orders ready for customer pickup'),
                'count' => $statusCounts['reached'],
            ],
            [
                'key' => 'delivered',
                'label' => translate('Delivered'),
                'description' => translate('Orders successfully handed over to customers'),
                'count' => $statusCounts['delivered'],
            ],
            [
                'key' => 'returned',
                'label' => translate('Returned'),
                'description' => translate('Orders sent back after pickup deadline or failed handover'),
                'count' => $statusCounts['returned'],
            ],
        ];

        return view('backend.setup_configurations.pickup_point.show', compact(
            'pickup_point',
            'statusCounts',
            'totalOrders',
            'activeOrders',
            'returnDueOrders',
            'recentOrders',
            'payoutSummary',
            'payoutRequests',
            'latestActivityAt',
            'latestReachedAt',
            'latestDeliveredAt',
            'deliveredOrReturned',
            'workflowSteps'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang           = $request->lang;
        $pickup_point   = PickupPoint::findOrFail($id);
        return view('backend.setup_configurations.pickup_point.edit', compact('pickup_point','lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'staff_id' => 'required|exists:staff,id',
            'commission_type' => 'required|in:percent,flat',
            'commission_amount' => 'required|numeric|min:0',
            'return_commission_type' => 'required|in:percent,flat',
            'return_commission_amount' => 'required|numeric|min:0',
            'internal_code' => 'nullable|string|max:50',
            'opening_time' => 'nullable|string|max:50',
            'closing_time' => 'nullable|string|max:50',
            'pickup_hold_days' => 'required|integer|min:1|max:30',
            'payout_frequency_days' => 'required|integer|in:7,15,30',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $pickup_point = PickupPoint::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $pickup_point->name = $request->name;
            $pickup_point->address = $request->address;
        }

        $pickup_point->phone = $request->phone;
        $pickup_point->pick_up_status = $request->has('pick_up_status') ? 1 : 0;
        $pickup_point->staff_id = $request->staff_id;
        $pickup_point->commission_type = $request->commission_type;
        $pickup_point->commission_amount = $request->commission_amount;
        $pickup_point->return_commission_type = $request->return_commission_type;
        $pickup_point->return_commission_amount = $request->return_commission_amount;
        $pickup_point->internal_code = $request->internal_code;
        $pickup_point->opening_time = $request->opening_time;
        $pickup_point->closing_time = $request->closing_time;
        $pickup_point->pickup_hold_days = $request->pickup_hold_days;
        $pickup_point->payout_frequency_days = $request->payout_frequency_days;
        $pickup_point->latitude = $request->latitude;
        $pickup_point->longitude = $request->longitude;
        $pickup_point->instructions = $request->instructions;
        $pickup_point->supports_return = $request->has('supports_return') ? 1 : 0;
        $pickup_point->supports_cod = $request->has('supports_cod') ? 1 : 0;
        if ($pickup_point->save()) {

            $pickup_point_translation = PickupPointTranslation::firstOrNew(['lang' => $request->lang,  'pickup_point_id' => $pickup_point->id]);
            $pickup_point_translation->name = $request->name;
            $pickup_point_translation->address = $request->address;
            $pickup_point_translation->save();

            flash(translate('PicupPoint has been updated successfully'))->success();
            return redirect()->route('pick_up_points.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pickup_point = PickupPoint::findOrFail($id);
        $pickup_point->pickup_point_translations()->delete();

        if(PickupPoint::destroy($id)){
            flash(translate('PicupPoint has been deleted successfully'))->success();
            return redirect()->route('pick_up_points.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function processPayoutRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $payoutRequest = PickupPointPayoutRequest::with('pickupPoint')->findOrFail($id);
        if ((int) $payoutRequest->status !== 0) {
            flash(translate('This payout request has already been processed.'))->warning();
            return back();
        }

        if ($request->status === 'approved') {
            $summary = app(PickupPointPayoutService::class)->payoutSummary($payoutRequest->pickupPoint);
            if ((float) $payoutRequest->amount > (float) ($summary['current_balance'] ?? 0)) {
                flash(translate('Payout amount is larger than the current pickup point balance.'))->error();
                return back();
            }

            $payoutRequest->status = 1;
        } else {
            $payoutRequest->status = 2;
        }

        $payoutRequest->payment_method = $request->payment_method;
        $payoutRequest->payment_reference = $request->payment_reference;
        $payoutRequest->admin_note = $request->admin_note;
        $payoutRequest->processed_at = now();
        $payoutRequest->processed_by = Auth::id();
        $payoutRequest->save();

        flash(translate('Pickup point payout request processed successfully.'))->success();
        return back();
    }

    protected function staleReachedOrdersQuery(PickupPoint $pickupPoint)
    {
        $cutoffDate = Carbon::today()->subDays($pickupPoint->holdDays())->toDateString();

        return Order::where('shipping_type', 'pickup_point')
            ->where('pickup_point_id', $pickupPoint->id)
            ->where('delivery_status', 'reached')
            ->where(function ($query) use ($cutoffDate) {
                $query->whereDate('delivery_history_date', '<=', $cutoffDate)
                    ->orWhere(function ($fallbackQuery) use ($cutoffDate) {
                        $fallbackQuery->whereNull('delivery_history_date')
                            ->whereDate('updated_at', '<=', $cutoffDate);
                    });
            });
    }
}
