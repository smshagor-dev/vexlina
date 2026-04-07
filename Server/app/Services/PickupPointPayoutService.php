<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PickupPoint;
use App\Models\PickupPointPayoutRequest;
use Carbon\Carbon;

class PickupPointPayoutService
{
    public function totalEarned(PickupPoint $pickupPoint): float
    {
        return Order::where('shipping_type', 'pickup_point')
            ->where('pickup_point_id', $pickupPoint->id)
            ->whereIn('delivery_status', ['delivered', 'returned'])
            ->get()
            ->sum(function (Order $order) use ($pickupPoint) {
                return $this->calculateOrderEarning($pickupPoint, $order, $order->delivery_status);
            });
    }

    public function approvedPayoutTotal(PickupPoint $pickupPoint): float
    {
        return (float) PickupPointPayoutRequest::where('pickup_point_id', $pickupPoint->id)
            ->where('status', 1)
            ->sum('amount');
    }

    public function pendingPayoutTotal(PickupPoint $pickupPoint): float
    {
        return (float) PickupPointPayoutRequest::where('pickup_point_id', $pickupPoint->id)
            ->where('status', 0)
            ->sum('amount');
    }

    public function availableBalance(PickupPoint $pickupPoint): float
    {
        return max(round($this->totalEarned($pickupPoint) - $this->approvedPayoutTotal($pickupPoint), 2), 0);
    }

    public function availableForRequest(PickupPoint $pickupPoint): float
    {
        return max(round($this->availableBalance($pickupPoint) - $this->pendingPayoutTotal($pickupPoint), 2), 0);
    }

    public function lastApprovedRequest(PickupPoint $pickupPoint): ?PickupPointPayoutRequest
    {
        return PickupPointPayoutRequest::where('pickup_point_id', $pickupPoint->id)
            ->where('status', 1)
            ->latest('processed_at')
            ->first();
    }

    public function nextEligibleAt(PickupPoint $pickupPoint): ?Carbon
    {
        $lastApproved = $this->lastApprovedRequest($pickupPoint);
        if (!$lastApproved || !$lastApproved->processed_at) {
            return null;
        }

        return Carbon::parse($lastApproved->processed_at)->addDays($pickupPoint->payout_frequency_days ?? 7)->startOfDay();
    }

    public function canRequest(PickupPoint $pickupPoint): array
    {
        $nextEligibleAt = $this->nextEligibleAt($pickupPoint);
        $availableForRequest = $this->availableForRequest($pickupPoint);

        if ($nextEligibleAt && Carbon::now()->lt($nextEligibleAt)) {
            return [
                'allowed' => false,
                'message' => translate('Next payout request will be available on ') . $nextEligibleAt->format('d M, Y'),
                'next_eligible_at' => $nextEligibleAt,
            ];
        }

        if ($availableForRequest <= 0) {
            return [
                'allowed' => false,
                'message' => translate('No requestable payout balance is available right now.'),
                'next_eligible_at' => $nextEligibleAt,
            ];
        }

        return [
            'allowed' => true,
            'message' => null,
            'next_eligible_at' => $nextEligibleAt,
        ];
    }

    public function calculateOrderEarning(PickupPoint $pickupPoint, Order $order, ?string $status = null): float
    {
        $status = $status ?: $order->delivery_status;

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

    public function payoutSummary(PickupPoint $pickupPoint): array
    {
        $nextEligibleAt = $this->nextEligibleAt($pickupPoint);
        $availability = $this->canRequest($pickupPoint);

        return [
            'total_earned' => $this->totalEarned($pickupPoint),
            'approved_payout_total' => $this->approvedPayoutTotal($pickupPoint),
            'pending_payout_total' => $this->pendingPayoutTotal($pickupPoint),
            'current_balance' => $this->availableBalance($pickupPoint),
            'requestable_balance' => $this->availableForRequest($pickupPoint),
            'payout_frequency_days' => (int) ($pickupPoint->payout_frequency_days ?? 7),
            'next_eligible_at' => optional($nextEligibleAt)->toDateString(),
            'can_request' => (bool) ($availability['allowed'] ?? false),
            'eligibility_message' => $availability['message'] ?? null,
        ];
    }
}
