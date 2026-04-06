<div class="pickup-mobile-nav aiz-mobile-bottom-nav d-xl-none fixed-bottom border-top mx-auto" style="background-color: rgb(255 255 255 / 95%)!important;">
    <div class="row align-items-center gutters-5">
        <div class="col">
            <a href="{{ route('pickup-point.dashboard') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['pickup-point.dashboard'],'text-primary') }}">
                <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['pickup-point.dashboard'],'text-primary') }}">{{ translate('Dashboard') }}</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('pickup-point.upcoming-orders') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['pickup-point.upcoming-orders'],'text-primary') }}">
                <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['pickup-point.upcoming-orders'],'text-primary') }}">{{ translate('Upcoming') }}</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('pickup-point.pickup-orders') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['pickup-point.pickup-orders'],'text-primary') }}">
                <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['pickup-point.pickup-orders'],'text-primary') }}">{{ translate('Pickup') }}</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('pickup-point.on-the-way-orders') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['pickup-point.on-the-way-orders'],'text-primary') }}">
                <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['pickup-point.on-the-way-orders'],'text-primary') }}">{{ translate('On The Way') }}</span>
            </a>
        </div>
    </div>
</div>
