<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\SteadfastService;
use Illuminate\Support\Facades\Log;

class SyncSteadfastOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steadfast:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync order delivery status from Steadfast';

    /**
     * Execute the console command.
     */
     
    public function __construct()
    {
        parent::__construct();
    }
    
    
    public function handle(SteadfastService $steadfastService)
    {
        $orders = Order::where('steadfast_synced', true)
            ->whereNotIn('delivery_status', ['completed', 'cancelled'])
            ->whereNotNull('steadfast_consignment_id')
            ->get();

        foreach ($orders as $order) {
            try {
                $searchType = 'consignment';
                $searchValue = $order->steadfast_consignment_id;

                if (!$searchValue && $order->steadfast_invoice) {
                    $searchType = 'invoice';
                    $searchValue = $order->steadfast_invoice;
                }

                if (!$searchValue && $order->steadfast_tracking_code) {
                    $searchType = 'tracking';
                    $searchValue = $order->steadfast_tracking_code;
                }

                if (!$searchValue) {
                    continue;
                }

                $response = $steadfastService->getStatus($searchType, $searchValue);

                if (($response['status'] ?? null) !== 200) {
                    Log::warning("Steadfast status not found", [
                        'order_id' => $order->id,
                        'search_type' => $searchType,
                        'search_value' => $searchValue,
                        'response' => $response
                    ]);
                    continue;
                }

                $steadfastStatus = $response['delivery_status'] ?? null;
                if (!$steadfastStatus) {
                    continue;
                }

                $mappedStatus = $this->mapSteadfastStatus($steadfastStatus);

                if ($order->delivery_status !== $mappedStatus) {
                    $oldStatus = $order->delivery_status;

                    $order->update([
                        'delivery_status' => $mappedStatus,
                        'steadfast_last_status' => strtolower($steadfastStatus),
                        'steadfast_status_synced_at' => now()
                    ]);

                    Log::info('Steadfast status synced', [
                        'order_id' => $order->id,
                        'old' => $oldStatus,
                        'new' => $mappedStatus
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Steadfast sync failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('Steadfast order status sync completed.');
    }

    private function mapSteadfastStatus(string $status): string
    {
        return match (strtolower($status)) {
            'pending'    => 'pending',
            'approved'   => 'approved',
            'processing' => 'processing',
            'completed'  => 'completed',
            'cancelled'  => 'cancelled',
            default      => 'pending',
        };
    }

}
