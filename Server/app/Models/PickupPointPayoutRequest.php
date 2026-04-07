<?php

namespace App\Models;

use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\Model;

class PickupPointPayoutRequest extends Model
{
    use PreventDemoModeChanges;

    protected $fillable = [
        'pickup_point_id',
        'amount',
        'status',
        'payment_method',
        'payment_reference',
        'account_snapshot',
        'message',
        'admin_note',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class, 'pickup_point_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function statusLabel(): string
    {
        return match ((int) $this->status) {
            1 => translate('Approved'),
            2 => translate('Rejected'),
            default => translate('Pending'),
        };
    }
}
