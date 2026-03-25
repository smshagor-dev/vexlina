<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelSave extends Model
{
    protected $table = 'reels_saves';

    protected $fillable = ['reel_post_id', 'user_id'];
}
