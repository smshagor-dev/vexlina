<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReelsTables extends Migration
{
    public function up()
    {
        Schema::create('reels_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->integer('product_id')->nullable();
            $table->unsignedInteger('video_upload_id');
            $table->unsignedInteger('thumbnail_upload_id')->nullable();
            $table->text('caption')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['draft', 'published', 'hidden', 'deleted'])->default('published');
            $table->boolean('allow_comments')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('product_id');
            $table->index('status');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('video_upload_id')->references('id')->on('uploads')->onDelete('cascade');
            $table->foreign('thumbnail_upload_id')->references('id')->on('uploads')->onDelete('set null');
        });

        Schema::create('reels_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reel_post_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->unique(['reel_post_id', 'user_id']);
            $table->foreign('reel_post_id')->references('id')->on('reels_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('reels_saves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reel_post_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->unique(['reel_post_id', 'user_id']);
            $table->foreign('reel_post_id')->references('id')->on('reels_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('reels_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reel_post_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('device_key', 120)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['reel_post_id', 'user_id']);
            $table->foreign('reel_post_id')->references('id')->on('reels_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('reels_shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reel_post_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('platform', 50)->nullable();
            $table->timestamps();

            $table->foreign('reel_post_id')->references('id')->on('reels_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('reels_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reel_post_id');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('comment');
            $table->enum('status', ['published', 'hidden', 'deleted'])->default('published');
            $table->timestamps();

            $table->index(['reel_post_id', 'status']);
            $table->foreign('reel_post_id')->references('id')->on('reels_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('reels_comments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reels_comments');
        Schema::dropIfExists('reels_shares');
        Schema::dropIfExists('reels_views');
        Schema::dropIfExists('reels_saves');
        Schema::dropIfExists('reels_likes');
        Schema::dropIfExists('reels_posts');
    }
}
