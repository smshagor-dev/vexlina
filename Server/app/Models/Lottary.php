<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lottary extends Model
{
    use HasFactory;

    protected $table = 'lottaries';

    protected $fillable = [
        'title',
        'description',
        'photo',
        'price',
        'prize_number',
        'winner_number',
        'start_date',
        'drew_date',
        'is_drew',
        'is_active',
    ];

    protected $casts = [
        'is_drew'   => 'boolean',
        'is_active' => 'boolean',
        'drew_date' => 'datetime',
        'start_date' => 'datetime',
    ];


    public function prizes()
    {
        return $this->hasMany(LottaryPrize::class, 'lottary_id');
    }
    
    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? asset($this->photo)
            : asset('assets/img/placeholder.png');
    }
    
    public function tickets()
    {
        return $this->hasMany(LottaryTicket::class, 'lottary_id');
    }


}
