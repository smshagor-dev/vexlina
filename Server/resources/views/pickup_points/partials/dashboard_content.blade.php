@php
    $earningCardClasses = ['pickup-earning-card--today', 'pickup-earning-card--week', 'pickup-earning-card--month', 'pickup-earning-card--total'];
@endphp

<div class="card pickup-station-card mt-4">
    <div class="card-body p-4">
        <div class="pickup-station-card__title">{{ translate('Pickup Point Information') }}</div>
        <div class="pickup-station-card__grid">
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Station Code') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->internal_code ?: '-' }}</p>
            </div>
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Point Name') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->getTranslation('name') }}</p>
            </div>
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Location') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->getTranslation('address') }}</p>
            </div>
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Contact') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->phone }}</p>
            </div>
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Working Hours') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->workingHoursLabel() }}</p>
            </div>
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Hold Window') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->holdDays() }} {{ translate('days') }}</p>
            </div>
            <div class="pickup-station-card__meta">
                <div class="pickup-station-card__label">{{ translate('Instructions') }}</div>
                <p class="pickup-station-card__line">{{ optional($pickup_point)->instructions ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>


<div class="row gutters-16">
    <div class="col-12">
        <div class="row gutters-16">
            <div class="col-md-6 col-xl-4 py-2">
                <a href="{{ route('pickup-point.upcoming-orders') }}" class="pickup-stat-card" style="background:linear-gradient(135deg,#111827 0%,#374151 100%);">
                    <p>{{ translate('Upcoming Orders') }}</p>
                    <h4>{{ sprintf('%02d', $upcoming_count) }}</h4>
                </a>
            </div>
            <div class="col-md-6 col-xl-4 py-2">
                <a href="{{ route('pickup-point.pickup-orders') }}" class="pickup-stat-card" style="background:linear-gradient(135deg,#d97706 0%,#f59e0b 100%);">
                    <p>{{ translate('Picked Up Orders') }}</p>
                    <h4>{{ sprintf('%02d', $pickup_count) }}</h4>
                </a>
            </div>
            <div class="col-md-6 col-xl-4 py-2">
                <a href="{{ route('pickup-point.on-the-way-orders') }}" class="pickup-stat-card" style="background:linear-gradient(135deg,#1d4ed8 0%,#3b82f6 100%);">
                    <p>{{ translate('On The Way Orders') }}</p>
                    <h4>{{ sprintf('%02d', $on_the_way_count) }}</h4>
                </a>
            </div>
            <div class="col-md-6 col-xl-4 py-2">
                <a href="{{ route('pickup-point.reached-orders') }}" class="pickup-stat-card" style="background:linear-gradient(135deg,#0f766e 0%,#14b8a6 100%);">
                    <p>{{ translate('Reached Orders') }}</p>
                    <h4>{{ sprintf('%02d', $reached_count) }}</h4>
                </a>
            </div>
            <div class="col-md-6 col-xl-4 py-2">
                <a href="{{ route('pickup-point.completed-orders') }}" class="pickup-stat-card" style="background:linear-gradient(135deg,#15803d 0%,#22c55e 100%);">
                    <p>{{ translate('Completed Orders') }}</p>
                    <h4>{{ sprintf('%02d', $completed_count) }}</h4>
                </a>
            </div>
            <div class="col-md-6 col-xl-4 py-2">
                <a href="{{ route('pickup-point.return-orders') }}" class="pickup-stat-card" style="background:linear-gradient(135deg,#b91c1c 0%,#ef4444 100%);">
                    <p>{{ translate('Return Orders') }}</p>
                    <h4>{{ sprintf('%02d', $return_count) }}</h4>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row gutters-16 mt-2">
    @foreach ($earning_summaries as $index => $summary)
        <div class="col-md-6 col-xl-6 py-2">
            <div class="pickup-earning-card {{ $earningCardClasses[$index] ?? 'pickup-earning-card--total' }}">
                <div class="pickup-earning-card__label">{{ $summary['label'] }}</div>
                <div class="pickup-earning-card__headline">{{ single_price($summary['delivery_earning'] + $summary['return_earning']) }}</div>
                <div class="pickup-earning-card__row">
                    <span>{{ translate('Delivery') }}</span>
                    <strong>{{ single_price($summary['delivery_earning']) }}</strong>
                </div>
                <div class="pickup-earning-card__row">
                    <span>{{ translate('Return') }}</span>
                    <strong>{{ single_price($summary['return_earning']) }}</strong>
                </div>
                <div class="pickup-earning-card__total">
                    <span>{{ translate('Total') }}</span>
                    <span>{{ single_price($summary['delivery_earning'] + $summary['return_earning']) }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>


