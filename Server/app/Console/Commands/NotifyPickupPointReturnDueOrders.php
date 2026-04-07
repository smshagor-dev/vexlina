<?php

namespace App\Console\Commands;

use App\Models\FirebaseNotification;
use App\Models\Order;
use App\Utility\NotificationUtility;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyPickupPointReturnDueOrders extends Command
{
    protected $signature = 'pickup-point:notify-return-due-orders';

    protected $description = 'Notify pickup point managers when reached orders exceed the pickup hold window';

    public function handle(): int
    {
        $orders = Order::with(['pickup_point.staff.user'])
            ->where('shipping_type', 'pickup_point')
            ->where('delivery_status', 'reached')
            ->get();

        $sentCount = 0;

        foreach ($orders as $order) {
            $holdDays = optional($order->pickup_point)->holdDays() ?? 5;
            $cutoffDate = Carbon::today()->subDays($holdDays)->toDateString();
            $reachedAt = $order->delivery_history_date ?: $order->updated_at;

            if (!$reachedAt || Carbon::parse($reachedAt)->toDateString() > $cutoffDate) {
                continue;
            }

            $manager = optional(optional(optional($order->pickup_point)->staff)->user);

            if (!$manager || !$manager->id || empty($manager->device_token)) {
                continue;
            }

            $alreadySent = FirebaseNotification::where('receiver_id', $manager->id)
                ->where('item_type', 'pickup_return_due')
                ->where('item_type_id', $order->id)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $request = new \stdClass();
            $request->device_token = $manager->device_token;
            $request->title = 'Return action required';
            $request->text = "Order {$order->code} has been waiting at the pickup point for more than {$holdDays} days. Please return it.";
            $request->type = 'pickup_return_due';
            $request->id = $order->id;
            $request->user_id = $manager->id;

            NotificationUtility::sendFirebaseNotification($request);
            $sentCount++;
        }

        $this->info("Pickup point return reminders sent: {$sentCount}");

        return self::SUCCESS;
    }
}
