<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Lottary;
use App\Models\LottaryTicket;
use Illuminate\Support\Str;
use App\Models\NotificationType;
use App\Notifications\LotteryNotification;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
    
    public function saved(Order $order)
    {
        $userId = $order->user_id;
    
        // get active lottery
        $lottary = Lottary::where('is_active', 1)
            ->where('is_drew', 0)
            ->latest()
            ->first();
    
        // calculate shipping cost
        $shippingCost = OrderDetail::where('order_id', $order->id)
            ->sum('shipping_cost');
    
        $eligibleAmount = $order->grand_total - $shippingCost;
    
        // if no lottery or not eligible → stop everything
        if (!$lottary || $eligibleAmount < $lottary->price) {
            return;
        }
    

        if ($order->payment_status !== 'paid') {
    
            $alreadyPending = \DB::table('notifications')
                ->where('notification_type_id', 35)
                ->where('notifiable_id', $userId)
                ->whereJsonContains('data->order_id', $order->id)
                ->exists();
    
            if (!$alreadyPending) {
                \DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'notification_type_id' => 35,
                    'type' => 'App\Notifications\LotteryNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $userId,
                    'data' => json_encode([
                        'order_id'   => $order->id,
                        'order_code' => $order->code,
                        'user_id'    => $userId,
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            return;
        }
    

        $alreadyTicket = LottaryTicket::where('user_id', $userId)
            ->where('lottary_id', $lottary->id)
            ->where('order_id', $order->id)
            ->exists();
    
        if ($alreadyTicket) {
            return;
        }
    
        do {
            $ticketNumber = generateLotteryTicket();
        } while (LottaryTicket::where('ticket_number', $ticketNumber)->exists());
    
        LottaryTicket::create([
            'user_id'       => $userId,
            'lottary_id'    => $lottary->id,
            'order_id'      => $order->id,
            'ticket_number' => $ticketNumber,
        ]);
    
        \DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'notification_type_id' => 36,
            'type' => 'App\Notifications\LotteryNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $userId,
            'data' => json_encode([
                'order_id' => $order->id,
                'ticket_number' => $ticketNumber,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

}
