@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-700 fs-20 fs-md-24 text-dark">{{ translate('Track Order') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item has-transition opacity-50 hov-opacity-100">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home') }}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        "{{ translate('Track Order') }}"
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="container text-left">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-lg-8 mx-auto">
                {{-- Search Form --}}
                <form action="{{ route('orders.track') }}" method="GET">
                    <div class="bg-white border rounded-0">
                        <div class="fs-15 fw-600 p-3 border-bottom text-center">
                            {{ translate('Check Your Order Status') }}
                        </div>
                        <div class="form-box-content p-3">
                            <div class="form-group">
                                <input type="text" class="form-control rounded-0 mb-3" 
                                       placeholder="{{ translate('Order Code / Invoice / Tracking ID')}}" 
                                       name="search" value="{{ request('search') }}" required>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary rounded-0 w-150px">
                                    {{ translate('Track Order') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Display order info if found --}}
        @isset($order)
        <div class="bg-white border rounded-0 mt-5">
            <div class="fs-15 fw-600 p-3">
                {{ translate('Order Summary') }}
            </div>
            <div class="p-3">
                <div class="row">
                    <div class="col-lg-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Order Code')}}</td>
                                <td>{{ $order->code }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Customer')}}:</td>
                                <td>{{ json_decode($order->shipping_address)->name }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Email')}}:</td>
                                <td>{{ $order->user->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Mobile Number')}}:</td>
                                <td>{{ $order->user->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Shipping address')}}:</td>
                                <td>
                                    {{ json_decode($order->shipping_address)->address }},
                                    {{ json_decode($order->shipping_address)->city }},
                                    {{ json_decode($order->shipping_address)->country }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-lg-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Order date')}}:</td>
                                <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Total order amount')}}:</td>
                                <td style="font-weight:600; color:#28a745;">
                                    {{ single_price($order->grand_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Shipping method')}}:</td>
                                <td>{{ translate('Flat shipping rate') }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Payment method')}}:</td>
                                <td>{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Order Status')}}:</td>
                                <td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td>
                            </tr>
                            @if(!empty($order->tracking_code))
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Tracking code') }}:</td>
                                <td>{{ $order->tracking_code }}</td>
                            </tr>
                            @endif

                            <tr>
                                <td class="w-50 fw-600">{{ translate('Order Code')}}</td>
                                <td>{{ $order->invoice ?? $order->code }}</td>
                            </tr>
                            @if(!empty($steadfastStatus))
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Delivery Status')}}:</td>
                                <td>
                                    @if($steadfastStatus)
                                        @php
                                            $displayStatus = ($steadfastStatus === 'in_review')
                                                ? 'pending'
                                                : $steadfastStatus;
                                        @endphp
                                
                                        <span
                                            style="
                                                display: inline-block;
                                                padding: 4px 10px;
                                                font-size: 12px;
                                                font-weight: 600;
                                                color: #0c5460;
                                                background-color: #d1ecf1;
                                                border-radius: 12px;
                                                text-transform: capitalize;
                                            ">
                                            {{ ucfirst(str_replace('_',' ', $displayStatus)) }}
                                        </span>
                                    @else
                                        <span style="color:#999; font-size:12px;">
                                            Not available
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products --}}
        @foreach ($order->orderDetails as $orderDetail)
            @if($orderDetail->product)
            <div class="bg-white border rounded-0 mt-4">
                <div class="p-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ translate('Product Name') }}</th>
                                <th>{{ translate('Quantity') }}</th>
                                <th>{{ translate('Shipped By') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $orderDetail->product->getTranslation('name') }} ({{ $orderDetail->variation }})</td>
                                <td>{{ $orderDetail->quantity }}</td>
                                <td>Vexlina</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
        @endisset
    </div>
</section>
@endsection
