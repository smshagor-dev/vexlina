<?php

return [
    'api_key' => env('STEADFAST_API_KEY', 'py0clk54uvdkvw4lks09qlijbszowtzt'),
    'secret_key' => env('STEADFAST_SECRET_KEY', 'yvwi9vaj0tpbrtjr9lj4pyya'),
    'base_url' => env('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1'),

    'default_delivery_type' => 0, 
    'default_total_lot' => 1,
    
    'webhook_token' => env('STEADFAST_WEBHOOK_TOKEN'),
];