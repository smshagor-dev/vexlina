<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelComment extends Model
{
    protected $table = 'reels_comments';

    protected $fillable = ['reel_post_id', 'user_id', 'parent_id', 'comment', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
