<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LottaryWinner extends Model
{
    use HasFactory;

    protected $table = 'lottary_winners';

    protected $fillable = [
        'user_id',
        'fake_people_id',
        'user_type',
        'lottary_id',
        'lottary_prize_id',
        'lottary_tickets_id',
        'ticket_number',
        'claim_request',
        'mobile',
        'claim_request_address',
        'claim_code',
        'send_gift',
    ];
    
    protected $casts = [
        'claim_request' => 'boolean',
        'send_gift' => 'boolean',
    ];

    /**
     * Real user relation (nullable)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Fake user relation (nullable)
     */
    public function fakePerson()
    {
        return $this->belongsTo(FakePeople::class, 'fake_people_id');
    }

    /**
     * Lottary relation
     */
    public function lottary()
    {
        return $this->belongsTo(Lottary::class, 'lottary_id');
    }

    /**
     * Lottary prize relation
     */
    public function prize()
    {
        return $this->belongsTo(LottaryPrize::class, 'lottary_prize_id');
    }

    /**
     * Lottary ticket relation
     */
    public function ticket()
    {
        return $this->belongsTo(LottaryTicket::class, 'lottary_tickets_id');
    }

    /**
     * Check if winner is real
     */
    public function isReal(): bool
    {
        return $this->user_type === 'Real';
    }

    /**
     * Check if winner is fake
     */
    public function isFake(): bool
    {
        return $this->user_type === 'Fake';
    }
}
