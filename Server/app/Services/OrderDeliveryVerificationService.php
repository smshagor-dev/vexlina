<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class OrderDeliveryVerificationService
{
    public function verify(Order $order, ?string $verificationCode, User $actor, string $source = 'web'): array
    {
        if (!$this->canVerify($order, $actor)) {
            return [
                'success' => false,
                'message' => translate('You are not allowed to verify this order.'),
                'status' => 403,
            ];
        }

        if ($order->delivery_status === 'cancelled') {
            return [
                'success' => false,
                'message' => translate('Cancelled orders cannot be verified.'),
                'status' => 422,
            ];
        }

        $normalizedCode = trim((string) $verificationCode);
        if ($normalizedCode === '') {
            return [
                'success' => false,
                'message' => translate('Verification code is required.'),
                'status' => 422,
            ];
        }

        if (!hash_equals((string) $order->code, $normalizedCode)) {
            return [
                'success' => false,
                'message' => translate('Invalid verification code.'),
                'status' => 422,
            ];
        }

        if (!$order->delivery_verification_status) {
            $order->delivery_verification_status = true;
            $order->delivery_verified_at = now();
            $order->delivery_verified_by = $actor->id;
            $order->delivery_verification_source = $source;
            $order->save();
        }

        return [
            'success' => true,
            'message' => translate('Order delivery verified successfully.'),
            'status' => 200,
            'data' => [
                'order_id' => $order->id,
                'order_code' => $order->code,
                'delivery_verification_status' => (bool) $order->delivery_verification_status,
                'delivery_verified_at' => optional($order->delivery_verified_at)->toDateTimeString(),
                'delivery_verified_by' => $order->delivery_verified_by,
                'delivery_verification_source' => $order->delivery_verification_source,
            ],
        ];
    }

    public function ensureVerifiedForDelivery(
        Order $order,
        User $actor,
        ?string $verificationCode = null,
        string $source = 'web'
    ): array {
        if ($actor->user_type !== 'delivery_boy') {
            return ['success' => true, 'status' => 200];
        }

        if ($order->delivery_verification_status) {
            return ['success' => true, 'status' => 200];
        }

        return $this->verify($order, $verificationCode, $actor, $source);
    }

    protected function canVerify(Order $order, User $actor): bool
    {
        if ($actor->user_type === 'delivery_boy') {
            return (int) $order->assign_delivery_boy === (int) $actor->id;
        }

        if ($actor->user_type === 'seller') {
            return (int) $order->seller_id === (int) $actor->id;
        }

        return in_array($actor->user_type, ['admin', 'staff'], true);
    }
}
