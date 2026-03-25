<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelPost extends Model
{
    protected $table = 'reels_posts';

    protected $fillable = [
        'user_id',
        'product_id',
        'video_upload_id',
        'thumbnail_upload_id',
        'caption',
        'duration_seconds',
        'status',
        'allow_comments',
    ];

    protected $casts = [
        'allow_comments' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function video()
    {
        return $this->belongsTo(Upload::class, 'video_upload_id');
    }

    public function thumbnail()
    {
        return $this->belongsTo(Upload::class, 'thumbnail_upload_id');
    }

    public function likes()
    {
        return $this->hasMany(ReelLike::class, 'reel_post_id');
    }

    public function saves()
    {
        return $this->hasMany(ReelSave::class, 'reel_post_id');
    }

    public function views()
    {
        return $this->hasMany(ReelView::class, 'reel_post_id');
    }

    public function shares()
    {
        return $this->hasMany(ReelShare::class, 'reel_post_id');
    }

    public function comments()
    {
        return $this->hasMany(ReelComment::class, 'reel_post_id');
    }
}
