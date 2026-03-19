<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakePeople extends Model
{
    use HasFactory;

    protected $table = 'fake_people';

    protected $fillable = [
        'full_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'age',
        'gender',
        'district',
        'upazila',
        'district_type',
        'country',
    ];


    public function lottaryWinners()
    {
        return $this->hasMany(LottaryWinner::class, 'fake_people_id');
    }


    public function getFullNameAttribute($value)
    {
        if ($value) return $value;

        return trim("{$this->first_name} {$this->last_name}");
    }
}
