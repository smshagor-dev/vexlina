<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Order extends Model
{
    use PreventDemoModeChanges;
    
     protected $fillable = [
        'steadfast_consignment_id',
        'steadfast_tracking_code',
        'steadfast_invoice',
        'steadfast_synced',
        'steadfast_synced_at',
        'delivery_verification_status',
        'delivery_verified_at',
        'delivery_verified_by',
        'delivery_verification_source',
    ];

    protected $casts = [
        'steadfast_synced'    => 'boolean',
        'steadfast_synced_at' => 'datetime',
        'delivery_verification_status' => 'boolean',
        'delivery_verified_at' => 'datetime',
    ];
    
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }

    public function commissionHistory()
    {
        return $this->hasOne(CommissionHistory::class);
    }
}
