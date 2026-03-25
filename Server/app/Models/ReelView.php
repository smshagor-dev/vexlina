<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelView extends Model
{
    protected $table = 'reels_views';

    protected $fillable = ['reel_post_id', 'user_id', 'device_key', 'ip_address'];
}
