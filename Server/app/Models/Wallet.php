<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Wallet extends Model
{
    use PreventDemoModeChanges;

    protected static function booted()
    {
        static::created(function (self $wallet) {
            $wallet->ensureTransactionNumber();
        });
    }

    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function paymentMeta()
    {
        $decoded = json_decode((string) $this->payment_details, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function displayPaymentMethod()
    {
        if ($this->payment_method === 'card_to_card_sent') {
            return translate('Card To Card Sent');
        }

        if ($this->payment_method === 'card_to_card_received') {
            return translate('Card To Card Received');
        }

        return ucwords(str_replace('_', ' ', (string) $this->payment_method));
    }

    public function displayStatus()
    {
        if ($this->offline_payment) {
            return $this->approval == 1 ? translate('Approved') : translate('Pending');
        }

        return translate('Completed');
    }

    public function transferDirection()
    {
        if ($this->payment_method === 'card_to_card_sent') {
            return 'sent';
        }

        if ($this->payment_method === 'card_to_card_received') {
            return 'received';
        }

        return $this->amount < 0 ? 'debit' : 'credit';
    }

    public function counterpartyLabel()
    {
        $meta = $this->paymentMeta();
        $name = trim((string) ($meta['counterparty_name'] ?? ''));
        $card = trim((string) ($meta['counterparty_card_number'] ?? ''));

        return trim($name . ($card !== '' ? ' (' . $card . ')' : ''));
    }

    public function ensureTransactionNumber()
    {
        $transactionNumber = trim((string) $this->transaction_number);

        if ($transactionNumber === '') {
            $transactionNumber = static::generateTransactionNumber($this->id);
            $this->transaction_number = $transactionNumber;
            $this->saveQuietly();
        }

        return $transactionNumber;
    }

    public static function generateTransactionNumber($walletId)
    {
        return 'WTXN'
            . str_pad((string) $walletId, 6, '0', STR_PAD_LEFT)
            . random_int(10, 99);
    }
}
