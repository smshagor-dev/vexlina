<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SteadfastWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        if ($token !== config('steadfast.webhook_token')) {
            return response()->json(['status'=>'error','message'=>'Unauthorized'], 401);
        }

        $data = $request->all();

        $order = Order::where(function($q) use ($data) {
            if (!empty($data['consignment_id'])) {
                $q->orWhere('steadfast_consignment_id', $data['consignment_id']);
            }
            if (!empty($data['invoice'])) {
                $q->orWhere('steadfast_invoice', $data['invoice']);
            }
            if (!empty($data['tracking_code'])) {
                $q->orWhere('steadfast_tracking_code', $data['tracking_code']);
            }
        })->first();


        if (!$order) {
            Log::warning('Webhook order not found', $data);
            return response()->json(['status'=>'error','message'=>'Order not found'], 404);
        }

        $mappedStatus = $this->mapSteadfastStatus($data['status'] ?? 'pending');

        if ($order->delivery_status !== $mappedStatus) {
            $oldStatus = $order->delivery_status;

            $order->update([
                'delivery_status' => $mappedStatus,
                'steadfast_last_status' => strtolower($data['status'] ?? ''),
                'steadfast_status_synced_at' => now(),
            ]);

            Log::info('Webhook: Steadfast status updated', [
                'order_id' => $order->id,
                'old' => $oldStatus,
                'new' => $mappedStatus,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery status updated'
        ]);
    }

    
    private function mapSteadfastStatus(string $status): string
    {
        return match (strtolower($status)) {
            'pending'           => 'pending',
            'delivered'         => 'delivered',
            'partial_delivered' => 'delivered',
            'cancelled'         => 'cancelled',
            'unknown'           => 'pending',
            default             => 'pending',
        };
    }


}
