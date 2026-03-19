<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LottaryPrize extends Model
{
    use HasFactory;

    protected $table = 'lottary_prizes';

    protected $fillable = [
        'lottary_id',
        'name', 
        'prize_value',
        'winner_number',
        'drew_type',
        'description',
        'photo'
    ];


    public function lottary()
    {
        return $this->belongsTo(Lottary::class, 'lottary_id');
    }
    
    public function isReal(): bool
    {
        return $this->drew_type === 'Real';
    }
    
    public function isFake(): bool
    {
        return $this->drew_type === 'Fake';
    }
    
    public function winners()
    {
        return $this->hasMany(LottaryWinner::class, 'lottary_prize_id');
    }
    
    public function fakeWinners()
    {
        return $this->hasMany(LottaryWinner::class, 'lottary_prize_id')
                    ->where('user_type', 'Fake')
                    ->with('fakePerson');
    }

}
