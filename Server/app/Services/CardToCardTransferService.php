<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CardToCardTransferService
{
    public function transfer(User $sender, string $receiverCardNumber, float $amount): array
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => translate('Transfer amount must be greater than zero.'),
            ]);
        }

        $sender->ensureWalletCardDetails();
        $normalizedReceiverCardNumber = $this->normalizeCardNumber($receiverCardNumber);

        if ($normalizedReceiverCardNumber === '') {
            throw ValidationException::withMessages([
                'receiver_card_number' => translate('Receiver card number is required.'),
            ]);
        }

        return DB::transaction(function () use ($sender, $normalizedReceiverCardNumber, $amount) {
            /** @var User $lockedSender */
            $lockedSender = User::where('id', $sender->id)->lockForUpdate()->firstOrFail();
            $lockedSender->ensureWalletCardDetails();

            /** @var User|null $receiver */
            $receiver = User::whereRaw("REPLACE(wallet_card_number, ' ', '') = ?", [$normalizedReceiverCardNumber])
                ->lockForUpdate()
                ->first();

            if ($receiver === null) {
                throw ValidationException::withMessages([
                    'receiver_card_number' => translate('No wallet account was found for this card number.'),
                ]);
            }

            $receiver->ensureWalletCardDetails();

            if ((int) $receiver->id === (int) $lockedSender->id) {
                throw ValidationException::withMessages([
                    'receiver_card_number' => translate('You cannot send money to your own card.'),
                ]);
            }

            if ((float) $lockedSender->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => translate('You do not have enough wallet balance for this transfer.'),
                ]);
            }

            $lockedSender->balance = round(((float) $lockedSender->balance) - $amount, 2);
            $receiver->balance = round(((float) $receiver->balance) + $amount, 2);

            $lockedSender->save();
            $receiver->save();

            $senderWallet = new Wallet();
            $senderWallet->user_id = $lockedSender->id;
            $senderWallet->amount = -$amount;
            $senderWallet->payment_method = 'card_to_card_sent';
            $senderWallet->payment_details = json_encode([
                'type' => 'card_to_card',
                'counterparty_user_id' => $receiver->id,
                'counterparty_name' => $receiver->name,
                'counterparty_card_number' => $this->maskCardNumber($receiver->wallet_card_number),
            ]);
            $senderWallet->save();

            $receiverWallet = new Wallet();
            $receiverWallet->user_id = $receiver->id;
            $receiverWallet->amount = $amount;
            $receiverWallet->payment_method = 'card_to_card_received';
            $receiverWallet->payment_details = json_encode([
                'type' => 'card_to_card',
                'counterparty_user_id' => $lockedSender->id,
                'counterparty_name' => $lockedSender->name,
                'counterparty_card_number' => $this->maskCardNumber($lockedSender->wallet_card_number),
            ]);
            $receiverWallet->save();

            return [
                'sender' => $lockedSender->fresh(),
                'receiver' => $receiver->fresh(),
                'sender_wallet' => $senderWallet->fresh(),
                'receiver_wallet' => $receiverWallet->fresh(),
            ];
        });
    }

    protected function normalizeCardNumber(string $cardNumber): string
    {
        return preg_replace('/\D/', '', $cardNumber);
    }

    protected function maskCardNumber(?string $cardNumber): string
    {
        $digits = $this->normalizeCardNumber((string) $cardNumber);

        if ($digits === '') {
            return '';
        }

        return substr($digits, 0, 4) . '****' . substr($digits, -4);
    }
}
