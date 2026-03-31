<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWalletCardSecurityColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_card_expiry_month', 2)->nullable()->after('wallet_card_number');
            $table->string('wallet_card_expiry_year', 2)->nullable()->after('wallet_card_expiry_month');
            $table->string('wallet_card_cvv', 3)->nullable()->after('wallet_card_expiry_year');
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'wallet_card_number' => $this->shouldRefreshCardNumber($user)
                            ? $this->generateWalletCardNumber($user->id)
                            : $user->wallet_card_number,
                        'wallet_card_expiry_month' => $user->wallet_card_expiry_month ?: $this->generateWalletCardExpiryMonth($user->id),
                        'wallet_card_expiry_year' => $user->wallet_card_expiry_year ?: $this->generateWalletCardExpiryYear(),
                        'wallet_card_cvv' => $user->wallet_card_cvv ?: $this->generateWalletCardCvv($user->id),
                    ]);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wallet_card_expiry_month',
                'wallet_card_expiry_year',
                'wallet_card_cvv',
            ]);
        });
    }

    private function shouldRefreshCardNumber($user)
    {
        $existing = preg_replace('/\D/', '', (string) $user->wallet_card_number);
        $legacy = '5217' . str_pad((string) $user->id, 12, '0', STR_PAD_LEFT);

        return $existing === '' || $existing === $legacy;
    }

    private function generateWalletCardNumber($userId)
    {
        $middle = substr(str_pad((string) abs(crc32('wallet-card-' . $userId)), 8, '0', STR_PAD_LEFT), 0, 8);
        $suffix = str_pad((string) $userId, 4, '0', STR_PAD_LEFT);

        return '5217' . $middle . $suffix;
    }

    private function generateWalletCardExpiryMonth($userId)
    {
        return str_pad(((abs(crc32('wallet-month-' . $userId)) % 12) + 1), 2, '0', STR_PAD_LEFT);
    }

    private function generateWalletCardExpiryYear()
    {
        return date('y', strtotime('+10 years'));
    }

    private function generateWalletCardCvv($userId)
    {
        return str_pad((string) ((abs(crc32('wallet-cvv-' . $userId)) % 900) + 100), 3, '0', STR_PAD_LEFT);
    }
}
