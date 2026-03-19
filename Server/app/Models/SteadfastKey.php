<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SteadfastKey extends Model
{
    use HasFactory;
    
    protected $table = 'steadfast_key';

    protected $fillable = [
        'steadfast_api_key',
        'steadfast_secret_key',
        'steadfast_base_url',
        'steadfast_webhook_token',
    ];
}
