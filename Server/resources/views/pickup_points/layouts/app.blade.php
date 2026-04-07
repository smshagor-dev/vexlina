<!DOCTYPE html>
@if(get_system_language()->rtl == 1)
<html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@else
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endif
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ getBaseURL() }}">
    <meta name="file-base-url" content="{{ getFileBaseURL() }}">
    <title>@yield('meta_title', get_setting('website_name').' | '.translate('Pickup Point Dashboard'))</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    @if(get_system_language()->rtl == 1)
    <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap-rtl.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/custom-style.css') }}">
    <style>
        :root{
            --primary: {{ get_setting('base_color', '#d43533') }};
            --hov-primary: {{ get_setting('base_hov_color', '#9d1b1a') }};
            --soft-primary: {{ hex2rgba(get_setting('base_color','#d43533'),.15) }};
            --soft-light: #dfdfe6;
            --dark: #292933;
        }
        body{font-family:'Public Sans',sans-serif;}
        .pickup-mobile-nav .text-primary{color:var(--primary)!important;}
    </style>
</head>
<body>
    <div class="aiz-main-wrapper d-flex flex-column bg-white">
        @include('pickup_points.inc.nav')
        <section class="py-5">
            <div class="container">
                <div class="d-flex align-items-start">
                    @include('pickup_points.inc.sidenav')
                    <div class="aiz-user-panel">
                        @yield('panel_content')
                    </div>
                </div>
            </div>
        </section>
        @include('pickup_points.inc.footer')
    </div>

    @yield('modal')

    <script src="{{ static_asset('assets/js/vendors.js') }}"></script>
    <script src="{{ static_asset('assets/js/aiz-core.js') }}"></script>
    <script>
        @foreach (session('flash_notification', collect())->toArray() as $message)
            AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
        @endforeach
    </script>
    @yield('script')
</body>
</html>
