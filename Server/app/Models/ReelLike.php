<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelLike extends Model
{
    protected $table = 'reels_likes';

    protected $fillable = ['reel_post_id', 'user_id'];
}
