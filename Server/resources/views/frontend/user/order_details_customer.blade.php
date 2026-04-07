@extends('frontend.layouts.user_panel')

@section('panel_content')
    @php
        $pickupQrPayload = null;
        $pickupQrImage = null;
        $pickupDeadline = null;
        $pickupDaysLeft = null;
        $pickupIsReturnDue = false;

        if ($order->shipping_type === 'pickup_point' && $order->pickup_point) {
            $pickupQrPayload = app(\App\Services\OrderDeliveryVerificationService::class)->buildPickupQrPayload($order);
            $pickupQrImage = app(\App\Services\OrderDeliveryVerificationService::class)->buildPickupQrImageUrl($order);
            $pickupHoldDays = $order->pickup_point->holdDays();
            $pickupReachedAt = $order->delivery_history_date ?: $order->updated_at;
            if ($pickupReachedAt) {
                $pickupDeadline = \Carbon\Carbon::parse($pickupReachedAt)->startOfDay()->addDays($pickupHoldDays);
                $pickupDaysLeft = max(0, \Carbon\Carbon::today()->diffInDays($pickupDeadline, false));
                $pickupIsReturnDue = $order->delivery_status === 'reached' && \Carbon\Carbon::today()->greaterThanOrEqualTo($pickupDeadline->copy()->startOfDay());
            }
        }
    @endphp
    <style>
        .customer-order-page {
            padding: 6px 0 18px;
        }
        .customer-order-page .page-shell {
            background: linear-gradient(180deg, #fffaf8 0%, #ffffff 28%, #fff7f1 100%);
            border: 1px solid #fa3e0017;
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 18px 45px rgba(250, 62, 0, 0.06);
        }
        .customer-order-page .page-heading {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }
        .customer-order-page .page-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        .customer-order-page .page-subtitle {
            margin-top: 6px;
            font-size: 13px;
            color: #6b7280;
        }
        .customer-order-page .order-code-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, #fa3e00 0%, #ff7b4d 100%);
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(250, 62, 0, 0.18);
        }
        .customer-order-page .smart-card {
            border: 1px solid #fa3e0015 !important;
            border-radius: 20px !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            background: #ffffff;
        }
        .customer-order-page .smart-card .card-header {
            padding: 16px 18px;
            border-bottom: 1px solid #fa3e0014 !important;
            border-left: none !important;
            background: linear-gradient(90deg, #fff8f4 0%, #ffffff 100%) !important;
        }
        .customer-order-page .section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: rgba(250, 62, 0, 0.08);
            color: #fa3e00;
            margin-right: 10px;
        }
        .customer-order-page .summary-table td,
        .customer-order-page .amount-table td {
            padding-top: 10px;
            padding-bottom: 10px;
            border-top: none;
            vertical-align: top;
        }
        .customer-order-page .summary-table tr + tr td,
        .customer-order-page .amount-table tr + tr td {
            border-top: 1px dashed #f1d5ca;
        }
        .customer-order-page .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid #fa3e0030;
            background: #fff8f4;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
        }
        .customer-order-page .qr-shell {
            border: 1px solid #fa3e0040;
            border-radius: 18px;
            padding: 18px;
            background: linear-gradient(135deg, #fffaf8 0%, #ffffff 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.65);
        }
        .customer-order-page .qr-image-box {
            border: 1px solid #f3dfd7;
            border-radius: 18px;
            padding: 12px;
            display: inline-block;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        }
        .customer-order-page .support-box {
            border: 1px solid #fa3e0018;
            border-radius: 16px;
            padding: 16px;
            background: #fffaf7;
        }
        .customer-order-page .qr-side-card {
            height: 100%;
        }
        .customer-order-page .amount-side-card {
            height: 100%;
            position: sticky;
            top: 18px;
        }
        .customer-order-page .details-table thead tr {
            background: #fff8f4;
        }
        .customer-order-page .details-table-wrap {
            border: 1px solid #fa3e0010;
            border-radius: 18px;
            padding: 10px 12px;
            background: linear-gradient(180deg, #fffdfa 0%, #ffffff 100%);
        }
        .customer-order-page .details-table th {
            border-top: none !important;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .02em;
            font-weight: 700;
            padding-top: 14px;
            padding-bottom: 14px;
        }
        .customer-order-page .details-table td {
            vertical-align: middle;
            padding-top: 16px;
            padding-bottom: 16px;
            border-color: #f5e3db;
        }
        .customer-order-page .details-table tbody tr:hover {
            background: #fffaf7;
        }
        .customer-order-page .product-link {
            font-weight: 600;
            line-height: 1.65;
            color: #1f2937 !important;
            text-decoration: none !important;
            transition: color .2s ease;
        }
        .customer-order-page .product-link:hover {
            color: #fa3e00 !important;
        }
        .customer-order-page .variation-chip,
        .customer-order-page .qty-chip,
        .customer-order-page .refund-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
        }
        .customer-order-page .variation-chip {
            background: #fff4ed;
            color: #7c2d12;
            border: 1px solid #fed7c7;
        }
        .customer-order-page .qty-chip {
            min-width: 34px;
            background: linear-gradient(135deg, #fa3e00 0%, #ff7b4d 100%);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(250, 62, 0, 0.16);
        }
        .customer-order-page .price-value {
            font-size: 18px;
            font-weight: 700;
            color: #fa3e00;
            white-space: nowrap;
        }
        .customer-order-page .refund-chip-dark {
            background: #374151;
            color: #ffffff;
        }
        .customer-order-page .refund-chip-warning {
            background: #fff3cd;
            color: #7c5700;
        }
        .customer-order-page .refund-chip-danger {
            background: #ffe2e2;
            color: #b42318;
        }
        .customer-order-page .refund-chip-success {
            background: #dcfce7;
            color: #166534;
        }
        .customer-order-page .refund-chip-muted {
            background: #e5e7eb;
            color: #4b5563;
        }
        .customer-order-page .review-pending {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            color: #6b7280;
            line-height: 1.5;
        }
        .customer-order-page .amount-card-body {
            padding-top: 6px;
        }
        .customer-order-page .amount-table tbody tr:last-child td {
            padding-top: 16px;
            padding-bottom: 12px;
        }
        .customer-order-page .amount-total-row td {
            border-top: 1px solid #f1d5ca !important;
        }
        .customer-order-page .amount-total-value {
            font-size: 22px;
            font-weight: 800;
            color: #fa3e00;
            line-height: 1.2;
        }
        .customer-order-page .amount-table td:last-child {
            text-align: right;
        }
        .customer-order-page .delivery-type-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #fa3e0030;
            background: #fff8f4;
            color: #111827;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
        }
        .customer-order-page .delivery-type-pill i {
            margin-right: 6px;
        }
        .customer-order-page .review-btn {
            background: linear-gradient(135deg, #fa3e00 0%, #ff7b4d 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 7px 14px;
            font-weight: 600;
            box-shadow: 0 10px 22px rgba(250, 62, 0, 0.15);
        }
        .customer-order-page .pay-btn {
            border-radius: 16px;
            background: linear-gradient(135deg, #fa3e00 0%, #ff7b4d 100%);
            box-shadow: 0 14px 28px rgba(250, 62, 0, 0.18);
        }
        @media (max-width: 767px) {
            .customer-order-page .page-shell {
                padding: 16px;
                border-radius: 18px;
            }
            .customer-order-page .page-title {
                font-size: 20px;
            }
            .customer-order-page .page-heading {
                margin-bottom: 18px;
            }
            .customer-order-page .details-table-wrap {
                padding: 8px;
            }
        }
    </style>

    <div class="customer-order-page">
        <div class="page-shell">
            <!-- Order id -->
            <div class="page-heading">
                <div>
                    <h1 class="page-title">{{ translate('Order details') }}</h1>
                    <div class="page-subtitle">
                        {{ translate('Track status, payment, delivery method, and pickup readiness from one place.') }}
                    </div>
                </div>
                <div class="order-code-pill">
                    <i class="las la-receipt"></i>
                    <span>{{ translate('Order ID') }}: {{ $order->code }}</span>
                </div>
            </div>

    @php
        $first_order = $order->orderDetails->first();
        $gstin = get_seller_gstin($order);
    @endphp

    <!-- Order Summary -->
    <div class="card rounded-0 shadow-none border mb-4 smart-card">
        <div class="card-header border-bottom-0">
            <h5 class="fs-16 fw-700 text-dark mb-0">
                <span class="section-icon"><i class="las la-clipboard-list"></i></span>
                {{ translate('Order Summary') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table-borderless table summary-table">
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
                    <table class="table-borderless table summary-table">
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
                                @if ($order->shipping_type === 'pickup_point')
                                    {{ translate('Pickup Point') }}
                                @elseif ($order->shipping_type === 'carrier')
                                    {{ translate('Carrier Delivery') }}
                                @else
                                    {{ translate('Home Delivery') }}
                                @endif
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
                </div>
            </div>
        </div>
    </div>

    <div class="row gutters-16">
        <div class="col-lg-8">
            <div class="card rounded-0 shadow-none border mt-2 mb-4 smart-card qr-side-card">
                <div class="card-header border-bottom-0">
                    <h5 class="fs-16 fw-700 text-dark mb-0">
                        <span class="section-icon"><i class="las la-qrcode"></i></span>
                        {{ $order->shipping_type === 'pickup_point' && $order->delivery_status === 'reached' ? translate('Pickup QR Code') : translate('Order Verification Code') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($order->shipping_type === 'pickup_point' && $order->delivery_status === 'reached' && $pickupQrPayload)
                        <div class="qr-shell">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="mb-2" style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                        <strong style="padding-right: 8px;">{{ translate('Ready for pickup') }}</strong>
                                        @if($pickupIsReturnDue)
                                            <span class="badge badge-danger" style="white-space: normal; line-height: 1.35; display: inline-block; max-width: 100%;">{{ translate('Pickup Window Expired') }}</span>
                                        @elseif($pickupDaysLeft !== null)
                                            <span class="badge badge-success" style="white-space: normal; line-height: 1.35; display: inline-block; max-width: 100%;">
                                                {{ $pickupDaysLeft }} {{ translate('day(s) left') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-muted fs-13 mb-2">
                                        {{ translate('Show this QR code at the pickup point to receive your order.') }}
                                    </div>
                                    <div class="fs-13 mb-1">
                                        <strong>{{ translate('Pickup Point') }}:</strong> {{ optional($order->pickup_point)->name ?? '-' }}
                                    </div>
                                    <div class="fs-13 mb-1">
                                        <strong>{{ translate('Address') }}:</strong> {{ optional($order->pickup_point)->address ?? '-' }}
                                    </div>
                                    <div class="fs-13 mb-1">
                                        <strong>{{ translate('Phone') }}:</strong> {{ optional($order->pickup_point)->phone ?? '-' }}
                                    </div>
                                    <div class="fs-13 mb-1">
                                        <strong>{{ translate('Branch / Station Code') }}:</strong> {{ optional($order->pickup_point)->internal_code ?? '-' }}
                                    </div>
                                    <div class="fs-13 mb-1">
                                        <strong>{{ translate('Working Hours') }}:</strong> {{ optional($order->pickup_point)->workingHoursLabel() }}
                                    </div>
                                    @if (!empty(optional($order->pickup_point)->instructions))
                                        <div class="fs-13 mb-1">
                                            <strong>{{ translate('Instructions') }}:</strong> {{ optional($order->pickup_point)->instructions }}
                                        </div>
                                    @endif
                                    <div class="fs-13 mb-1">
                                        <strong>{{ translate('Return Support') }}:</strong>
                                        {{ optional($order->pickup_point)->supportsReturn() ? translate('Available') : translate('Not Available') }}
                                    </div>
                                    <div class="fs-13">
                                        <strong>{{ translate('Collect Before') }}:</strong> {{ optional($pickupDeadline)->format('d-m-Y') ?? '-' }}
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mt-3 mt-md-0">
                                    <div class="qr-image-box">
                                        <img
                                            src="{{ $pickupQrImage }}"
                                            alt="Pickup QR for Order {{ $order->code }}"
                                            class="img-fluid"
                                            style="max-width: 170px; width: 100%; height: auto;"
                                        >
                                    </div>
                                    <div class="text-muted fs-12 mt-2">
                                        {{ translate('Customer pickup QR') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-center justify-content-md-start">
                            <div class="text-center">
                                <div class="qr-image-box mb-2">
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
                    @endif

                    @if ($order->assign_delivery_boy && $order->delivery_boy)
                        <hr style="border-color: #fa3e0033;">
                        <div class="mt-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start support-box">
                                <div class="mb-3 mb-md-0">
                                    <div class="fw-700 fs-15 text-dark">{{ translate('Delivery Boy Support') }}</div>
                                    <div class="text-muted fs-13">
                                        {{ $order->delivery_boy->name }}
                                        @if ($order->delivery_boy->phone)
                                            <span class="d-block">{{ $order->delivery_boy->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap">
                                    <a href="{{ route('conversations.customer_delivery', encrypt($order->id)) }}"
                                       class="btn btn-soft-primary btn-sm mr-2 mb-2">
                                        <i class="las la-comment-dots mr-1"></i>{{ translate('Message Delivery Boy') }}
                                    </a>
                                    @if ($order->delivery_boy->phone)
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->delivery_boy->phone) }}"
                                           class="btn btn-primary btn-sm mb-2">
                                            <i class="las la-phone mr-1"></i>{{ translate('Call Delivery Boy') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Amount -->
            <div class="card rounded-0 shadow-none border mt-2 smart-card amount-side-card">
                <div class="card-header border-bottom-0">
                    <b class="fs-16 fw-700 text-dark">
                        <span class="section-icon"><i class="las la-money-bill-wave"></i></span>
                        {{ translate('Order Amount') }}
                    </b>
                </div>
                <div class="card-body pb-0 amount-card-body">
                    <table class="table-borderless table amount-table">
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
                            <tr class="border-top amount-total-row">
                                <td class="w-50 fw-700 fs-16">{{ translate('Total') }}</td>
                                <td class="text-right">
                                    <strong class="amount-total-value">{{ single_price($order->grand_total) }}</strong>
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
                    class="btn btn-block btn-lg fw-600 pay-btn"
                    style="color: white; border: none; margin-top: 20px;">
                    <i class="las la-credit-card mr-2"></i>
                    {{ translate('Make Payment') }}
                </button>
            @endif
        </div>
    </div>

    <!-- Order Details -->
    <div class="row gutters-16">
        <div class="col-12">
            <div class="card rounded-0 shadow-none border mt-2 mb-4 smart-card">
                <div class="card-header border-bottom-0">
                    <h5 class="fs-16 fw-700 text-dark mb-0">
                        <span class="section-icon"><i class="las la-list-alt"></i></span>
                        {{ translate('Order Details') }}
                    </h5>
                </div>
                <div class="card-body table-responsive">
                    <div class="details-table-wrap">
                    <table class="aiz-table table details-table mb-0">
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
                                               class="product-link hover-text" style="--hover-color: #fa3e00;">
                                                {{ \Illuminate\Support\Str::limit($orderDetail->product->getTranslation('name'), 24, '...') }}
                                            </a>
                                        @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"
                                               class="product-link hover-text" style="--hover-color: #fa3e00;">
                                                {{ \Illuminate\Support\Str::limit($orderDetail->product->getTranslation('name'), 24, '...') }}
                                            </a>
                                        @else
                                            <strong class="text-danger">{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="variation-chip">{{ $orderDetail->variation ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="qty-chip">
                                            {{ $orderDetail->quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                            <span class="delivery-type-pill">
                                                <i class="las la-home" style="margin-right: 6px;"></i>
                                                {{ translate('Home Delivery') }}
                                            </span>
                                    
                                        @elseif ($order->shipping_type == 'pickup_point')
                                            <span class="delivery-type-pill">
                                                <i class="las la-store-alt"></i>
                                                {{ translate('Pickup Point') }}
                                            </span>
                                    
                                        @elseif($order->shipping_type == 'carrier')
                                            <span class="delivery-type-pill">
                                                <i class="las la-truck"></i>
                                                {{ translate('Carrier Delivery') }}
                                            </span>
                                            @if ($order->carrier != null)
                                                <br>
                                                <small style="
                                                    display: inline-block;
                                                    margin-top: 6px;
                                                    font-size: 12px;
                                                    color: #6c757d;
                                                ">
                                                    {{ $order->carrier->name }}
                                                    @if(!empty($order->carrier->transit_time))
                                                        · {{ translate('Transit Time').' - '.$order->carrier->transit_time }}
                                                    @endif
                                                </small>
                                            @endif
                                        @endif
                                    </td>

                                    <td><span class="price-value">{{ single_price($orderDetail->price) }}</span></td>
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
                                                <span class="refund-chip refund-chip-warning">
                                                    {{ translate('Pending') }}
                                                </span>
                                        
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 2)
                                                <span class="refund-chip refund-chip-danger">
                                                    {{ translate('Rejected') }}
                                                </span>
                                        
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 1)
                                                <span class="refund-chip refund-chip-success">
                                                    {{ translate('Approved') }}
                                                </span>
                                        
                                            @elseif ($orderDetail->product != null && $orderDetail->refund_days != 0)
                                                <span class="refund-chip refund-chip-muted">
                                                    {{ translate('N/A') }}
                                                </span>
                                        
                                            @else
                                                <span class="refund-chip refund-chip-dark">
                                                    {{ translate('Non-refundable') }}
                                                </span>
                                            @endif
                                        </td>

                                    @endif
                                    <td class="text-xl-right pr-0">
                                        @if ($orderDetail->delivery_status == 'delivered')
                                            <a href="javascript:void(0);" onclick="product_review('{{ $orderDetail->product_id }}', '{{ $order->id }}')"
                                                class="btn btn-sm review-btn"
                                                style="color: white;">
                                                {{ translate('Review') }}
                                            </a>
                                        @else
                                            <span class="review-pending"><i class="las la-clock"></i>{{ translate('Not Delivered Yet') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
