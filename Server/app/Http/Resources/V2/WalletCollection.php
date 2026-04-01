<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'transaction_number' => $data->ensureTransactionNumber(),
                    'amount' => single_price(($data->amount)),
                    'raw_amount' => (float) $data->amount,
                    'payment_method' => $data->displayPaymentMethod(),
                    'approval_string' => $data->displayStatus(),
                    'direction' => $data->transferDirection(),
                    'counterparty' => $data->counterpartyLabel(),
                    'date' => Carbon::createFromTimestamp(strtotime($data->created_at))->format('d-m-Y'),
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'result' => true,
            'status' => 200
        ];
    }
}
