@extends('frontend.layouts.user_panel')

@section('panel_content')
    <!-- Order id -->
    <div class="aiz-titlebar mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fs-20 fw-700 text-dark">{{ translate('Order id') }}: <span style="color: #fa3e00;">{{ $order->code }}</span></h1>
            </div>
        </div>
    </div>

    @php
        $first_order = $order->orderDetails->first();
        $gstin = get_seller_gstin($order);
    @endphp

    <!-- Order Summary -->
    <div class="card rounded-0 shadow-none border mb-4">
        <div class="card-header border-bottom-0" style="border-left: 4px solid #fa3e00; background-color: #fff9f7;">
            <h5 class="fs-16 fw-700 text-dark mb-0">
                <i class="las la-clipboard-list mr-2" style="color: #fa3e00;"></i>
                {{ translate('Order Summary') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table-borderless table">
                        <tr>
                            <td style="width: 50%; font-weight: 600;">
                                {{ translate('Order Code') }}:
                            </td>
                            <td>
                                <span style="
                                    display: inline-block;
                                    padding: 4px 10px;
                                    border-radius: 999px;
                                    background-color: #fa3e00;
                                    color: #ffffff;
                                    font-size: 14px;
                                    font-weight: 500;
                                ">
                                    {{ $order->code }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="w-50 fw-600">{{ translate('Customer') }}:</td>
                            <td>{{ json_decode($order->shipping_address)->name }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Email') }}:</td>
                            @if ($order->user_id != null)
                                <td>{{ $order->user->email }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping address') }}:</td>
                            <td>
                                <i class="las la-map-marker-alt mr-1" style="color: #fa3e00;"></i>
                                {{ json_decode($order->shipping_address)->address }},
                                {{ json_decode($order->shipping_address)->city }},
                                @if(isset(json_decode($order->shipping_address)->state)) {{ json_decode($order->shipping_address)->state }} - @endif
                                {{ json_decode($order->shipping_address)->postal_code }},
                                {{ json_decode($order->shipping_address)->country }}
                            </td>
                        </tr>
                        @if ( json_decode($order->billing_address) != null)
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Billing address') }}:</td>
                                <td>
                                    <i class="las la-file-invoice-dollar mr-1" style="color: #fa3e00;"></i>
                                    {{ json_decode($order->billing_address)->address }},
                                    {{ json_decode($order->billing_address)->city }},
                                    @if(isset(json_decode($order->billing_address)->state)) {{ json_decode($order->billing_address)->state }} - @endif
                                    {{ json_decode($order->billing_address)->postal_code }},
                                    {{ json_decode($order->billing_address)->country }}
                                </td>
                            </tr>
                        @endif
                        @if ($gstin != null && is_numeric($first_order->gst_amount))
                            <tr>
                                <td class="w-50 fw-600">{{ translate('GSTIN') }}:</td>
                                <td>
                                    <span class="badge badge-light border" style="border-color: #fa3e00 !important;">
                                        {{$gstin}}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table-borderless table">
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order date') }}:</td>
                            <td>
                                <i class="las la-calendar-alt mr-1" style="color: #fa3e00;"></i>
                                {{ date('d-m-Y H:i A', $order->date) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; font-weight: 600;">
                                {{ translate('Order status') }}:
                            </td>
                            <td>
                                <span style="
                                    display: inline-block;
                                    padding: 4px 10px;
                                    border-radius: 999px;
                                    background-color: #fa3e00;
                                    color: #ffffff;
                                    font-size: 13px;
                                    font-weight: 500;
                                    text-transform: capitalize;
                                ">
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="w-50 fw-600">{{ translate('Total order amount') }}:</td>
                            <td>
                                <span class="fw-700" style="color: #fa3e00;">
                                    {{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping method') }}:</td>
                            <td>
                                <i class="las la-shipping-fast mr-1" style="color: #fa3e00;"></i>
                                {{ translate('Flat shipping rate') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Payment method') }}:</td>
                            <td>
                                <i class="las la-credit-card mr-1" style="color: #fa3e00;"></i>
                                {{ ucfirst(translate(str_replace('_', ' ', $order->payment_type))) }}
                            </td>
                        </tr>
                        @if ($order->additional_info)
                            <tr>
                                <td class="fw-600" style="color: #fa3e00;">{{ translate('Additional Info') }}</td>
                                <td class="text-muted">{{ $order->additional_info }}</td>
                            </tr>
                        @endif
                        @if ($order->tracking_code)
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Tracking code') }}:</td>
                                <td>
                                    <span class="badge badge-light border" style="border-color: #fa3e00 !important;">
                                        <i class="las la-barcode mr-1"></i>
                                        {{ $order->tracking_code }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </table>
                    
                    <!-- Order Verification QR Block -->
                    <hr style="border-color: #fa3e0033;">
                    
                    <div class="mt-4">
                        <div class="fw-600 mb-2 fs-14 text-center text-md-left" style="color: #fa3e00;">
                            <i class="las la-qrcode mr-1"></i>
                            {{ translate('Order Verification Code') }}
                        </div>
                    
                        <div class="d-flex justify-content-center justify-content-md-start">
                            <div class="text-center">
                                <div class="border rounded p-3 d-inline-block mb-2" style="border-color: #fa3e00 !important; background: #fff9f7;">
                                    <img
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($order->code) }}"
                                        alt="QR Code for Order {{ $order->code }}"
                                        class="img-fluid"
                                        style="max-width: 140px; width: 100%; height: auto;"
                                    >
                                </div>
                                <div class="text-muted fs-12">
                                    {{ translate('Scan to verify order') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details -->
    <div class="row gutters-16">
        <div class="col-md-9">
            <div class="card rounded-0 shadow-none border mt-2 mb-4">
                <div class="card-header border-bottom-0" style="border-left: 4px solid #fa3e00; background-color: #fff9f7;">
                    <h5 class="fs-16 fw-700 text-dark mb-0">
                        <i class="las la-list-alt mr-2" style="color: #fa3e00;"></i>
                        {{ translate('Order Details') }}
                    </h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="aiz-table table">
                        <thead class="text-gray fs-12" style="background-color: #fff9f7;">
                            <tr>
                                <th class="pl-0" style="border-top: none;">#</th>
                                <th width="30%" style="border-top: none;">{{ translate('Product') }}</th>
                                <th data-breakpoints="md" style="border-top: none;">{{ translate('Variation') }}</th>
                                <th style="border-top: none;">{{ translate('Quantity') }}</th>
                                <th data-breakpoints="md" style="border-top: none;">{{ translate('Delivery Type') }}</th>
                                <th style="border-top: none;">{{ translate('Price') }}</th>
                                @if (addon_is_activated('refund_request'))
                                    <th data-breakpoints="md" style="border-top: none;">{{ translate('Refund') }}</th>
                                @endif
                                <th data-breakpoints="md" class="text-right pr-0" style="border-top: none;">{{ translate('Review') }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-14">
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td class="pl-0">
                                        <span class="badge badge-light border" style="border-color: #fa3e00 !important;">
                                            {{ sprintf('%02d', $key+1) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"
                                               class="text-dark hover-text" style="--hover-color: #fa3e00;">
                                                {{ \Illuminate\Support\Str::limit($orderDetail->product->getTranslation('name'), 24, '...') }}
                                            </a>
                                        @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"
                                               class="text-dark hover-text" style="--hover-color: #fa3e00;">
                                                {{ \Illuminate\Support\Str::limit($orderDetail->product->getTranslation('name'), 24, '...') }}
                                            </a>
                                        @else
                                            <strong class="text-danger">{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $orderDetail->variation }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill" style="background-color: #fa3e00; color: white;">
                                            {{ $orderDetail->quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                            <span style="
                                                display: inline-flex;
                                                align-items: center;
                                                padding: 4px 10px;
                                                border-radius: 999px;
                                                border: 1px solid #fa3e00;
                                                background-color: #f8f9fa;
                                                font-size: 13px;
                                                font-weight: 500;
                                                color: #000;
                                            ">
                                                <i class="las la-home" style="margin-right: 6px;"></i>
                                                {{ translate('Home Delivery') }}
                                            </span>
                                    
                                        @elseif ($order->shipping_type == 'pickup_point')
                                            @if ($order->pickup_point != null)
                                                <span style="
                                                    display: inline-flex;
                                                    align-items: center;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    border: 1px solid #fa3e00;
                                                    background-color: #f8f9fa;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                    color: #000;
                                                ">
                                                    <i class="las la-store-alt" style="margin-right: 6px;"></i>
                                                    {{ $order->pickup_point->name }}
                                                </span>
                                            @else
                                                {{ translate('Pickup Point') }}
                                            @endif
                                    
                                        @elseif($order->shipping_type == 'carrier')
                                            @if ($order->carrier != null)
                                                <span style="
                                                    display: inline-flex;
                                                    align-items: center;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    border: 1px solid #fa3e00;
                                                    background-color: #f8f9fa;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                    color: #000;
                                                ">
                                                    <i class="las la-truck" style="margin-right: 6px;"></i>
                                                    {{ $order->carrier->name }}
                                                </span>
                                                <br>
                                                <small style="
                                                    display: inline-block;
                                                    margin-top: 4px;
                                                    font-size: 12px;
                                                    color: #6c757d;
                                                ">
                                                    {{ translate('Transit Time').' - '.$order->carrier->transit_time }}
                                                </small>
                                            @else
                                                {{ translate('Carrier') }}
                                            @endif
                                        @endif
                                    </td>

                                    <td class="fw-700" style="color: #fa3e00;">{{ single_price($orderDetail->price) }}</td>
                                    @if (addon_is_activated('refund_request'))
                                        @php
                                            $no_of_max_day = $orderDetail->refund_days;
                                            $last_refund_date = null;
                                            if ($order->delivered_date && $no_of_max_day > 0) {
                                                $last_refund_date = Carbon\Carbon::parse($order->delivered_date)->addDays($no_of_max_day);
                                            }
                                            $today_date = Carbon\Carbon::now();
                                        @endphp
                                        <td>
                                            @if (
                                                $orderDetail->product != null &&
                                                $orderDetail->refund_request == null &&
                                                $last_refund_date &&
                                                $today_date <= $last_refund_date &&
                                                $order->payment_status == 'paid' &&
                                                $order->delivery_status == 'delivered'
                                            )
                                                <a href="{{ route('refund_request_send_page', $orderDetail->id) }}"
                                                   style="
                                                       display: inline-block;
                                                       padding: 6px 12px;
                                                       background-color: #fa3e00;
                                                       color: #ffffff;
                                                       text-decoration: none;
                                                       font-size: 13px;
                                                       font-weight: 500;
                                                       border: none;
                                                       cursor: pointer;
                                                   ">
                                                    {{ translate('Send') }}
                                                </a>
                                        
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 0)
                                                <span style="
                                                    display: inline-block;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    background-color: #ffc107;
                                                    color: #000000;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                ">
                                                    {{ translate('Pending') }}
                                                </span>
                                        
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 2)
                                                <span style="
                                                    display: inline-block;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    background-color: #dc3545;
                                                    color: #ffffff;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                ">
                                                    {{ translate('Rejected') }}
                                                </span>
                                        
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 1)
                                                <span style="
                                                    display: inline-block;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    background-color: #28a745;
                                                    color: #ffffff;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                ">
                                                    {{ translate('Approved') }}
                                                </span>
                                        
                                            @elseif ($orderDetail->product != null && $orderDetail->refund_days != 0)
                                                <span style="
                                                    display: inline-block;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    background-color: #6c757d;
                                                    color: #ffffff;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                ">
                                                    {{ translate('N/A') }}
                                                </span>
                                        
                                            @else
                                                <span style="
                                                    display: inline-block;
                                                    padding: 4px 10px;
                                                    border-radius: 999px;
                                                    background-color: #343a40;
                                                    color: #ffffff;
                                                    font-size: 13px;
                                                    font-weight: 500;
                                                ">
                                                    {{ translate('Non-refundable') }}
                                                </span>
                                            @endif
                                        </td>

                                    @endif
                                    <td class="text-xl-right pr-0">
                                        @if ($orderDetail->delivery_status == 'delivered')
                                            <a href="javascript:void(0);" onclick="product_review('{{ $orderDetail->product_id }}', '{{ $order->id }}')"
                                                class="btn btn-sm rounded-0"
                                                style="background-color: #fa3e00; color: white; border: none;">
                                                {{ translate('Review') }}
                                            </a>
                                        @else
                                            <span class="text-muted"><i class="las la-clock mr-1"></i>{{ translate('Not Delivered Yet') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Amount -->
        <div class="col-md-3">
            <div class="card rounded-0 shadow-none border mt-2">
                <div class="card-header border-bottom-0" style="border-left: 4px solid #fa3e00; background-color: #fff9f7;">
                    <b class="fs-16 fw-700 text-dark">
                        <i class="las la-money-bill-wave mr-2" style="color: #fa3e00;"></i>
                        {{ translate('Order Amount') }}
                    </b>
                </div>
                <div class="card-body pb-0">
                    <table class="table-borderless table">
                        <tbody>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Subtotal') }}</td>
                                <td class="text-right">
                                    <span>{{ single_price($order->orderDetails->sum('price')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Shipping') }}</td>
                                <td class="text-right">
                                    <span class="text-muted">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                </td>
                            </tr>
                            @if(is_numeric($first_order->gst_amount))
                                @if(same_state_shipping($order))
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('CGST') }}</td>
                                        <td class="text-right">
                                            <span class="text-muted">{{ single_price($order->orderDetails->sum('gst_amount')/2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('SGST') }}</td>
                                        <td class="text-right">
                                            <span class="text-muted">{{ single_price($order->orderDetails->sum('gst_amount')/2) }}</span>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('IGST') }}</td>
                                        <td class="text-right">
                                            <span class="text-muted">{{ single_price($order->orderDetails->sum('gst_amount')) }}</span>
                                        </td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td class="w-50 fw-600">{{ translate('Tax') }}</td>
                                    <td class="text-right">
                                        <span class="text-muted">{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Coupon') }}</td>
                                <td class="text-right">
                                    <span class="text-success">-{{ single_price($order->coupon_discount) }}</span>
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td class="w-50 fw-700 fs-16">{{ translate('Total') }}</td>
                                <td class="text-right">
                                    <strong class="fs-16" style="color: #fa3e00;">{{ single_price($order->grand_total) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($order->payment_status == 'unpaid' && $order->delivery_status == 'pending' && $order->manual_payment == 0)
                <button
                    @if(addon_is_activated('offline_payment'))
                        onclick="select_payment_type({{ $order->id }})"
                    @else
                        onclick="online_payment({{ $order->id }})"
                    @endif
                    class="btn btn-block btn-lg rounded-0 fw-600"
                    style="background-color: #fa3e00; color: white; border: none; margin-top: 20px;">
                    <i class="las la-credit-card mr-2"></i>
                    {{ translate('Make Payment') }}
                </button>
            @endif
        </div>
    </div>
@endsection

@section('modal')
    <!-- Product Review Modal -->
    <div class="modal fade" id="product-review-modal">
        <div class="modal-dialog">
            <div class="modal-content" id="product-review-modal-content">

            </div>
        </div>
    </div>

    <!-- Select Payment Type Modal -->
    <div class="modal fade" id="payment_type_select_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom-color: #fa3e0033;">
                    <h5 class="modal-title" id="exampleModalLabel" style="color: #fa3e00;">
                        <i class="las la-credit-card mr-2"></i>
                        {{ translate('Select Payment Type') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fa3e00;"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="order_id" name="order_id" value="{{ $order->id }}">
                    <div class="row">
                        <div class="col-md-2">
                            <label>{{ translate('Payment Type') }}</label>
                        </div>
                        <div class="col-md-10">
                            <div class="mb-3">
                                <select class="form-control aiz-selectpicker rounded-0" onchange="payment_modal(this.value)"
                                    data-minimum-results-for-search="Infinity" style="border-color: #fa3e00;">
                                    <option value="">{{ translate('Select One') }}</option>
                                    <option value="online">{{ translate('Online payment') }}</option>
                                    <option value="offline">{{ translate('Offline payment') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 transition-3d-hover mr-1"
                            id="payment_select_type_modal_cancel" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Online payment Modal -->
    <div class="modal fade" id="online_payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom-color: #fa3e0033;">
                    <h5 class="modal-title" id="exampleModalLabel" style="color: #fa3e00;">
                        <i class="las la-globe mr-2"></i>
                        {{ translate('Make Payment') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fa3e00;"></button>
                </div>
                <div class="modal-body gry-bg px-3 pt-3" style="overflow-y: inherit;">
                    <form class="" action="{{ route('order.re_payment') }}" method="post">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Payment Method') }}</label>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-3">
                                    <select class="form-control selectpicker rounded-0" data-live-search="true" name="payment_option" required style="border-color: #fa3e00;">
                                        @include('partials.online_payment_options')
                                        @if (get_setting('wallet_system') == 1 && (auth()->user()->balance >= $order->grand_total))
                                            <option value="wallet">{{ translate('Wallet') }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 transition-3d-hover mr-1"
                                data-dismiss="modal" style="border-color: #fa3e00; color: #fa3e00;">
                                {{ translate('Cancel') }}
                            </button>
                            <button type="submit"
                                class="btn btn-sm rounded-0 transition-3d-hover mr-1"
                                style="background-color: #fa3e00; color: white; border: none;">
                                {{ translate('Confirm') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- offline payment Modal -->
    <div class="modal fade" id="offline_order_re_payment_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom-color: #fa3e0033;">
                    <h5 class="modal-title" id="exampleModalLabel" style="color: #fa3e00;">
                        <i class="las la-university mr-2"></i>
                        {{ translate('Offline Order Payment') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fa3e00;"></button>
                </div>
                <div id="offline_order_re_payment_modal_body"></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function product_review(product_id,order_id) {
            $.post('{{ route('product_review_modal') }}', {
                _token: '{{ @csrf_token() }}',
                product_id: product_id,
                order_id: order_id
            }, function(data) {
                $('#product-review-modal-content').html(data);
                $('#product-review-modal').modal('show', {
                    backdrop: 'static'
                });
                AIZ.extra.inputRating();
            });
        }

        function select_payment_type(id) {
            $('#payment_type_select_modal').modal('show');
        }

        function payment_modal(type) {
            if (type == 'online') {
                $("#payment_select_type_modal_cancel").click();
                online_payment();
            } else if (type == 'offline') {
                $("#payment_select_type_modal_cancel").click();
                $.post('{{ route('offline_order_re_payment_modal') }}', {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}'
                }, function(data) {
                    $('#offline_order_re_payment_modal_body').html(data);
                    $('#offline_order_re_payment_modal').modal('show');
                });
            }
        }

        function online_payment() {
            $('input[name=customer_package_id]').val();
            $('#online_payment_modal').modal('show');
        }
    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrValue = document.getElementById('qrValue').value;
        const qrImg = document.getElementById('orderQr');

        if (qrValue) {
            const size = 200;
            const encodedData = encodeURIComponent(qrValue);

            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodedData}`;
        } else {
            qrImg.style.display = 'none';
        }
        
        // Add CSS for hover effects
        const style = document.createElement('style');
        style.textContent = `
            .hover-text:hover {
                color: #fa3e00 !important;
                text-decoration: underline;
            }
            .badge-light:hover {
                background-color: #fa3e00 !important;
                color: white !important;
                border-color: #fa3e00 !important;
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection