<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_point_payout_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pickup_point_id');
            $table->decimal('amount', 20, 2);
            $table->tinyInteger('status')->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('account_snapshot')->nullable();
            $table->text('message')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedInteger('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('pickup_point_id', 'fk_payout_pickup_point')
                ->references('id')
                ->on('pickup_points')
                ->onDelete('cascade');

            $table->foreign('processed_by', 'fk_payout_processed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_point_payout_requests');
    }
};
