@extends('pickup_points.layouts.app')

@section('panel_content')
@php
    $pickupQrPayload = null;
    $pickupQrImage = null;
    if ($order->shipping_type === 'pickup_point') {
        $pickupQrPayload = app(\App\Services\OrderDeliveryVerificationService::class)->buildPickupQrPayload($order);
        $pickupQrImage = app(\App\Services\OrderDeliveryVerificationService::class)->buildPickupQrImageUrl($order);
    }
@endphp
<div class="aiz-titlebar mt-2 mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h3 class="fs-20 fw-700 text-dark">{{ translate('Order id') }}: {{ $order->code }}</h3>
        </div>
    </div>
</div>

<div class="card shadow-none rounded-0 border mt-4">
    <div class="card-header border-bottom-0">
        <b class="fs-16 fw-700 text-dark">{{ translate('Order Summary') }}</b>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6">
                <table class="table table-borderless">
                    <tr><td class="w-50 fw-600">{{ translate('Order Code') }}:</td><td>{{ $order->code }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Customer') }}:</td><td>{{ json_decode($order->shipping_address)->name ?? '-' }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Shipping address') }}:</td><td>{{ json_decode($order->shipping_address)->address ?? '-' }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Pickup Point') }}:</td><td>{{ optional($order->pickup_point)->getTranslation('name') }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Station Code') }}:</td><td>{{ optional($order->pickup_point)->internal_code ?: '-' }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Working Hours') }}:</td><td>{{ optional($order->pickup_point)->workingHoursLabel() }}</td></tr>
                </table>
            </div>
            <div class="col-lg-6">
                <table class="table table-borderless">
                    <tr><td class="w-50 fw-600">{{ translate('Order date') }}:</td><td>{{ date('d-m-Y H:i A', $order->date) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Order status') }}:</td><td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Payment method') }}:</td><td>{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Grand total') }}:</td><td>{{ single_price($order->grand_total) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Pickup Hold Window') }}:</td><td>{{ optional($order->pickup_point)->holdDays() }} {{ translate('days') }}</td></tr>
                </table>
            </div>
        </div>
        @if(optional($order->pickup_point)->instructions)
            <div class="alert alert-light border mt-3 mb-0">
                <strong>{{ translate('Pickup Instructions') }}:</strong> {{ $order->pickup_point->instructions }}
            </div>
        @endif
        @if($pickupQrPayload)
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="alert alert-light border mb-0">
                        <strong>{{ translate('Customer Pickup QR Verification') }}:</strong>
                        @if($order->delivery_verification_status)
                            <span class="badge badge-inline badge-success ml-2">{{ translate('Verified') }}</span>
                            <div class="mt-2 text-muted fs-13">
                                {{ translate('Verified at') }}: {{ optional($order->delivery_verified_at)->format('d-m-Y h:i A') ?: '-' }}
                            </div>
                        @else
                            <span class="badge badge-inline badge-warning ml-2">{{ translate('Pending') }}</span>
                            <div class="mt-2 text-muted fs-13">
                                {{ translate('Scan the customer QR from the pickup app to hand over and complete the order.') }}
                            </div>
                        @endif
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">{{ translate('Fallback verification code') }}</small>
                            <code>{{ $order->code }}</code>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="border rounded p-3 text-center h-100">
                        <img src="{{ $pickupQrImage }}" alt="{{ translate('Pickup QR') }}" class="img-fluid mb-2" style="max-width:200px;">
                        <div class="fs-12 text-muted">{{ translate('Customer Pickup QR') }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="card mt-4 shadow-none rounded-0 border">
    <div class="card-header border-bottom-0">
        <b class="fs-16 fw-700 text-dark">{{ translate('Order Details') }}</b>
    </div>
    <div class="card-body pb-0">
        <table class="table table-borderless table-responsive">
            <thead class="text-gray fs-12">
                <tr>
                    <th class="pl-0">#</th>
                    <th width="30%">{{ translate('Product') }}</th>
                    <th>{{ translate('Variation') }}</th>
                    <th>{{ translate('Quantity') }}</th>
                    <th>{{ translate('Price') }}</th>
                </tr>
            </thead>
            <tbody class="fs-14">
                @foreach ($order->orderDetails as $key => $orderDetail)
                    <tr>
                        <td class="pl-0">{{ $key + 1 }}</td>
                        <td>{{ optional($orderDetail->product)->getTranslation('name') ?? translate('Product Unavailable') }}</td>
                        <td>{{ $orderDetail->variation }}</td>
                        <td>{{ $orderDetail->quantity }}</td>
                        <td>{{ single_price($orderDetail->price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
