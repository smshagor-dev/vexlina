<header class="@if(get_setting('header_stikcy') == 'on') sticky-top @endif z-1020 bg-white border-bottom">
    <div class="position-relative logo-bar-area z-1025">
        <div class="container">
            <div class="d-flex align-items-center">
                <div class="col-auto pl-0 pr-3 d-flex align-items-center">
                    <a class="d-block py-20px mr-3 ml-0" href="{{ route('pickup-point.dashboard') }}">
                        @php $header_logo = get_setting('header_logo'); @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-40px" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-40px" height="40">
                        @endif
                    </a>
                    <div class="d-none d-lg-block">
                        <div class="fs-15 fw-700 text-dark">{{ translate('Pickup Point Dashboard') }}</div>
                        <div class="fs-12 text-secondary">{{ optional(optional(Auth::user()->staff)->pick_up_point)->getTranslation('name') }}</div>
                    </div>
                </div>

                <div class="ml-auto mr-0">
                    <span class="d-none d-xl-flex align-items-center py-20px">
                        <span class="size-40px rounded-circle overflow-hidden border border-transparent">
                            @if (Auth::user()->avatar_original != null)
                                <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}" class="img-fit h-100" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                            @else
                                <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="img-fit h-100">
                            @endif
                        </span>
                        <h4 class="h5 fs-14 fw-700 text-dark ml-2 mb-0">{{ Auth::user()->name }}</h4>
                    </span>
                </div>
            </div>
        </div>
    </div>
</header>
