<div class="row gutters-16">
    @php
    $products = $products ?? [];
    $product_variation = $product_variation ?? [];
    $pickup_point_list = $pickup_point_list ?? [];
    $carrier_list = $carrier_list ?? collect();
    $carts = $carts ?? collect();
    $shipping_info = $shipping_info ?? null;
    $owner_id = $owner_id ?? 0;
    $physical = false;
    $col_val = 'col-12';
    foreach ($products as $key => $cartItem){
    $product = get_single_product($cartItem);
    if ($product->digital == 0) {
    $physical = true;
    $col_val = 'col-md-6';
    }
    }
    @endphp
    <!-- Product List -->
    <div class="{{ $col_val }}">
        <ul class="list-group list-group-flush mb-3">
            @foreach ($products as $key => $cartItem)
            @php
            $product = get_single_product($cartItem);
            @endphp
            <li class="list-group-item pl-0 py-3 border-0">
                <div class="d-flex align-items-center">
                    <span class="mr-2 mr-md-3">
                        <img src="{{ get_image($product->thumbnail) }}"
                            class="img-fit size-60px"
                            alt="{{  $product->getTranslation('name')  }}"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </span>
                    <span class="fs-14 fw-400 text-dark">
                        <span class="text-truncate-2">{{ $product->getTranslation('name') }}</span>
                        @if ($product_variation[$key] != '')
                        <span class="fs-12 text-secondary">{{ translate('Variation') }}: {{ $product_variation[$key] }}</span>
                        @endif
                    </span>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    @if ($physical)
    <!-- Choose Delivery Type -->
    <div class="col-md-6 mb-2">
        <h6 class="fs-14 fw-700 mt-3">{{ translate('Choose Delivery Type') }}</h6>
        <div class="row gutters-16">
            <!-- Home Delivery -->
            @if (get_setting('shipping_type') != 'carrier_wise_shipping')
            <div class="col-6">
                <label class="aiz-megabox d-block bg-white mb-0">
                    <input
                        type="radio"
                        name="shipping_type_{{ $owner_id }}"
                        value="home_delivery"
                        onchange="show_pickup_point(this, {{ $owner_id }})"
                        data-target=".pickup_point_id_{{ $owner_id }}"
                        checked required>
                    <span class="d-flex aiz-megabox-elem rounded-0" style="padding: 0.75rem 1.2rem;">
                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                        <span class="flex-grow-1 pl-3 fw-600">{{ translate('Home Delivery') }}</span>
                    </span>
                </label>
            </div>
            <!-- Carrier -->
            @else
            <div class="col-6">
                <label class="aiz-megabox d-block bg-white mb-0">
                    <input
                        type="radio"
                        name="shipping_type_{{ $owner_id }}"
                        value="carrier"
                        class="shipping-type-radio"
                        data-owner="{{ $owner_id }}"
                        onchange="show_pickup_point(this, {{ $owner_id }})"
                        data-target=".pickup_point_id_{{ $owner_id }}"
                        checked required>
                    <span class="d-flex aiz-megabox-elem rounded-0" style="padding: 0.75rem 1.2rem;">
                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                        <span class="flex-grow-1 pl-3 fw-600">{{ translate('Carrier') }}</span>
                    </span>
                </label>
            </div>
            @endif
            <!-- Local Pickup -->
            @if (count($pickup_point_list) > 0)
            <div class="col-6">
                <label class="aiz-megabox d-block bg-white mb-0">
                    <input
                        type="radio"
                        name="shipping_type_{{ $owner_id }}"
                        value="pickup_point"
                        class="shipping-type-radio"
                        data-owner="{{ $owner_id }}"
                        onchange="show_pickup_point(this, {{ $owner_id }})"
                        data-target=".pickup_point_id_{{ $owner_id }}"
                        required>
                    <span class="d-flex aiz-megabox-elem rounded-0" style="padding: 0.75rem 1.2rem;">
                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                        <span class="flex-grow-1 pl-3 fw-600">{{ translate('Pickup Point') }}</span>
                    </span>
                </label>
            </div>
            @endif
        </div>

        <!-- Pickup Point List -->
        @if (count($pickup_point_list) > 0)
        <div class="mt-3 pickup_point_id_{{ $owner_id }} d-none">
            <div class="alert alert-soft-info fs-12 mb-2 pickup-point-location-note d-none">
                {{ translate('Showing nearest pickup points based on your current location.') }}
            </div>
            <select
                class="form-control aiz-selectpicker rounded-0 pickup-point-select"
                name="pickup_point_id_{{ $owner_id }}"
                data-live-search="true"
                onchange="updateDeliveryInfo('pickup_point', this.value, {{ $owner_id }})">
                <option value="">{{ translate('Select your nearest pickup point')}}</option>
                @foreach ($pickup_point_list as $pick_up_point)
                <option
                    value="{{ $pick_up_point->id }}"
                    data-latitude="{{ $pick_up_point->latitude }}"
                    data-longitude="{{ $pick_up_point->longitude }}"
                    data-content="<span class='d-block'>
                                                <span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span>
                                                <span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span>
                                                <span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pick_up_point->phone }}</span>
                                                <span class='d-block opacity-50 fs-12'><i class='las la-clock'></i> {{ $pick_up_point->workingHoursLabel() }}</span>
                                                <span class='d-block opacity-50 fs-12'><i class='las la-box'></i> {{ translate('Pickup Hold') }}: {{ $pick_up_point->holdDays() }} {{ translate('days') }}</span>
                                                @if($pick_up_point->instructions)
                                                <span class='d-block opacity-50 fs-12'><i class='las la-info-circle'></i> {{ $pick_up_point->instructions }}</span>
                                                @endif
                                            </span>">
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Carrier Wise Shipping -->
        @if (get_setting('shipping_type') == 'carrier_wise_shipping')

        <div class="row pt-3 carrier_id_{{ $owner_id }}">
            @if($carrier_list->isEmpty())

            <div class="col-md-12">
                <div class="alert alert-danger col-md-12 mb-2">
                    <strong>{{ translate('Shipping is not available to your selected address.') }}</strong><br>
                    {{ translate('Please choose a different address.') }}
                </div>
                <span class="shipping-unavailable-flag" style="display: none;"></span>
            </div>


            @else
            @foreach($carrier_list as $carrier_key => $carrier)
            <div class="col-md-12 mb-2">
                <label class="aiz-megabox d-block bg-white mb-0">
                    <input
                        type="radio"
                        name="carrier_id_{{ $owner_id }}"
                        value="{{ $carrier->id }}"
                        @if($carrier_key==0) checked @endif
                        onchange="updateDeliveryInfo('carrier', {{ $carrier->id }}, {{ $owner_id }})">
                    <span class="d-flex flex-wrap p-3 aiz-megabox-elem rounded-0">
                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                        <span class="flex-grow-1 pl-3 fw-600">
                            <img src="{{ uploaded_asset($carrier->logo)}}" alt="Image" class="w-50px img-fit">
                        </span>
                        <span class="flex-grow-1 pl-3 fw-700">{{ $carrier->name }}</span>
                        <span class="flex-grow-1 pl-3 fw-600">{{ translate('Transit in').' '.$carrier->transit_time }}</span>
                        <span class="flex-grow-1 pl-4 pl-sm-3 fw-600 mt-2 mt-sm-0 text-sm-right">{{ single_price(carrier_base_price($carts, $carrier->id, $owner_id, $shipping_info)) }}</span>
                    </span>
                </label>
            </div>
            @endforeach
            @endif
        </div>

        @endif
    </div>
    @endif
</div>

<script>
    (function () {
        if (window.__pickupPointGeoInit) {
            return;
        }
        window.__pickupPointGeoInit = true;

        function toNumber(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : null;
        }

        function distanceInKm(lat1, lon1, lat2, lon2) {
            const toRad = (deg) => (deg * Math.PI) / 180;
            const earthRadiusKm = 6371;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return earthRadiusKm * c;
        }

        function sortPickupPointSelects(userLatitude, userLongitude) {
            document.querySelectorAll('.pickup-point-select').forEach(function (select) {
                const placeholderOption = select.querySelector('option[value=""]');
                const pickupPointOptions = Array.from(select.querySelectorAll('option'))
                    .filter(function (option) {
                        return option.value !== '';
                    })
                    .map(function (option) {
                        const latitude = toNumber(option.dataset.latitude);
                        const longitude = toNumber(option.dataset.longitude);
                        const distance = latitude !== null && longitude !== null
                            ? distanceInKm(userLatitude, userLongitude, latitude, longitude)
                            : Number.POSITIVE_INFINITY;

                        option.dataset.distance = Number.isFinite(distance)
                            ? distance.toFixed(2)
                            : '';

                        return {
                            option: option,
                            distance: distance,
                        };
                    })
                    .sort(function (first, second) {
                        return first.distance - second.distance;
                    });

                pickupPointOptions.forEach(function (entry) {
                    select.appendChild(entry.option);
                });

                const note = select.parentElement.querySelector('.pickup-point-location-note');
                if (note && pickupPointOptions.length > 0 && Number.isFinite(pickupPointOptions[0].distance)) {
                    note.classList.remove('d-none');
                }

                if (window.jQuery && window.jQuery.fn.selectpicker) {
                    window.jQuery(select).selectpicker('refresh');
                }
            });
        }

        function resolveLocationAndSort() {
            if (!navigator.geolocation) {
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    sortPickupPointSelects(position.coords.latitude, position.coords.longitude);
                },
                function () {
                    // Keep the existing order when location access is denied/unavailable.
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000,
                }
            );
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', resolveLocationAndSort);
        } else {
            resolveLocationAndSort();
        }
    })();
</script>
