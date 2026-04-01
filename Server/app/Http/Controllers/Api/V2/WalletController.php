<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\WalletCollection;
use App\Models\CombinedOrder;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CardToCardTransferService;
use App\Services\WalletPaymentDiscountService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function balance()
    {
        $user = User::find(auth()->user()->id);
        $latest = Wallet::where('user_id', auth()->user()->id)->latest()->first();
        return response()->json([
            'balance' => single_price($user->balance),
            'last_recharged' => $latest == null ? "Not Available" : $latest->created_at->diffForHumans(),
        ]);
    }

    public function walletRechargeHistory()
    {
        return new WalletCollection(Wallet::where('user_id', auth()->user()->id)->latest()->paginate(10));
    }

    public function processPayment(Request $request)
    {
        $order = new OrderController;
        $user = User::find($request->user_id);
        $walletPaymentDiscountService = new WalletPaymentDiscountService;
        $cartItems = Cart::where('user_id', $request->user_id)->active()->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'result' => false,
                'combined_order_id' => 0,
                'message' => translate('Cart is Empty')
            ]);
        }

        $subtotal = 0.00;
        $tax = 0.00;
        $gst = 0.00;
        foreach ($cartItems as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            $gst += cart_product_gst($cartItem, $product, false);
        }
        $shippingCost = $cartItems->sum('shipping_cost');
        $couponDiscount = $cartItems->sum('discount');
        $walletPayableAmount = round(($subtotal + $tax + $shippingCost + $gst) - $couponDiscount, 2);
        $walletPayableAmount = $walletPaymentDiscountService->applyDiscountToTotalUsingSubtotal(
            $walletPayableAmount,
            $subtotal,
            $couponDiscount,
            'wallet'
        );

        if ($user->balance >= $walletPayableAmount) {
            
            $response =  $order->store($request, true);
            $decoded_response = $response->original;
            if ($decoded_response['result'] == true) { // only decrease user balance with a success
                $combined_order = CombinedOrder::where('id', $decoded_response['combined_order_id'])->first();
                $payableAmount = $combined_order != null
                    ? (float) $combined_order->grand_total
                    : $walletPayableAmount;

                $user->balance -= $payableAmount;
                $user->save();            
            }

            $combined_order = CombinedOrder::where('id', $decoded_response['combined_order_id'])->first();

            foreach ($combined_order->orders as $key => $order) {
                calculateCommissionAffilationClubPoint($order);
            }
            
            return $response;

        } else {
            return response()->json([
                'result' => false,
                'combined_order_id' => 0,
                'message' => translate('Insufficient wallet balance')
            ]);
        }
    }

    public function offline_recharge(Request $request)
    {
        $wallet = new Wallet;
        $wallet->user_id = auth()->user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = $request->payment_option;
        $wallet->payment_details = $request->trx_id;
        $wallet->transaction_number = $request->trx_id;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->reciept = $request->photo;
        $wallet->save();
        return response()->json([
            'result' => true,
            'message' => translate('Offline Recharge has been done. Please wait for response.')
        ]);
    }

    public function sendMoney(Request $request, CardToCardTransferService $transferService)
    {
        $request->validate([
            'receiver_card_number' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $transfer = $transferService->transfer(
                auth()->user(),
                (string) $request->receiver_card_number,
                (float) $request->amount
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? translate('Validation failed.');

            return response()->json([
                'result' => false,
                'message' => $message,
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'result' => false,
                'message' => translate('Unable to complete card to card transfer right now.'),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Money sent successfully to') . ' ' . ($transfer['receiver']->name ?? translate('receiver')),
            'balance' => single_price($transfer['sender']->balance),
        ]);
    }

}
