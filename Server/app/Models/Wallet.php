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
