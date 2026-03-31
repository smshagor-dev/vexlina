<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $walletCardDetails = $data->ensureWalletCardDetails();

                return [
                    'id' => (integer) $data->id,
                    'name' => $data->name,
                    'type' => $data->user_type,
                    'email' => $data->email,
                    'avatar' => $data->avatar,
                    'avatar_original' => uploaded_asset($data->avatar_original),
                    'address' => $data->address,
                    'city' => $data->city,
                    'country' => $data->country,
                    'postal_code' => $data->postal_code,
                    'phone' => $data->phone,
                    'wallet_card_number' => $walletCardDetails['number'],
                    'wallet_card_expiry_month' => $walletCardDetails['expiry_month'],
                    'wallet_card_expiry_year' => $walletCardDetails['expiry_year'],
                    'wallet_card_cvv' => $walletCardDetails['cvv'],
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
