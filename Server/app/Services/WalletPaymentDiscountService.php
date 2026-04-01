<?php

namespace App\Services;

class WalletPaymentDiscountService
{
    public function isEnabled(): bool
    {
        return (int) get_setting('wallet_payment_discount_status') === 1
            && $this->getPercentage() > 0;
    }

    public function getPercentage(): float
    {
        return round((float) get_setting('wallet_payment_discount_percent'), 2);
    }

    public function shouldApply(?string $paymentType): bool
    {
        return $paymentType === 'wallet' && $this->isEnabled();
    }

    public function calculateDiscount(float $amount, ?string $paymentType = 'wallet'): float
    {
        if (!$this->shouldApply($paymentType) || $amount <= 0) {
            return 0.00;
        }

        return round(($amount * $this->getPercentage()) / 100, 2);
    }

    public function calculateDiscountOnSubtotal(
        float $subtotal,
        float $couponDiscount = 0.00,
        ?string $paymentType = 'wallet'
    ): float {
        $discountBase = max(round($subtotal - $couponDiscount, 2), 0.00);

        return $this->calculateDiscount($discountBase, $paymentType);
    }

    public function applyDiscount(float $amount, ?string $paymentType = 'wallet'): float
    {
        return max(round($amount - $this->calculateDiscount($amount, $paymentType), 2), 0.00);
    }

    public function applyDiscountToTotalUsingSubtotal(
        float $total,
        float $subtotal,
        float $couponDiscount = 0.00,
        ?string $paymentType = 'wallet'
    ): float {
        return max(
            round($total - $this->calculateDiscountOnSubtotal($subtotal, $couponDiscount, $paymentType), 2),
            0.00
        );
    }
}
