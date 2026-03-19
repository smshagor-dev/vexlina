@foreach($orders as $order)
  <div class="mb-4 border rounded" style="border-color: #fa3e0033 !important; overflow: hidden;">
      <!-- Order Header -->
      <div class="p-3" style="background-color: #fff9f7; border-bottom: 1px solid #fa3e0033;">
          <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="mb-1 fs-16 fw-700 mb-0">
                    <a class="text-dark hover-orange" href="{{route('purchase_history.details', encrypt($order->id))}}" 
                       style="--hover-color: #fa3e00;">
                        <i class="las la-receipt mr-2" style="color: #fa3e00;"></i>
                        <span style="color: #fa3e00;">{{ $order->code }}</span>
                    </a>
                </p>
                <span class="text-muted fs-12 d-block">
                    <i class="las la-calendar-alt mr-1" style="color: #fa3e00;"></i>
                    {{ date('d-m-Y', $order->date) }}
                </span>
            </div>
        
            <!-- PC Action Buttons (visible only on desktop) -->
            <div class="d-none d-md-flex gap-2 align-items-center">
                <a type="button" href="{{ route('re_order', encrypt($order->id)) }}" 
                   class="btn btn-sm rounded px-3"
                   style="background-color: #fa3e00; color: white; border: none; font-size: 13px;">
                    <i class="las la-redo-alt mr-1"></i> {{ translate('Reorder') }}
                </a>
                
                <a class="btn btn-sm border rounded px-3"
                   href="{{route('purchase_history.details', encrypt($order->id))}}"
                   style="font-size: 13px;">
                   <i class="las la-eye mr-1" style="color: #fa3e00;"></i>{{ translate('View') }}
                </a>
                
                <a class="btn btn-sm border rounded px-3"
                   href="{{ route('invoice.download', $order->id) }}"
                   style="font-size: 13px;">
                   <i class="las la-download mr-1" style="color: #fa3e00;"></i>{{ translate('Invoice') }}
                </a>
                
                @if ($order->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                    <a href="javascript:void(0)"  
                       class="btn btn-sm border rounded px-3 confirm-delete" 
                       data-href="{{route('purchase_history.destroy', $order->id)}}"
                       style="font-size: 13px;">
                       <i class="las la-trash mr-1" style="color: #fa3e00;"></i> {{ translate('Cancel') }}
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Store Name -->
        <div class="mt-2">
            <span class="font-weight-bold fs-13" style="color: #fa3e00;">
                <i class="las la-store-alt mr-1"></i>
                {{ get_shop_by_user_id($order->seller_id)->name??"Inhouse Products" }}
            </span>
        </div>
        
        

      <!-- Order Items -->
      <div class="p-3">
          @foreach($order->orderDetails as $orderDetail)
              <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #fa3e0011 !important;">
                  <div class="row align-items-center">
                      <!-- Product Image -->
                      <div class="col-3">
                          <div class="position-relative">
                              <img
                                  src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"
                                  class="img-fluid rounded"
                                  style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #fa3e0033;"
                              >
                              <span class="badge badge-pill position-absolute" 
                                    style="top: -5px; right: -5px; background-color: #fa3e00; color: white; font-size: 9px; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                  {{ $orderDetail->quantity }}
                              </span>
                          </div>
                      </div>
                      
                      <!-- Product Details -->
                      <div class="col-9 pl-0">
                          <a href="{{ route('product', $orderDetail->product->slug) }}"
                             class="font-weight-semibold fs-14 text-dark hover-orange d-block mb-1"
                             style="--hover-color: #fa3e00; line-height: 1.3;"
                             title="{{ $orderDetail->product->getTranslation('name') }}">
                              {{ \Illuminate\Support\Str::limit($orderDetail->product->getTranslation('name'), 45, '...') }}
                          </a>
                          
                          @if($orderDetail->variation)
                          <div class="text-muted small mb-1">
                              {{ \Illuminate\Support\Str::limit($orderDetail->variation, 35, '...') }}
                          </div>
                          @endif
                          
                          <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="font-weight-bold fs-14" style="color: #fa3e00;">
                                {{ single_price($orderDetail->price) }}
                            </div>
                        
                            <div class="text-muted small d-flex align-items-center">
                                <i class="las la-layer-group mr-1" style="color: #fa3e00; font-size: 14px;"></i>
                                <span class="font-weight-bold fs-14">{{ $orderDetail->quantity }} {{ translate('pcs') }}</span>
                            </div>
                        </div>

                      </div>
                  </div>
              </div>
          @endforeach
      </div>

      <!-- Order Summary Section -->
      <div class="p-3" style="background-color: #f8f9fa; border-top: 1px solid #fa3e0033;">
          <div class="row">
              <!-- Status Section -->
              <div class="col-6">
                  <div class="mb-2">
                      <div class="text-muted small mb-1">{{ translate('Status') }}</div>
                      <span style="
                            display: inline-flex;
                            align-items: center;
                            padding: 4px 10px;
                            font-size: 12px;
                            font-weight: 500;
                            border-radius: 999px;
                            background-color: #fff9f7;
                            color: #fa3e00;
                            border: 1px solid #fa3e00;
                        ">
                            <i class="las la-truck" style="margin-right: 6px;"></i>
                            {{ \Illuminate\Support\Str::limit(translate(ucfirst(str_replace('_', ' ', $order->delivery_status))), 12, '') }}
                        </span>

                  </div>
                  
                  <div>
                        <div style="
                            color: #6c757d;
                            font-size: 12px;
                            margin-bottom: 4px;
                        ">
                            {{ translate('Payment') }}
                        </div>
                    
                        @if ($order->payment_status == 'paid')
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                padding: 4px 10px;
                                font-size: 12px;
                                font-weight: 500;
                                border-radius: 999px;
                                background-color: #e7f7ef;
                                color: #28a745;
                                border: 1px solid #28a745;
                            ">
                                <i class="las la-check-circle" style="margin-right: 6px;"></i>
                                {{ translate('Paid') }}
                            </span>
                        @else
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                padding: 4px 10px;
                                font-size: 12px;
                                font-weight: 500;
                                border-radius: 999px;
                                background-color: #fff9f7;
                                color: #fa3e00;
                                border: 1px solid #fa3e00;
                            ">
                                <i class="las la-clock" style="margin-right: 6px;"></i>
                                {{ translate('Unpaid') }}
                            </span>
                        @endif
                    </div>

              </div>
              
              <!-- QR Code Section -->
              <div class="col-6">
                  <div class="text-center">
                      <div class="border rounded p-2 d-inline-block mb-2" 
                           style="border-color: #fa3e00 !important; background-color: white; max-width: 100px;">
                          <img
                              src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($order->code) }}&format=png&color=000000&bgcolor=ffffff"
                              alt="QR for Order {{ $order->code }}"
                              style="width: 80px; height: 80px;"
                              class="img-fluid"
                          >
                      </div>
                  </div>
              </div>
          </div>
          
          <!-- Order Total -->
          <div class="mt-3 pt-3 border-top text-center" style="border-color: #fa3e0033 !important;">
              <div class="text-muted small">{{ translate('Order Total') }}</div>
              <div class="font-weight-bold fs-16" style="color: #fa3e00;">
                  {{ single_price($order->grand_total) }}
              </div>
          </div>
          
        </div>
          
        <div class="p-3" style="background-color: #f8f9fa; border-top: 1px solid #fa3e0033;">
          <div class="d-flex d-md-none justify-content-between mt-3 gap-2">
        
            <a type="button" href="{{ route('re_order', encrypt($order->id)) }}"
               class="btn btn-sm flex-fill d-flex align-items-center justify-content-center p-2 rounded"
               style="background-color: #fa3e00; color: white; border: none; font-size: 11px; min-height: 50px;">
                <i class="las la-redo-alt me-1 fs-16"></i>
                <span style="font-size: 11px;">{{ translate('Reorder') }}</span>
            </a>
        
            <a class="btn btn-sm border flex-fill d-flex align-items-center justify-content-center p-2 rounded"
               href="{{route('purchase_history.details', encrypt($order->id))}}"
               style="font-size: 11px; min-height: 50px;">
                <i class="las la-eye me-1 fs-16" style="color: #fa3e00;"></i>
                <span style="font-size: 11px; color: #fa3e00;">{{ translate('View') }}</span>
            </a>
        
            <a class="btn btn-sm border flex-fill d-flex align-items-center justify-content-center p-2 rounded"
               href="{{ route('invoice.download', $order->id) }}"
               style="font-size: 11px; min-height: 50px;">
                <i class="las la-download me-1 fs-16" style="color: #fa3e00;"></i>
                <span style="font-size: 11px; color: #fa3e00;">{{ translate('Invoice') }}</span>
            </a>
        
            @if ($order->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                <a href="javascript:void(0)"
                   class="btn btn-sm border flex-fill d-flex align-items-center justify-content-center p-2 rounded confirm-delete"
                   data-href="{{route('purchase_history.destroy', $order->id)}}"
                   style="font-size: 11px; min-height: 50px;">
                    <i class="las la-trash me-1 fs-16" style="color: #fa3e00;"></i>
                    <span style="font-size: 11px; color: #fa3e00;">{{ translate('Cancel') }}</span>
                </a>
            @endif
        
          </div>
        </div>
        
    </div>

  </div>
@endforeach

<!-- Desktop View (hidden on mobile) -->
@foreach($orders as $order)
<div class="mb-4 d-none d-md-block">
    <div class="border rounded" style="border-color: #fa3e0033 !important; overflow: hidden;">
        <div class="p-3" style="background-color: #fff9f7; border-bottom: 1px solid #fa3e0033;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <p class="mb-2 fs-16 fw-700 mb-0">
                        <a class="text-dark hover-orange" href="{{route('purchase_history.details', encrypt($order->id))}}" 
                           style="--hover-color: #fa3e00;">
                            <i class="las la-receipt mr-2" style="color: #fa3e00;"></i>
                            {{ translate('Order ID')}} - <span style="color: #fa3e00;">{{ $order->code }}</span>
                        </a>
                    </p>
                </div>

                <div class="text-right">
                    <span class="text-muted fs-12 d-inline-block mr-3">
                        <i class="las la-calendar-alt mr-1" style="color: #fa3e00;"></i>
                        {{ date('d-m-Y', $order->date) }}
                    </span>
                    
                    <span class="font-weight-bold fs-13" style="color: #fa3e00;">
                        <i class="las la-store-alt mr-1"></i>
                        {{ get_shop_by_user_id($order->seller_id)->name??"Inhouse Products" }}
                    </span>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a type="button" href="{{ route('re_order', encrypt($order->id)) }}" 
                   class="btn btn-sm rounded px-4 py-1 mr-2"
                   style="background-color: #fa3e00; color: white; border: none;">
                    <i class="las la-redo-alt mr-1"></i>
                    {{ translate('Reorder') }}
                </a>

                <div class="dropdown">
                    <button type="button"
                        class="btn btn-sm dropdown-toggle text-white px-4 py-1 rounded"
                        data-toggle="dropdown"
                        style="background-color: #fa3e00; border: none;">
                        <i class="las la-cog mr-1"></i>
                        {{ translate('Options') }}
                    </button>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item text-secondary dropdown-bg-hover" 
                           href="{{route('purchase_history.details', encrypt($order->id))}}"
                           style="--hover-bg: #fff9f7;">
                           <i class="las la-eye mr-2" style="color: #fa3e00;"></i>{{ translate('View') }}
                        </a>
                        <a class="dropdown-item text-secondary dropdown-bg-hover" 
                           href="{{ route('invoice.download', $order->id) }}"
                           style="--hover-bg: #fff9f7;">
                           <i class="las la-download mr-2" style="color: #fa3e00;"></i>{{ translate('Invoice') }}
                        </a>
                        @if ($order->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                        <a href="javascript:void(0)"  
                           class="dropdown-item text-secondary dropdown-bg-hover confirm-delete" 
                           data-href="{{route('purchase_history.destroy', $order->id)}}"
                           style="--hover-bg: #fff9f7;">
                           <i class="las la-trash mr-2" style="color: #fa3e00;"></i> {{ translate('Cancel') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Order Items -->
        <div class="p-3">
            @foreach($order->orderDetails as $orderDetail)
                @if (!$loop->first)
                    <hr class="hr-split" style="border-color: #fa3e0011;">
                @endif
        
                <div class="row align-items-center">
                    <div class="col-md-7 d-flex align-items-center">
                        <div class="position-relative">
                            <img
                                src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"
                                class="img-fluid mr-3 product-history-img rounded"
                                style="border: 1px solid #fa3e0033;"
                            >
                            <span class="badge badge-pill position-absolute" 
                                  style="top: -8px; right: 5px; background-color: #fa3e00; color: white; font-size: 10px;">
                                {{ $orderDetail->quantity }}
                            </span>
                        </div>
        
                        <div class="w-300px text-wrap">
                            <a href="{{ route('product', $orderDetail->product->slug) }}"
                               class="font-weight-semibold fs-14 text-dark hover-orange text-truncate-2 d-block"
                               style="--hover-color: #fa3e00;"
                               title="{{ $orderDetail->product->getTranslation('name') }}">
                                {{ $orderDetail->product->getTranslation('name') }}
                            </a>
                            <div class="text-muted small">
                                {{ $orderDetail->variation }}
                            </div>
                            <div class="font-weight-bold mt-1" style="color: #fa3e00;">
                                {{ single_price($orderDetail->price) }}
                            </div>
                        </div>
                    </div>
        
                    <div class="col-md-3 text-md-left text-right">
                        <div class="font-weight-bold" style="color: #fa3e00;">
                            {{ single_price($orderDetail->price) }}
                        </div>
                        <div class="text-muted small">
                            <i class="las la-layer-group mr-1" style="color: #fa3e00;"></i>
                            {{ translate('Qty') }} {{ $orderDetail->quantity }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Desktop Footer -->
        <div class="p-3" style="background-color: #f8f9fa; border-top: 1px solid #fa3e0033;">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div style="
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    ">
                        <span style="
                            display: inline-flex;
                            align-items: center;
                            padding: 4px 12px;
                            font-size: 12px;
                            font-weight: 500;
                            border-radius: 999px;
                            background-color: #fff9f7;
                            color: #fa3e00;
                            border: 1px solid #fa3e00;
                        ">
                            <i class="las la-truck" style="margin-right: 6px;"></i>
                            {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                        </span>
                    
                        @if ($order->payment_status == 'paid')
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                padding: 4px 12px;
                                font-size: 12px;
                                font-weight: 500;
                                border-radius: 999px;
                                background-color: #e7f7ef;
                                color: #28a745;
                                border: 1px solid #28a745;
                            ">
                                <i class="las la-check-circle" style="margin-right: 6px;"></i>
                                {{ translate('Paid') }}
                            </span>
                        @else
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                padding: 4px 12px;
                                font-size: 12px;
                                font-weight: 500;
                                border-radius: 999px;
                                background-color: #fff9f7;
                                color: #fa3e00;
                                border: 1px solid #fa3e00;
                            ">
                                <i class="las la-clock" style="margin-right: 6px;"></i>
                                {{ translate('Unpaid') }}
                            </span>
                        @endif
                    </div>

                </div>
                
                <div class="col-md-4 text-center">
                    <div class="border rounded p-2 d-inline-block" 
                         style="border-color: #fa3e00 !important; background-color: white;">
                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($order->code) }}&format=png&color=000000&bgcolor=ffffff"
                            alt="QR for Order {{ $order->code }}"
                            style="width: 80px; height: 80px;"
                            class="img-fluid mb-1"
                        >
                        <div class="text-muted fs-11">
                            {{ translate('Order Verification') }}
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 text-right">
                    <div class="text-muted small">{{ translate('Order Total') }}</div>
                    <div class="font-weight-bold fs-16" style="color: #fa3e00;">
                        {{ single_price($order->grand_total) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="aiz-pagination mt-4" id="pagination">
    {{ $orders->links() }}
</div>

<style>
    .hover-orange:hover {
        color: #fa3e00 !important;
    }
    
    .dropdown-bg-hover:hover {
        background-color: var(--hover-bg, #f8f9fa) !important;
    }
    
    .product-history-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }
    
    @media (max-width: 768px) {
        .w-300px {
            max-width: 100% !important;
        }
        
        
        
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .badge.rounded-pill {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }
        
        /* Mobile button styling */
        .btn-sm.flex-fill {
            flex: 1;
        }
    }
    
    @media (min-width: 769px) {
        .d-md-block {
            display: block !important;
        }
        
        .d-md-flex {
            display: flex !important;
        }
        
        .d-md-none {
            display: none !important;
        }
        
        /* PC button styling */
        .btn-sm.border {
            border: 1px solid #ddd !important;
            background-color: white;
        }
        
        .btn-sm.border:hover {
            background-color: #fff9f7;
        }
    }
    
    .border-dashed {
        border-style: dashed !important;
    }
    
    .hr-split {
        margin: 15px 0;
    }
    
    /* Ensure no horizontal overflow */
    .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    .col-3, .col-6, .col-9 {
        padding-left: 8px;
        padding-right: 8px;
    }
</style>