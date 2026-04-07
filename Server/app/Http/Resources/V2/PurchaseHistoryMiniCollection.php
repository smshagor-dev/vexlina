<?php

namespace App\Http\Resources\V2;

use App\Services\OrderDeliveryVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseHistoryMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $verificationService = app(OrderDeliveryVerificationService::class);

        return [
            'data' => $this->collection->map(function ($data) use ($verificationService) {
                $pickupPoint = null;
                if ($data->shipping_type === 'pickup_point' && $data->pickup_point_id && $data->pickup_point) {
                    $holdDays = $data->pickup_point->holdDays();
                    $reachedAt = $data->delivery_history_date ?: $data->updated_at;
                    $deadline = $reachedAt ? Carbon::parse($reachedAt)->startOfDay()->addDays($holdDays) : null;
                    $daysLeft = $deadline ? max(0, Carbon::today()->diffInDays($deadline, false)) : null;

                    $pickupPoint = [
                        'id' => $data->pickup_point->id,
                        'name' => $data->pickup_point->getTranslation('name'),
                        'address' => $data->pickup_point->getTranslation('address'),
                        'phone' => $data->pickup_point->phone,
                        'internal_code' => $data->pickup_point->internal_code,
                        'working_hours' => $data->pickup_point->workingHoursLabel(),
                        'pickup_hold_days' => $holdDays,
                        'pickup_window_deadline' => optional($deadline)->toDateString(),
                        'pickup_window_days_left' => $daysLeft,
                        'is_return_due' => $data->delivery_status === 'reached' && $deadline ? Carbon::today()->greaterThanOrEqualTo($deadline->copy()->startOfDay()) : false,
                    ];
                }

                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'user_id' => intval($data->user_id),
                    'shipping_type' => $data->shipping_type,
                    'pickup_point' => $pickupPoint,
                    'payment_type' => ucwords(str_replace('_', ' ', $data->payment_type)),
                    'payment_status' => translate($data->payment_status),
                    'payment_status_string' => ucwords(str_replace('_', ' ', translate($data->payment_status))),
                    'delivery_status' => $data->delivery_status,
                    'delivery_status_string' => $data->delivery_status == translate('pending') ? translate("Order Placed") : ucwords(str_replace('_', ' ',  translate($data->delivery_status))),
                    'grand_total' => format_price(convert_price($data->grand_total)),
                    'date' => Carbon::createFromTimestamp($data->date)->format('d-m-Y'),
                    'delivery_verification_status' => (bool) $data->delivery_verification_status,
                    'delivery_verified_at' => optional($data->delivery_verified_at)->toDateTimeString(),
                    'customer_pickup_qr_payload' => $data->shipping_type === 'pickup_point' && $data->delivery_status === 'reached'
                        ? $verificationService->buildPickupQrPayload($data)
                        : null,
                    'customer_pickup_qr_image' => $data->shipping_type === 'pickup_point' && $data->delivery_status === 'reached'
                        ? $verificationService->buildPickupQrImageUrl($data)
                        : null,
                    'links' => [
                        'details' => ''
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
