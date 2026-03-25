<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelShare extends Model
{
    protected $table = 'reels_shares';

    protected $fillable = ['reel_post_id', 'user_id', 'platform'];
}
