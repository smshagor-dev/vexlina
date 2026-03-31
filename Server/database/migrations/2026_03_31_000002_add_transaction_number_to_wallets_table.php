<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTransactionNumberToWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('transaction_number', 64)->nullable()->after('payment_details');
        });

        DB::table('wallets')->orderBy('id')->chunkById(100, function ($wallets) {
            foreach ($wallets as $wallet) {
                $transactionNumber = 'WTXN'
                    . str_pad((string) $wallet->id, 6, '0', STR_PAD_LEFT)
                    . random_int(10, 99);

                DB::table('wallets')
                    ->where('id', $wallet->id)
                    ->update(['transaction_number' => $transactionNumber]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('transaction_number');
        });
    }
}
