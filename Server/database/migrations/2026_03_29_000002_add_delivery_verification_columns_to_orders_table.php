<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('delivery_verification_status')
                ->default(false)
                ->after('payment_status_viewed');
            $table->timestamp('delivery_verified_at')
                ->nullable()
                ->after('delivery_verification_status');
            $table->unsignedBigInteger('delivery_verified_by')
                ->nullable()
                ->after('delivery_verified_at');
            $table->string('delivery_verification_source', 30)
                ->nullable()
                ->after('delivery_verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_verification_status',
                'delivery_verified_at',
                'delivery_verified_by',
                'delivery_verification_source',
            ]);
        });
    }
};
