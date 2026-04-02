<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DeliveryBoyPurchaseHistoryMiniCollection extends ResourceCollection
{
    protected function isValidLatitude($value): bool
    {
        return $value !== null && is_numeric($value) && floatval($value) >= -90 && floatval($value) <= 90;
    }

    protected function isValidLongitude($value): bool
    {
        return $value !== null && is_numeric($value) && floatval($value) >= -180 && floatval($value) <= 180;
    }

    protected function defaultCoordinatePayload(): array
    {
        return [
            'available' => false,
            'lat' => 90.99,
            'lng' => 180.99,
        ];
    }

    protected function extractCoordinates(array $shipping_address): array
    {
        $lat = null;
        $lng = null;

        if (!empty($shipping_address['lat_lang'])) {
            $exploded_lat_lang = explode(',', $shipping_address['lat_lang']);
            if (count($exploded_lat_lang) >= 2) {
                $lat = floatval(trim($exploded_lat_lang[0]));
                $lng = floatval(trim($exploded_lat_lang[1]));
            }
        }

        if ($lat === null && isset($shipping_address['latitude'])) {
            $lat = floatval($shipping_address['latitude']);
        }

        if ($lng === null && isset($shipping_address['longitude'])) {
            $lng = floatval($shipping_address['longitude']);
        }

        if (($lat === null || $lng === null) && isset($shipping_address['location']) && is_array($shipping_address['location'])) {
            $lat = $lat ?? (isset($shipping_address['location']['lat']) ? floatval($shipping_address['location']['lat']) : null);
            $lng = $lng ?? (isset($shipping_address['location']['lng']) ? floatval($shipping_address['location']['lng']) : null);
        }

        $isValidLat = $this->isValidLatitude($lat);
        $isValidLng = $this->isValidLongitude($lng);

        return [
            'available' => $isValidLat && $isValidLng,
            'lat' => $isValidLat ? $lat : 90.99,
            'lng' => $isValidLng ? $lng : 180.99,
        ];
    }

    protected function extractCoordinatesFromUser($user): array
    {
        if (!$user) {
            return $this->defaultCoordinatePayload();
        }

        $address = $user->addresses()
            ->orderByDesc('set_default')
            ->orderByDesc('id')
            ->first();

        if (!$address) {
            return $this->defaultCoordinatePayload();
        }

        $lat = $this->isValidLatitude($address->latitude) ? floatval($address->latitude) : null;
        $lng = $this->isValidLongitude($address->longitude) ? floatval($address->longitude) : null;

        return [
            'available' => $lat !== null && $lng !== null,
            'lat' => $lat ?? 90.99,
            'lng' => $lng ?? 180.99,
        ];
    }

    protected function resolveStoreCoordinates($data): array
    {
        $lat = null;
        $lng = null;

        if ($data->shop) {
            $lat = $data->shop->delivery_pickup_latitude;
            $lng = $data->shop->delivery_pickup_longitude;
        } else {
            $lat = get_setting('delivery_pickup_latitude');
            $lng = get_setting('delivery_pickup_longitude');
        }

        $isValidLat = $this->isValidLatitude($lat);
        $isValidLng = $this->isValidLongitude($lng);

        return [
            'available' => $isValidLat && $isValidLng,
            'lat' => $isValidLat ? floatval($lat) : 90.99,
            'lng' => $isValidLng ? floatval($lng) : 180.99,
        ];
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $storeCoordinates = $this->resolveStoreCoordinates($data);
                $shipping_address = json_decode($data->shipping_address,true);
                $coordinates = is_array($shipping_address)
                    ? $this->extractCoordinates($shipping_address)
                    : $this->defaultCoordinatePayload();

                if (!$coordinates['available']) {
                    $coordinates = $this->extractCoordinatesFromUser($data->user);
                }

                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'user_id' => intval($data->user_id),
                    'payment_type' => ucwords(str_replace('_', ' ', translate($data->payment_type))) ,
                    'payment_status' => $data->payment_status,
                    'payment_status_string' => ucwords(str_replace('_', ' ', $data->payment_status)),
                    'delivery_status' => $data->delivery_status,
                    'delivery_status_string' => $data->delivery_status == 'pending'? "Order Placed" : ucwords(str_replace('_', ' ',  $data->delivery_status)),
                    'grand_total' => format_price($data->grand_total) ,
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s',$data->delivery_history_date)->format('d-m-Y'),
                    'cancel_request' => $data->cancel_request == 1,
                    'delivery_history_date' => $data->delivery_history_date,
                    'delivery_verification_status' => (bool) $data->delivery_verification_status,
                    'delivery_verified_at' => optional($data->delivery_verified_at)->toDateTimeString(),
                    'location_available' => $coordinates['available'],
                    'lat' => $coordinates['lat'],
                    'lang' => $coordinates['lng'],
                    'store_location_available' => $storeCoordinates['available'],
                    'delivery_pickup_latitude' => $storeCoordinates['lat'],
                    'delivery_pickup_longitude' => $storeCoordinates['lng'],
                    'links' => [
                        'details' => ""
                    ]
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
