<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LottaryTicket extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'lottary_id',
        'ticket_number',
        'order_id',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function lottary()
    {
        return $this->belongsTo(Lottary::class, 'lottary_id');
    }
}
