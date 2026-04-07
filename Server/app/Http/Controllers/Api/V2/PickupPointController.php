<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\AffiliateController;
use App\Http\Resources\V2\DeliveryBoyPurchaseHistoryMiniCollection;
use App\Http\Resources\V2\PurchaseHistoryCollection;
use App\Http\Resources\V2\PurchaseHistoryItemsCollection;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PickupPoint;
use App\Models\PickupPointPayoutRequest;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\OrderDeliveryVerificationService;
use App\Services\PickupPointPayoutService;
use App\Utility\EmailUtility;
use App\Utility\NotificationUtility;
use App\Utility\SmsUtility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class PickupPointController extends Controller
{
    protected OrderDeliveryVerificationService $orderDeliveryVerificationService;

    public function __construct()
    {
        $this->orderDeliveryVerificationService = new OrderDeliveryVerificationService();
    }

    public function dashboard_summary($id)
    {
        $pickupPoint = $this->pickupPointForUserId((int) $id);
        $query = $this->baseOrderQuery($pickupPoint->id);
        $todayEarning = $this->calculateEarningsForStatus($pickupPoint->id, 'delivered', Carbon::today(), Carbon::today()->endOfDay())
            + $this->calculateEarningsForStatus($pickupPoint->id, 'returned', Carbon::today(), Carbon::today()->endOfDay());
        $totalEarning = $this->calculateEarningsForStatus($pickupPoint->id, 'delivered')
            + $this->calculateEarningsForStatus($pickupPoint->id, 'returned');

        return response()->json([
            'pickup_point' => [
                'id' => $pickupPoint->id,
                'name' => $pickupPoint->getTranslation('name'),
                'address' => $pickupPoint->getTranslation('address'),
                'phone' => $pickupPoint->phone,
                'internal_code' => $pickupPoint->internal_code,
                'opening_time' => $pickupPoint->opening_time,
                'closing_time' => $pickupPoint->closing_time,
                'working_hours' => $pickupPoint->workingHoursLabel(),
                'pickup_hold_days' => $pickupPoint->holdDays(),
                'instructions' => $pickupPoint->instructions,
                'supports_return' => $pickupPoint->supportsReturn(),
                'supports_cod' => $pickupPoint->supportsCod(),
                'latitude' => $pickupPoint->latitude,
                'longitude' => $pickupPoint->longitude,
            ],
            'upcoming_orders' => (clone $query)->whereIn('delivery_status', ['pending', 'confirmed'])->count(),
            'picked_up_orders' => (clone $query)->where('delivery_status', 'picked_up')->count(),
            'on_the_way_orders' => (clone $query)->where('delivery_status', 'on_the_way')->count(),
            'reached_orders' => (clone $query)->where('delivery_status', 'reached')->count(),
            'completed_orders' => (clone $query)->where('delivery_status', 'delivered')->count(),
            'return_orders' => (clone $query)->where('delivery_status', 'returned')->count(),
            'completed_delivery' => (clone $query)->where('delivery_status', 'delivered')->count(),
            'pending_delivery' => (clone $query)->whereIn('delivery_status', ['pending', 'confirmed', 'picked_up', 'on_the_way', 'reached'])->count(),
            'total_collection' => format_price(0),
            'total_earning' => format_price($totalEarning),
            'cancelled' => (clone $query)->where('delivery_status', 'returned')->count(),
            'on_the_way' => (clone $query)->where('delivery_status', 'on_the_way')->count(),
            'picked' => (clone $query)->where('delivery_status', 'picked_up')->count(),
            'assigned' => (clone $query)->whereIn('delivery_status', ['pending', 'confirmed'])->count(),
            'reached' => (clone $query)->where('delivery_status', 'reached')->count(),
            'today_earning' => format_price($todayEarning),
            'earning_summary' => $this->earningSummaryPayload($pickupPoint->id),
            'return_due_orders_count' => $this->staleReachedOrdersQuery($pickupPoint->id)->count(),
            'return_due_orders' => $this->returnDueOrdersPayload($pickupPoint->id),
        ]);
    }

    public function upcoming_orders($id)
    {
        return new DeliveryBoyPurchaseHistoryMiniCollection(
            $this->baseOrderQuery($this->pickupPointForUserId((int) $id)->id)
                ->whereIn('delivery_status', ['pending', 'confirmed'])
                ->paginate(10)
        );
    }

    public function picked_up_orders($id)
    {
        return new DeliveryBoyPurchaseHistoryMiniCollection(
            $this->baseOrderQuery($this->pickupPointForUserId((int) $id)->id)
                ->where('delivery_status', 'picked_up')
                ->paginate(10)
        );
    }

    public function on_the_way_orders($id)
    {
        return new DeliveryBoyPurchaseHistoryMiniCollection(
            $this->baseOrderQuery($this->pickupPointForUserId((int) $id)->id)
                ->where('delivery_status', 'on_the_way')
                ->paginate(10)
        );
    }

    public function reached_orders($id)
    {
        return new DeliveryBoyPurchaseHistoryMiniCollection(
            $this->baseOrderQuery($this->pickupPointForUserId((int) $id)->id)
                ->where('delivery_status', 'reached')
                ->paginate(10)
        );
    }

    public function completed_orders($id)
    {
        return new DeliveryBoyPurchaseHistoryMiniCollection(
            $this->baseOrderQuery($this->pickupPointForUserId((int) $id)->id)
                ->where('delivery_status', 'delivered')
                ->paginate(10)
        );
    }

    public function returned_orders($id)
    {
        return new DeliveryBoyPurchaseHistoryMiniCollection(
            $this->baseOrderQuery($this->pickupPointForUserId((int) $id)->id)
                ->where('delivery_status', 'returned')
                ->paginate(10)
        );
    }

    public function payout_summary($id)
    {
        $pickupPoint = $this->pickupPointForUserId((int) $id);
        $summary = app(PickupPointPayoutService::class)->payoutSummary($pickupPoint);

        return response()->json([
            'result' => true,
            'pickup_point' => [
                'id' => $pickupPoint->id,
                'name' => $pickupPoint->getTranslation('name'),
                'payout_method' => $pickupPoint->payout_method,
                'payout_account_name' => $pickupPoint->payout_account_name,
                'payout_account_number' => $pickupPoint->payout_account_number,
                'payout_bank_name' => $pickupPoint->payout_bank_name,
                'payout_branch_name' => $pickupPoint->payout_branch_name,
                'payout_routing_number' => $pickupPoint->payout_routing_number,
                'payout_mobile_wallet_type' => $pickupPoint->payout_mobile_wallet_type,
                'payout_mobile_wallet_number' => $pickupPoint->payout_mobile_wallet_number,
                'payout_notes' => $pickupPoint->payout_notes,
                'payout_frequency_days' => (int) ($pickupPoint->payout_frequency_days ?? 7),
            ],
            'summary' => [
                'total_earned' => format_price($summary['total_earned']),
                'approved_payout_total' => format_price($summary['approved_payout_total']),
                'pending_payout_total' => format_price($summary['pending_payout_total']),
                'current_balance' => format_price($summary['current_balance']),
                'requestable_balance' => format_price($summary['requestable_balance']),
                'current_balance_value' => $summary['current_balance'],
                'requestable_balance_value' => $summary['requestable_balance'],
                'payout_frequency_days' => $summary['payout_frequency_days'],
                'next_eligible_at' => $summary['next_eligible_at'],
                'can_request' => $summary['can_request'],
                'eligibility_message' => $summary['eligibility_message'],
            ],
        ]);
    }

    public function payout_requests($id)
    {
        $pickupPoint = $this->pickupPointForUserId((int) $id);
        $requests = PickupPointPayoutRequest::where('pickup_point_id', $pickupPoint->id)
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'data' => collect($requests->items())->map(function (PickupPointPayoutRequest $request) {
                return [
                    'id' => $request->id,
                    'amount' => format_price($request->amount),
                    'amount_value' => $request->amount,
                    'status' => (int) $request->status,
                    'status_label' => $request->statusLabel(),
                    'message' => $request->message,
                    'admin_note' => $request->admin_note,
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'requested_at' => optional($request->requested_at ?: $request->created_at)->format('d-m-Y h:i A'),
                    'processed_at' => optional($request->processed_at)->format('d-m-Y h:i A'),
                ];
            })->values(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
            'success' => true,
            'status' => 200,
        ]);
    }

    public function update_payout_info(Request $request)
    {
        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());
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

        return response()->json([
            'result' => true,
            'message' => translate('Payout information updated successfully.'),
        ]);
    }

    public function store_payout_request(Request $request)
    {
        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'message' => 'nullable|string|max:1000',
        ]);

        $payoutService = app(PickupPointPayoutService::class);
        $eligibility = $payoutService->canRequest($pickupPoint);
        if (empty($pickupPoint->payout_method) || empty($pickupPoint->payout_account_name)) {
            return response()->json([
                'result' => false,
                'message' => translate('Please save payout information before sending a payout request.'),
            ], 422);
        }

        if (!($eligibility['allowed'] ?? false)) {
            return response()->json([
                'result' => false,
                'message' => $eligibility['message'] ?? translate('Payout request is not available right now.'),
            ], 422);
        }

        $requestableBalance = $payoutService->availableForRequest($pickupPoint);
        if ((float) $request->amount > $requestableBalance) {
            return response()->json([
                'result' => false,
                'message' => translate('Requested amount is larger than the requestable balance.'),
            ], 422);
        }

        $payoutRequest = new PickupPointPayoutRequest;
        $payoutRequest->pickup_point_id = $pickupPoint->id;
        $payoutRequest->amount = $request->amount;
        $payoutRequest->status = 0;
        $payoutRequest->account_snapshot = json_encode([
            'payout_method' => $pickupPoint->payout_method,
            'payout_account_name' => $pickupPoint->payout_account_name,
            'payout_account_number' => $pickupPoint->payout_account_number,
            'payout_bank_name' => $pickupPoint->payout_bank_name,
            'payout_branch_name' => $pickupPoint->payout_branch_name,
            'payout_routing_number' => $pickupPoint->payout_routing_number,
            'payout_mobile_wallet_type' => $pickupPoint->payout_mobile_wallet_type,
            'payout_mobile_wallet_number' => $pickupPoint->payout_mobile_wallet_number,
            'payout_notes' => $pickupPoint->payout_notes,
        ]);
        $payoutRequest->message = $request->message;
        $payoutRequest->requested_at = now();
        $payoutRequest->save();

        return response()->json([
            'result' => true,
            'message' => translate('Pickup point payout request submitted successfully.'),
        ]);
    }

    public function change_delivery_status(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|integer|required_without:delivery_verification_code',
            'status' => 'required|in:picked_up,on_the_way,reached,delivered,returned',
            'delivery_verification_code' => 'nullable|string|required_without:order_id',
        ]);

        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());
        $order = $this->resolveManagedOrderForStatusChange($pickupPoint->id, $request);
        $currentStatus = $order->delivery_status;
        $nextStatus = $request->status;

        $allowedTransitions = [
            'pending' => ['picked_up'],
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

        if ($nextStatus === 'returned' && !$this->isReturnAvailableForOrder($order)) {
            return response()->json([
                'result' => false,
                'message' => translate('Return is not available for this order.'),
            ], 422);
        }

        if ($request->filled('delivery_verification_code')) {
            $verification = $this->orderDeliveryVerificationService->verify(
                $order,
                $request->delivery_verification_code,
                auth()->user(),
                'pickup_app'
            );

            if (!($verification['success'] ?? false)) {
                return response()->json([
                    'result' => false,
                    'message' => $verification['message'] ?? translate('Delivery verification failed.'),
                ], $verification['status'] ?? 422);
            }
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
                $referredByUser = User::where('referral_code', $orderDetail->product_referral_code)->first();
                if ($referredByUser) {
                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referredByUser->id, 0, 0, 0, $orderDetail->quantity);
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

        $smsTemplate = SmsTemplate::where('identifier', 'delivery_status_change')->first();
        if (addon_is_activated('otp_system') && optional($smsTemplate)->status == 1) {
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
            'message' => translate('Delivery status changed to ') . ucwords(str_replace('_', ' ', $nextStatus)),
        ]);
    }

    public function earning_summary($id)
    {
        $this->pickupPointForUserId((int) $id);

        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();

        $today = $this->calculateEarningsForStatus((int) auth()->user()->staff->pick_up_point->id, 'delivered', $todayStart, $todayEnd)
            + $this->calculateEarningsForStatus((int) auth()->user()->staff->pick_up_point->id, 'returned', $todayStart, $todayEnd);
        $yesterday = $this->calculateEarningsForStatus((int) auth()->user()->staff->pick_up_point->id, 'delivered', $yesterdayStart, $yesterdayEnd)
            + $this->calculateEarningsForStatus((int) auth()->user()->staff->pick_up_point->id, 'returned', $yesterdayStart, $yesterdayEnd);

        return response()->json([
            'today_date' => $todayStart->format('d M, Y'),
            'today_earning' => format_price($today),
            'yesterday_date' => $yesterdayStart->format('d M, Y'),
            'yesterday_earning' => format_price($yesterday),
        ]);
    }

    public function earning($id)
    {
        $pickupPoint = $this->pickupPointForUserId((int) $id);
        $orders = $this->baseOrderQuery($pickupPoint->id)
            ->whereIn('delivery_status', ['delivered', 'returned'])
            ->paginate(10);

        return response()->json([
            'data' => collect($orders->items())->map(function ($order) {
                $earning = $this->calculateOrderEarningForPickupPoint(
                    auth()->user()->staff->pick_up_point,
                    $order,
                    $order->delivery_status
                );

                return [
                    'id' => $order->id,
                    'delivery_boy_id' => auth()->id(),
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'delivery_status' => $order->delivery_status,
                    'payment_type' => $order->payment_type,
                    'earning' => format_price($earning),
                    'collection' => format_price(0),
                    'date' => optional($order->updated_at)->format('d-m-Y'),
                ];
            })->values(),
            'links' => [
                'first' => $orders->url(1),
                'last' => $orders->url($orders->lastPage()),
                'prev' => $orders->previousPageUrl(),
                'next' => $orders->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $orders->currentPage(),
                'from' => $orders->firstItem(),
                'last_page' => $orders->lastPage(),
                'path' => $orders->path(),
                'per_page' => $orders->perPage(),
                'to' => $orders->lastItem(),
                'total' => $orders->total(),
            ],
            'success' => true,
            'status' => 200,
        ]);
    }

    public function details($id)
    {
        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());
        $order = $this->baseOrderQuery($pickupPoint->id)->where('id', $id)->get();

        return new PurchaseHistoryCollection($order);
    }

    public function items($id)
    {
        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());
        $order = $this->baseOrderQuery($pickupPoint->id)->select('id')->where('id', $id)->firstOrFail();
        $orderItems = OrderDetail::where('order_id', $order->id)->get();

        return new PurchaseHistoryItemsCollection($orderItems);
    }

    protected function pickupPointForUserId(int $userId): PickupPoint
    {
        abort_if(auth()->id() !== $userId, 403, 'Unauthorized pickup point access.');

        $user = auth()->user();
        abort_if(!$user || $user->user_type !== 'staff', 403, 'Unauthorized pickup point access.');

        $pickupPoint = optional($user->staff)->pick_up_point;
        abort_if(!$pickupPoint || !$pickupPoint->id, 403, 'Pickup point manager not found.');

        return $pickupPoint;
    }

    protected function baseOrderQuery(int $pickupPointId)
    {
        return Order::where('shipping_type', 'pickup_point')
            ->where('pickup_point_id', $pickupPointId)
            ->orderByDesc('id');
    }

    protected function findManagedOrder(int $pickupPointId, int $orderId): Order
    {
        return $this->baseOrderQuery($pickupPointId)->where('id', $orderId)->firstOrFail();
    }

    protected function earningSummaryPayload(int $pickupPointId): array
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();
        $lastMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        return [
            $this->earningWindowPayload(translate('Today'), $pickupPointId, $todayStart, $todayEnd),
            $this->earningWindowPayload(translate('Last 7 Days'), $pickupPointId, $sevenDaysAgo, $todayEnd),
            $this->earningWindowPayload(translate('Last Month'), $pickupPointId, $lastMonthStart, $lastMonthEnd),
            $this->earningWindowPayload(translate('Total'), $pickupPointId),
        ];
    }

    protected function earningWindowPayload(string $label, int $pickupPointId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $delivery = $this->calculateEarningsForStatus($pickupPointId, 'delivered', $from, $to);
        $return = $this->calculateEarningsForStatus($pickupPointId, 'returned', $from, $to);

        return [
            'label' => $label,
            'delivery_earning' => $delivery,
            'delivery_earning_string' => format_price($delivery),
            'return_earning' => $return,
            'return_earning_string' => format_price($return),
            'total_earning' => $delivery + $return,
            'total_earning_string' => format_price($delivery + $return),
        ];
    }

    protected function calculateEarningsForStatus(int $pickupPointId, string $status, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = $this->baseOrderQuery($pickupPointId)->where('delivery_status', $status);

        if ($from && $to) {
            if ($status === 'delivered') {
                $query->whereBetween('delivered_date', [$from, $to]);
            } else {
                $query->whereBetween('updated_at', [$from, $to]);
            }
        }

        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());

        return $query->get()->sum(function ($order) use ($status, $pickupPoint) {
            return $this->calculateOrderEarningForPickupPoint($pickupPoint, $order, $status);
        });
    }

    protected function calculateOrderEarningForPickupPoint($pickupPoint, Order $order, string $status): float
    {
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

    protected function resolveManagedOrderForStatusChange(int $pickupPointId, Request $request): Order
    {
        if ($request->filled('order_id')) {
            return $this->findManagedOrder($pickupPointId, (int) $request->order_id);
        }

        $currentStatuses = match ($request->status) {
            'picked_up' => ['pending', 'confirmed'],
            'on_the_way' => ['picked_up'],
            'reached' => ['on_the_way'],
            'delivered', 'returned' => ['reached'],
            default => [],
        };

        $verificationCode = $this->orderDeliveryVerificationService->extractOrderCodeFromPayload(
            (string) $request->delivery_verification_code
        );

        $managedOrder = $this->baseOrderQuery($pickupPointId)
            ->where('code', $verificationCode)
            ->whereIn('delivery_status', $currentStatuses)
            ->first();

        if ($managedOrder) {
            return $managedOrder;
        }

        $otherPickupOrder = Order::with('pickup_point')
            ->where('shipping_type', 'pickup_point')
            ->where('code', $verificationCode)
            ->whereIn('delivery_status', $currentStatuses)
            ->first();

        if ($otherPickupOrder && (int) $otherPickupOrder->pickup_point_id !== $pickupPointId) {
            $assignedPickupPoint = $otherPickupOrder->pickup_point;
            $assignedPickupName = $assignedPickupPoint
                ? $assignedPickupPoint->getTranslation('name')
                : translate('Unknown Pickup Point');
            $assignedPickupId = (int) ($otherPickupOrder->pickup_point_id ?? 0);

            throw new HttpResponseException(response()->json([
                'result' => false,
                'message' => translate('This order belongs to another pickup point.') . ' ' .
                    translate('Assigned pickup point') . ': ' . $assignedPickupName . ' (#' . $assignedPickupId . ')',
                'assigned_pickup_point' => [
                    'id' => $assignedPickupId,
                    'name' => $assignedPickupName,
                ],
            ], 422));
        }

        return $this->baseOrderQuery($pickupPointId)
            ->where('code', $verificationCode)
            ->whereIn('delivery_status', $currentStatuses)
            ->firstOrFail();
    }

    protected function staleReachedOrdersQuery(int $pickupPointId): Builder
    {
        $pickupPoint = $this->pickupPointForUserId((int) auth()->id());
        $cutoffDate = Carbon::today()->subDays($pickupPoint->holdDays())->toDateString();

        return $this->baseOrderQuery($pickupPointId)
            ->where('delivery_status', 'reached')
            ->where(function (Builder $query) use ($cutoffDate) {
                $query->whereDate('delivery_history_date', '<=', $cutoffDate)
                    ->orWhere(function (Builder $fallbackQuery) use ($cutoffDate) {
                        $fallbackQuery->whereNull('delivery_history_date')
                            ->whereDate('updated_at', '<=', $cutoffDate);
                    });
            });
    }

    protected function returnDueOrdersPayload(int $pickupPointId): array
    {
        return $this->staleReachedOrdersQuery($pickupPointId)
            ->limit(3)
            ->get(['id', 'code', 'delivery_history_date', 'updated_at'])
            ->map(function (Order $order) {
                $reachedAt = $order->delivery_history_date ?: $order->updated_at;

                return [
                    'id' => $order->id,
                    'code' => $order->code,
                    'reached_at' => optional($reachedAt)->format('d M, Y'),
                ];
            })
            ->values()
            ->all();
    }

    protected function isReturnDue(Order $order): bool
    {
        $reachedAt = $order->delivery_history_date ?: $order->updated_at;

        if (!$reachedAt) {
            return false;
        }

        return Carbon::parse($reachedAt)->startOfDay()->addDays(optional($order->pickup_point)->holdDays() ?? 5)->startOfDay()->lte(Carbon::today());
    }

    protected function isReturnAvailableForOrder(Order $order): bool
    {
        if (!addon_is_activated('refund_request')) {
            return true;
        }

        if ($order->orderDetails->isEmpty()) {
            return false;
        }

        return $order->orderDetails->every(function (OrderDetail $orderDetail) {
            return optional($orderDetail->product)->refundable != 0;
        });
    }
}
