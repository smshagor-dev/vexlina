<div class="aiz-user-sidenav-wrap position-relative z-1 rounded-0">
    <div class="aiz-user-sidenav overflow-auto c-scrollbar-light px-4 pb-4">
        <div class="d-xl-none">
            <button class="btn btn-sm p-2" data-toggle="class-toggle" data-backdrop="static" data-target=".aiz-mobile-side-nav" data-same=".mobile-side-nav-thumb">
                <i class="las la-times la-2x"></i>
            </button>
        </div>

        <div class="p-4 text-center mb-4 border-bottom position-relative">
            <span class="avatar avatar-md mb-3">
                @if (Auth::user()->avatar_original != null)
                    <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                @else
                    <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle">
                @endif
            </span>
            <h4 class="h5 fs-14 mb-1 fw-700 text-dark">{{ Auth::user()->name }}</h4>
            <div class="text-truncate opacity-60 fs-12">{{ optional(optional(Auth::user()->staff)->pick_up_point)->getTranslation('name') }}</div>
        </div>

        <div class="sidemnenu">
            <ul class="aiz-side-nav-list mb-3 pb-3 border-bottom" data-toggle="aiz-side-menu">
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.dashboard') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.dashboard']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Dashboard') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.upcoming-orders') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.upcoming-orders']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Upcoming Orders') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.pickup-orders') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.pickup-orders']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Picked Up Orders') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.on-the-way-orders') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.on-the-way-orders']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('On The Way Orders') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.reached-orders') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.reached-orders']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Reached Orders') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.completed-orders') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.completed-orders']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Completed Orders') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.return-orders') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.return-orders']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Return Orders') }}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('pickup-point.payouts') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pickup-point.payouts']) }}">
                        <span class="aiz-side-nav-text ml-3">{{ translate('Payouts') }}</span>
                    </a>
                </li>
            </ul>
            <a href="{{ route('logout') }}" class="btn btn-primary btn-block fs-14 fw-700">{{ translate('Sign Out') }}</a>
        </div>
    </div>
</div>
