@extends('pickup_points.layouts.app')

@section('panel_content')
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
                </table>
            </div>
            <div class="col-lg-6">
                <table class="table table-borderless">
                    <tr><td class="w-50 fw-600">{{ translate('Order date') }}:</td><td>{{ date('d-m-Y H:i A', $order->date) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Order status') }}:</td><td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Payment method') }}:</td><td>{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td></tr>
                    <tr><td class="w-50 fw-600">{{ translate('Grand total') }}:</td><td>{{ single_price($order->grand_total) }}</td></tr>
                </table>
            </div>
        </div>
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
