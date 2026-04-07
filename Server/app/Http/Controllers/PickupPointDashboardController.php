<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PickupPointPayoutRequest;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\PickupPointPayoutService;
use App\Utility\EmailUtility;
use App\Utility\NotificationUtility;
use App\Utility\SmsUtility;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PickupPointDashboardController extends Controller
{
    public function dashboard()
    {
        return view('pickup_points.dashboard');
    }

    public function dashboardSummary()
    {
        $html = view('pickup_points.partials.dashboard_content', $this->getDashboardData())->render();

        return response()->json([
            'result' => true,
            'html' => $html,
            'generated_at' => now()->format('d M, Y h:i A'),
        ]);
    }

    public function upcomingOrders()
    {
        $upcoming_orders = $this->baseOrderQuery()
            ->where('delivery_status', 'confirmed')
            ->paginate(10);

        return view('pickup_points.upcoming_orders', compact('upcoming_orders'));
    }

    public function pickupOrders()
    {
        $pickup_orders = $this->baseOrderQuery()
            ->where('delivery_status', 'picked_up')
            ->paginate(10);

        return view('pickup_points.pickup_orders', compact('pickup_orders'));
    }

    public function onTheWayOrders()
    {
        $on_the_way_orders = $this->baseOrderQuery()
            ->where('delivery_status', 'on_the_way')
            ->paginate(10);

        return view('pickup_points.on_the_way_orders', compact('on_the_way_orders'));
    }

    public function reachedOrders()
    {
        $reached_orders = $this->baseOrderQuery()
            ->where('delivery_status', 'reached')
            ->paginate(10);

        return view('pickup_points.reached_orders', compact('reached_orders'));
    }

    public function completedOrders()
    {
        $completed_orders = $this->baseOrderQuery()
            ->where('delivery_status', 'delivered')
            ->paginate(10);

        return view('pickup_points.completed_orders', compact('completed_orders'));
    }

    public function returnOrders()
    {
        $return_orders = $this->baseOrderQuery()
            ->where('delivery_status', 'returned')
            ->paginate(10);

        return view('pickup_points.return_orders', compact('return_orders'));
    }

    public function payouts()
    {
        $pickupPoint = $this->pickupPoint();
        $payoutSummary = app(PickupPointPayoutService::class)->payoutSummary($pickupPoint);
        $payoutRequests = PickupPointPayoutRequest::where('pickup_point_id', $pickupPoint->id)
            ->latest('id')
            ->paginate(10);

        return view('pickup_points.payouts', compact('pickupPoint', 'payoutSummary', 'payoutRequests'));
    }

    public function updatePayoutInfo(Request $request)
    {
        $request->validate([
            'payout_method' => 'required|in:bank,mobile_wallet,manual',
            'payout_account_name' => 'required|string|max:255',
            'payout_account_number' => 'nullable|string|max:255',
            'payout_bank_name' => 'nullable|string|max:255',
            'payout_branch_name' => 'nullable|string|max:255',
            'payout_routing_number' => 'nullable|string|max:255',
            'payout_mobile_wallet_type' => 'nullable|string|max:255',
            'payout_mobile_wallet_number' => 'nullable|string|max:255',
            'payout_notes' => 'nullable|string|max:1000',
        ]);

        $pickupPoint = $this->pickupPoint();
        $pickupPoint->payout_method = $request->payout_method;
        $pickupPoint->payout_account_name = $request->payout_account_name;
        $pickupPoint->payout_account_number = $request->payout_account_number;
        $pickupPoint->payout_bank_name = $request->payout_bank_name;
        $pickupPoint->payout_branch_name = $request->payout_branch_name;
        $pickupPoint->payout_routing_number = $request->payout_routing_number;
        $pickupPoint->payout_mobile_wallet_type = $request->payout_mobile_wallet_type;
        $pickupPoint->payout_mobile_wallet_number = $request->payout_mobile_wallet_number;
        $pickupPoint->payout_notes = $request->payout_notes;
        $pickupPoint->save();

        flash(translate('Payout information updated successfully.'))->success();
        return back();
    }

    public function storePayoutRequest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'message' => 'nullable|string|max:1000',
        ]);

        $pickupPoint = $this->pickupPoint();
        $payoutService = app(PickupPointPayoutService::class);
        $eligibility = $payoutService->canRequest($pickupPoint);

        if (empty($pickupPoint->payout_method) || empty($pickupPoint->payout_account_name)) {
            flash(translate('Please save payout information before sending a payout request.'))->warning();
            return back();
        }

        if (!($eligibility['allowed'] ?? false)) {
            flash($eligibility['message'] ?? translate('Payout request is not available right now.'))->warning();
            return back();
        }

        $requestableBalance = $payoutService->availableForRequest($pickupPoint);
        if ((float) $request->amount > $requestableBalance) {
            flash(translate('Requested amount is larger than the requestable balance.'))->error();
            return back();
        }

        $payoutRequest = new PickupPointPayoutRequest;
        $payoutRequest->pickup_point_id = $pickupPoint->id;
        $payoutRequest->amount = $request->amount;
        $payoutRequest->status = 0;
        $payoutRequest->account_snapshot = json_encode($this->payoutAccountSnapshot($pickupPoint));
        $payoutRequest->message = $request->message;
        $payoutRequest->requested_at = now();
        $payoutRequest->save();

        flash(translate('Pickup point payout request submitted successfully.'))->success();
        return back();
    }

    public function orderDetail($id)
    {
        $order = $this->findManagedOrder(decrypt($id));

        return view('pickup_points.order_detail', compact('order'));
    }

    public function updateDeliveryStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|in:picked_up,on_the_way,reached,delivered,returned',
        ]);

        $order = $this->findManagedOrder($request->order_id);
        $currentStatus = $order->delivery_status;
        $nextStatus = $request->status;

        $allowedTransitions = [
            'confirmed' => ['picked_up'],
            'picked_up' => ['on_the_way'],
            'on_the_way' => ['reached'],
            'reached' => ['delivered', 'returned'],
        ];

        if (!in_array($nextStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            return response()->json([
                'result' => false,
                'message' => translate('Invalid pickup order status transition.'),
            ], 422);
        }

        if ($nextStatus === 'delivered' && $this->isReturnDue($order)) {
            return response()->json([
                'result' => false,
                'message' => translate('Pickup window has expired for this order. Please return it instead.'),
            ], 422);
        }

        $order->delivery_viewed = '0';
        $order->delivery_status = $nextStatus;
        if ($nextStatus === 'reached') {
            $order->delivery_history_date = now();
        }
        if ($nextStatus === 'delivered') {
            $order->delivered_date = now();
            if ($order->payment_type === 'cash_on_delivery') {
                $order->payment_status = 'paid';
            }
        }
        if ($nextStatus === 'returned') {
            $order->delivered_date = null;
        }
        $order->save();

        foreach ($order->orderDetails as $orderDetail) {
            $orderDetail->delivery_status = $nextStatus;
            if ($nextStatus === 'delivered' && $order->payment_type === 'cash_on_delivery') {
                $orderDetail->payment_status = 'paid';
            }
            if ($nextStatus === 'returned') {
                product_restock($orderDetail);
            }
            $orderDetail->save();

            if (
                $nextStatus === 'returned' &&
                addon_is_activated('affiliate_system') &&
                $orderDetail->product_referral_code
            ) {
                $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();
                if ($referred_by_user) {
                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, 0, $orderDetail->quantity);
                }
            }
        }

        if ($nextStatus === 'delivered' && $order->payment_type === 'cash_on_delivery' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
            $order->commission_calculated = 1;
            $order->save();
        }

        if ($nextStatus === 'returned' && $order->payment_type === 'wallet') {
            $user = $order->user;
            if ($user) {
                $user->balance += $order->grand_total;
                $user->save();
            }
        }

        EmailUtility::order_email($order, $nextStatus);

        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()?->status == 1) {
            try {
                SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }

        NotificationUtility::sendNotification($order, $nextStatus);

        if (get_setting('google_firebase') == 1 && optional($order->user)->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = $nextStatus === 'reached'
                ? 'Order reached pickup point !'
                : 'Order updated !';
            $statusLabel = str_replace('_', ' ', $order->delivery_status);
            $request->text = $nextStatus === 'reached'
                ? "Your order {$order->code} is ready for pickup."
                : "Your order {$order->code} has been {$statusLabel}";
            $request->type = 'order';
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Delivery status has been updated'),
        ]);
    }

    private function getDashboardData(): array
    {
        $query = $this->baseOrderQuery();

        return [
            'pickup_point' => $this->pickupPoint(),
            'upcoming_count' => (clone $query)->where('delivery_status', 'confirmed')->count(),
            'pickup_count' => (clone $query)->where('delivery_status', 'picked_up')->count(),
            'on_the_way_count' => (clone $query)->where('delivery_status', 'on_the_way')->count(),
            'reached_count' => (clone $query)->where('delivery_status', 'reached')->count(),
            'completed_count' => (clone $query)->where('delivery_status', 'delivered')->count(),
            'return_count' => (clone $query)->where('delivery_status', 'returned')->count(),
            'earning_summaries' => $this->getEarningSummaries(),
            'return_due_count' => $this->staleReachedOrdersQuery()->count(),
            'return_due_orders' => $this->staleReachedOrdersQuery()->limit(3)->get(),
        ];
    }

    private function getEarningSummaries(): array
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();
        $lastMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        return [
            [
                'label' => translate('Today'),
                'delivery_earning' => $this->calculateEarningsForStatus('delivered', $todayStart, $todayEnd),
                'return_earning' => $this->calculateEarningsForStatus('returned', $todayStart, $todayEnd),
            ],
            [
                'label' => translate('Last 7 Days'),
                'delivery_earning' => $this->calculateEarningsForStatus('delivered', $sevenDaysAgo, $todayEnd),
                'return_earning' => $this->calculateEarningsForStatus('returned', $sevenDaysAgo, $todayEnd),
            ],
            [
                'label' => translate('Last Month'),
                'delivery_earning' => $this->calculateEarningsForStatus('delivered', $lastMonthStart, $lastMonthEnd),
                'return_earning' => $this->calculateEarningsForStatus('returned', $lastMonthStart, $lastMonthEnd),
            ],
            [
                'label' => translate('Total'),
                'delivery_earning' => $this->calculateEarningsForStatus('delivered'),
                'return_earning' => $this->calculateEarningsForStatus('returned'),
            ],
        ];
    }

    private function calculateEarningsForStatus(string $status, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = $this->baseOrderQuery()->where('delivery_status', $status);

        if ($from && $to) {
            if ($status === 'delivered') {
                $query->whereBetween('delivered_date', [$from, $to]);
            } else {
                $query->whereBetween('updated_at', [$from, $to]);
            }
        }

        return $query->get()->sum(function ($order) use ($status) {
            return $this->calculateOrderEarning($order, $status);
        });
    }

    private function calculateOrderEarning(Order $order, string $status): float
    {
        $pickupPoint = $this->pickupPoint();

        if ($status === 'returned') {
            $commissionType = $pickupPoint->return_commission_type ?? 'percent';
            $commissionAmount = (float) ($pickupPoint->return_commission_amount ?? 0);
        } else {
            $commissionType = $pickupPoint->commission_type ?? 'percent';
            $commissionAmount = (float) ($pickupPoint->commission_amount ?? 0);
        }

        if ($commissionType === 'flat') {
            return $commissionAmount;
        }

        return ((float) $order->grand_total * $commissionAmount) / 100;
    }

    private function pickupPoint()
    {
        return Auth::user()->staff->pick_up_point;
    }

    private function payoutAccountSnapshot($pickupPoint): array
    {
        return [
            'payout_method' => $pickupPoint->payout_method,
            'payout_account_name' => $pickupPoint->payout_account_name,
            'payout_account_number' => $pickupPoint->payout_account_number,
            'payout_bank_name' => $pickupPoint->payout_bank_name,
            'payout_branch_name' => $pickupPoint->payout_branch_name,
            'payout_routing_number' => $pickupPoint->payout_routing_number,
            'payout_mobile_wallet_type' => $pickupPoint->payout_mobile_wallet_type,
            'payout_mobile_wallet_number' => $pickupPoint->payout_mobile_wallet_number,
            'payout_notes' => $pickupPoint->payout_notes,
        ];
    }

    private function baseOrderQuery()
    {
        return Order::where('shipping_type', 'pickup_point')
            ->where('pickup_point_id', $this->pickupPoint()->id)
            ->orderByDesc('id');
    }

    private function findManagedOrder(int $orderId): Order
    {
        return $this->baseOrderQuery()->where('id', $orderId)->firstOrFail();
    }

    private function staleReachedOrdersQuery(): Builder
    {
        $cutoffDate = Carbon::today()->subDays($this->pickupPoint()->holdDays())->toDateString();

        return $this->baseOrderQuery()
            ->where('delivery_status', 'reached')
            ->where(function (Builder $query) use ($cutoffDate) {
                $query->whereDate('delivery_history_date', '<=', $cutoffDate)
                    ->orWhere(function (Builder $fallbackQuery) use ($cutoffDate) {
                        $fallbackQuery->whereNull('delivery_history_date')
                            ->whereDate('updated_at', '<=', $cutoffDate);
                    });
            });
    }

    private function isReturnDue(Order $order): bool
    {
        $reachedAt = $order->delivery_history_date ?: $order->updated_at;

        if (!$reachedAt) {
            return false;
        }

        return Carbon::parse($reachedAt)->startOfDay()->addDays($this->pickupPoint()->holdDays())->startOfDay()->lte(Carbon::today());
    }
}
