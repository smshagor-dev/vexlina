<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_points', function (Blueprint $table) {
            $table->unsignedInteger('payout_frequency_days')->default(7)->after('pickup_hold_days');
            $table->string('payout_method')->nullable()->after('payout_frequency_days');
            $table->string('payout_account_name')->nullable()->after('payout_method');
            $table->string('payout_account_number')->nullable()->after('payout_account_name');
            $table->string('payout_bank_name')->nullable()->after('payout_account_number');
            $table->string('payout_branch_name')->nullable()->after('payout_bank_name');
            $table->string('payout_routing_number')->nullable()->after('payout_branch_name');
            $table->string('payout_mobile_wallet_type')->nullable()->after('payout_routing_number');
            $table->string('payout_mobile_wallet_number')->nullable()->after('payout_mobile_wallet_type');
            $table->text('payout_notes')->nullable()->after('payout_mobile_wallet_number');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_points', function (Blueprint $table) {
            $table->dropColumn([
                'payout_frequency_days',
                'payout_method',
                'payout_account_name',
                'payout_account_number',
                'payout_bank_name',
                'payout_branch_name',
                'payout_routing_number',
                'payout_mobile_wallet_type',
                'payout_mobile_wallet_number',
                'payout_notes',
            ]);
        });
    }
};
