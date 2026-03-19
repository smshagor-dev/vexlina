<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SteadfastKey;
use Illuminate\Support\Facades\Cache;

class SteadfastService
{
    private $base_url;
    private $api_key;
    private $secret_key;

    public function __construct()
    {
        $keys = Cache::rememberForever('steadfast_keys', function () {
            return SteadfastKey::first();
        });
    
        if (!$keys) {
            throw new \Exception('Steadfast credentials not found');
        }
    
        $this->base_url  = $keys->steadfast_base_url;
        $this->api_key   = $keys->steadfast_api_key;
        $this->secret_key = $keys->steadfast_secret_key;
    }

    /**
     * Single Order
     */
    public function createOrder(array $orderData)
    {
        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'Api-Key' => $this->api_key,
                    'Secret-Key' => $this->secret_key,
                ])
                ->post($this->base_url . '/create_order', $orderData);
    
            Log::info('Steadfast response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
    
            if (!$response->successful()) {
                return [
                    'status'  => $response->status(),
                    'error'   => true,
                    'message' => $response->body(),
                ];
            }
    
            return $response->json();
    
        } catch (\Throwable $e) {
            Log::error('Steadfast HTTP exception', [
                'message' => $e->getMessage(),
            ]);
    
            throw $e; // DO NOT swallow this
        }
    }
    
    // public function createOrder(array $orderData) 
    // { 
    //     try { 
    //         $response = Http::timeout(30)->withHeaders([ 
    //             'Api-Key' => $this->api_key, 
    //             'Secret-Key' => $this->secret_key, 
    //             'Content-Type' => 'application/json' 
    //             ])->post($this->base_url . '/create_order', $orderData); 
                
    //         return $response->json(); 
            
    //     } 
                    
    //         catch (\Exception $e) {
    //         Log::error('Steadfast API Error: ' . $e->getMessage()); 
                
    //     return ['status' => 'error', 'message' => $e->getMessage()]; 
                        
    //     } 
        
    // }


    /**
     * Bulk Order
     */
    public function createBulkOrder(array $orders)
    {
        try {
            $response = Http::timeout(60)->withHeaders([
                'Api-Key' => $this->api_key,
                'Secret-Key' => $this->secret_key,
                'Content-Type' => 'application/json'
            ])->post($this->base_url . '/create_order/bulk-order', [
                'data' => json_encode($orders)
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Steadfast Bulk API Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Status Check
     */
    public function getStatus($type, $value)
    {
        try {
            $endpoints = [
                'consignment' => '/status_by_cid/',
                'invoice' => '/status_by_invoice/',
                'tracking' => '/status_by_trackingcode/'
            ];

            if (!isset($endpoints[$type])) {
                return null;
            }

            $response = Http::timeout(30)->withHeaders([
                'Api-Key' => $this->api_key,
                'Secret-Key' => $this->secret_key,
                'Content-Type' => 'application/json'
            ])->get($this->base_url . $endpoints[$type] . $value);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Steadfast Status API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Balance Check
     */
    public function getBalance()
    {
        try {
            $response = Http::withHeaders([
                'Api-Key' => $this->api_key,
                'Secret-Key' => $this->secret_key,
                'Content-Type' => 'application/json'
            ])->get($this->base_url . '/get_balance');

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Steadfast Balance API Error: ' . $e->getMessage());
            return null;
        }
    }
}