<!-- Top Bar Banner -->
@php
    $top_banner_background_color = get_setting('top_banner_background_color', get_setting('base_color'));
    $top_banner_text_color = get_setting('top_banner_text_color');
    $top_banner_image = get_setting('top_banner_image');
    $top_banner_image_for_tabs = get_setting('top_banner_image_for_tabs');
    $top_banner_image_for_mobile = get_setting('top_banner_image_for_mobile');
    $topBanners = \App\Models\TopBanner::where('status', 1)->orderBy('id','desc')->get();
    $menu_style = get_setting('mobile_menu_style', 'modern'); // Add this setting in your admin panel
@endphp 
    @if (count($topBanners) > 0 || $top_banner_image != null)
    <div class="position-relative top-banner removable-session z-1035 d-none" 
         data-key="top-banner" data-value="removed" style="background-color: {{ $top_banner_background_color }}">
        <div class="d-block text-reset h-40px h-lg-60px position-relative overflow-hidden">

            @if($top_banner_image != null)
            <!-- For Large device -->
            <img src="{{ uploaded_asset($top_banner_image)  }}"
                class="d-none d-xl-block img-fit h-100 w-100" alt="{{ translate('top_banner') }}">

            <!-- For Medium device -->
            <img src="{{ uploaded_asset($top_banner_image_for_tabs ?? $top_banner_image)  }}"
                class="d-none d-md-block d-xl-none img-fit h-100 w-100" alt="{{ translate('top_banner') }}">

            <!-- For Small device -->
            <img src="{{ uploaded_asset($top_banner_image_for_mobile ?? $top_banner_image) }}"
                class="d-md-none img-fit h-100 w-100" alt="{{ translate('top_banner') }}">
            @endif

            <!-- Scroll Text -->
            <div class="top-banner-scroll-text position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                <div class="container">
                    <div class="overflow-hidden">
                        <div class="top-banner-scroll-inner">
                            @foreach ($topBanners as $banner)
                                <a href="{{ $banner->link ?? '#' }}" style="color: {{$top_banner_text_color}};"
                                    class="{{ $banner->link ? 'has-link' : 'no-link' }}">
                                    {{ $banner->getTranslation('text') }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="btn text-white h-100 absolute-top-right set-session" 
            data-key="top-banner" data-value="removed"
            data-toggle="remove-parent" data-parent=".top-banner">
            <i style="color: {{$top_banner_text_color}};" class="la la-close la-2x"></i>
        </button>
    </div>
    @endif
	@include('header.' .get_element_type_by_id(get_setting('header_element')))

<!-- Top Menu Sidebar -->
@if($menu_style == 'classic')
<!-- =============== CLASSIC DESIGN =============== -->
<div class="aiz-top-menu-sidebar classic-mobile-menu collapse-sidebar-wrap sidebar-xl sidebar-left d-lg-none z-1035">
    <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle" data-target=".aiz-top-menu-sidebar"
        data-same=".hide-top-menu-bar"></div>
    <div class="collapse-sidebar classic-menu-content c-scrollbar-light text-left">
        <!-- Classic Menu Header -->
        <div class="classic-menu-header bg-gradient-primary p-4 position-relative">
            <button type="button" class="btn btn-sm btn-light hide-top-menu-bar" data-toggle="class-toggle"
                data-target=".aiz-top-menu-sidebar">
                <i class="las la-times la-1x"></i>
            </button>
            
            @auth
                <div class="d-flex align-items-center">
                    <div class="size-60px rounded-circle overflow-hidden border border-3 border-white shadow-sm">
                        @if ($user->avatar_original != null)
                            <img src="{{ $user_avatar }}" class="img-fit h-100" alt="{{ translate('avatar') }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="img-fit h-100"
                                alt="{{ translate('avatar') }}">
                        @endif
                    </div>
                    <div class="ml-3">
                        <h4 class="h5 fs-16 fw-700 text-white mb-1">{{ $user->name }}</h4>
                        <p class="text-white opacity-80 mb-0 fs-12">
                            <i class="las la-envelope mr-1"></i> {{ $user->email }}
                        </p>
                    </div>
                </div>
            @else
                <div class="text-center py-3">
                    <div class="size-70px rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3 shadow">
                        <i class="las la-user-circle la-3x text-primary"></i>
                    </div>
                    <h5 class="text-white mb-2">{{ translate('Welcome Guest') }}</h5>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('user.login') }}" class="btn btn-light btn-sm mr-2">
                            <i class="las la-sign-in-alt mr-1"></i> {{ translate('Login') }}
                        </a>
                        <a href="{{ route('user.registration') }}" class="btn btn-outline-light btn-sm">
                            <i class="las la-user-plus mr-1"></i> {{ translate('Register') }}
                        </a>
                    </div>
                </div>
            @endauth
        </div>

        <!-- Classic Menu Body -->
        <div class="classic-menu-body p-0">
            @if (get_setting('header_menu_labels') != null)
                <div class="px-4 pt-4">
                    <h6 class="text-uppercase fs-12 text-muted mb-3 fw-600">
                        <i class="las la-compass mr-2"></i> {{ translate('Main Menu') }}
                    </h6>
                </div>
                <ul class="list-unstyled mb-4">
                    @foreach (json_decode(get_setting('header_menu_labels'), true) as $key => $value)
                        <li class="border-bottom border-soft-light">
                            <a href="{{ json_decode(get_setting('header_menu_links'), true)[$key] }}"
                                class="d-flex align-items-center px-4 py-3 text-dark fs-14 fw-500
                                        @if (url()->current() == json_decode(get_setting('header_menu_links'), true)[$key]) active bg-soft-primary @endif">
                                <i class="las la-angle-right mr-3 text-primary"></i>
                                {{ translate($value) }}
                                @if (url()->current() == json_decode(get_setting('header_menu_links'), true)[$key])
                                    <span class="ml-auto"><i class="las la-check text-success"></i></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @auth
                <div class="px-4">
                    <h6 class="text-uppercase fs-12 text-muted mb-3 fw-600">
                        <i class="las la-user-circle mr-2"></i> {{ translate('My Account') }}
                    </h6>
                </div>
                <ul class="list-unstyled mb-4">
                    @if (isAdmin())
                        <li class="border-bottom border-soft-light">
                            <a href="{{ route('admin.dashboard') }}"
                                class="d-flex align-items-center px-4 py-3 text-dark fs-14 fw-500">
                                <i class="las la-tachometer-alt mr-3 text-info"></i>
                                {{ translate('Admin Dashboard') }}
                            </a>
                        </li>
                    @else
                        <li class="border-bottom border-soft-light">
                            <a href="{{ route('dashboard') }}" 
                                class="d-flex align-items-center px-4 py-3 text-dark fs-14 fw-500
                                        {{ areActiveRoutes(['dashboard'], ' active bg-soft-primary') }}">
                                <i class="las la-tachometer-alt mr-3 text-info"></i>
                                {{ translate('Dashboard') }}
                            </a>
                        </li>
                    @endif

                    @if (isCustomer())
                        <li class="border-bottom border-soft-light">
                            <a href="{{ route('customer.all-notifications') }}"
                                class="d-flex align-items-center px-4 py-3 text-dark fs-14 fw-500
                                        {{ areActiveRoutes(['customer.all-notifications'], ' active bg-soft-primary') }}">
                                <i class="las la-bell mr-3 text-warning"></i>
                                {{ translate('Notifications') }}
                                <span class="badge badge-primary badge-pill ml-auto">0</span>
                            </a>
                        </li>
                        <li class="border-bottom border-soft-light">
                            <a href="{{ route('wishlists.index') }}"
                                class="d-flex align-items-center px-4 py-3 text-dark fs-14 fw-500
                                        {{ areActiveRoutes(['wishlists.index'], ' active bg-soft-primary') }}">
                                <i class="las la-heart mr-3 text-danger"></i>
                                {{ translate('Wishlist') }}
                            </a>
                        </li>
                        <li class="border-bottom border-soft-light">
                            <a href="{{ route('compare') }}"
                                class="d-flex align-items-center px-4 py-3 text-dark fs-14 fw-500
                                        {{ areActiveRoutes(['compare'], ' active bg-soft-primary') }}">
                                <i class="las la-exchange-alt mr-3 text-success"></i>
                                {{ translate('Compare') }}
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="px-4">
                    <h6 class="text-uppercase fs-12 text-muted mb-3 fw-600">
                        <i class="las la-cog mr-2"></i> {{ translate('Settings') }}
                    </h6>
                </div>
                <ul class="list-unstyled mb-4">
                    <li class="border-bottom border-soft-light">
                        <a href="{{ route('logout') }}"
                            class="d-flex align-items-center px-4 py-3 text-danger fs-14 fw-500">
                            <i class="las la-sign-out-alt mr-3"></i>
                            {{ translate('Logout') }}
                        </a>
                    </li>
                </ul>
            @endauth
        </div>

        <!-- Classic Menu Footer -->
        <div class="classic-menu-footer bg-soft-light border-top p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fs-12 text-muted">
                    <i class="las la-globe mr-1"></i> {{ translate('Version') }} {{ env('APP_VERSION', '1.0') }}
                </div>
                <div>
                    <a href="#" class="text-muted mr-3"><i class="lab la-facebook-f la-lg"></i></a>
                    <a href="#" class="text-muted mr-3"><i class="lab la-twitter la-lg"></i></a>
                    <a href="#" class="text-muted"><i class="lab la-instagram la-lg"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

@else
<!-- =============== MODERN DESIGN =============== -->
<div class="aiz-top-menu-sidebar modern-mobile-menu collapse-sidebar-wrap sidebar-xl sidebar-left d-lg-none z-1035">
    <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle" data-target=".aiz-top-menu-sidebar"
        data-same=".hide-top-menu-bar"></div>
    <div class="collapse-sidebar modern-menu-content c-scrollbar-light text-left">
        <!-- Modern Menu Header -->
        <div class="modern-menu-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                @auth
                    <div class="size-45px rounded-circle overflow-hidden border border-2 border-primary">
                        @if ($user->avatar_original != null)
                            <img src="{{ $user_avatar }}" class="img-fit h-100" alt="{{ translate('avatar') }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="img-fit h-100"
                                alt="{{ translate('avatar') }}">
                        @endif
                    </div>
                    <div class="ml-3">
                        <h5 class="fs-14 fw-700 text-dark mb-0">{{ $user->name }}</h5>
                        <p class="fs-11 text-muted mb-0">{{ translate('Welcome back') }}</p>
                    </div>
                @else
                    <div class="size-45px rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                        <i class="las la-user la-lg text-primary"></i>
                    </div>
                    <div class="ml-3">
                        <h5 class="fs-14 fw-700 text-dark mb-0">{{ translate('Guest User') }}</h5>
                        <p class="fs-11 text-muted mb-0">{{ translate('Login to continue') }}</p>
                    </div>
                @endauth
            </div>
            <button type="button" class="btn btn-circle btn-soft-danger hide-top-menu-bar" data-toggle="class-toggle"
                data-target=".aiz-top-menu-sidebar">
                <i class="las la-times la-1x"></i>
            </button>
        </div>

        <!-- Modern Menu Body -->
        <div class="modern-menu-body">
            @if (!Auth::check())
                <div class="px-4 py-3 bg-gradient-primary">
                    <p class="text-white fs-13 mb-2">{{ translate('Join our community') }}</p>
                    <div class="d-flex">
                        <a href="{{ route('user.login') }}" class="btn btn-light btn-sm flex-grow-1 mr-2">
                            <i class="las la-sign-in-alt mr-1"></i> {{ translate('Login') }}
                        </a>
                        <a href="{{ route('user.registration') }}" class="btn btn-outline-light btn-sm flex-grow-1">
                            <i class="las la-user-plus mr-1"></i> {{ translate('Register') }}
                        </a>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            @auth
                @if (isCustomer())
                <div class="px-3 pt-4">
                    <h6 class="fs-12 text-uppercase text-muted fw-600 mb-3">{{ translate('Quick Actions') }}</h6>
                    <div class="row no-gutters">
                        <div class="col-4 mb-3">
                            <a href="{{ route('dashboard') }}" 
                               class="d-flex flex-column align-items-center text-center p-2 rounded-lg hover-bg-soft-primary">
                                <div class="size-45px rounded-circle bg-soft-info d-flex align-items-center justify-content-center mb-2">
                                    <i class="las la-tachometer-alt la-lg text-info"></i>
                                </div>
                                <span class="fs-11 fw-500 text-dark">{{ translate('Dashboard') }}</span>
                            </a>
                        </div>
                        <div class="col-4 mb-3">
                            <a href="{{ route('customer.all-notifications') }}"
                               class="d-flex flex-column align-items-center text-center p-2 rounded-lg hover-bg-soft-primary">
                                <div class="size-45px rounded-circle bg-soft-warning d-flex align-items-center justify-content-center mb-2">
                                    <i class="las la-bell la-lg text-warning"></i>
                                </div>
                                <span class="fs-11 fw-500 text-dark">{{ translate('Notifications') }}</span>
                            </a>
                        </div>
                        <div class="col-4 mb-3">
                            <a href="{{ route('wishlists.index') }}"
                               class="d-flex flex-column align-items-center text-center p-2 rounded-lg hover-bg-soft-primary">
                                <div class="size-45px rounded-circle bg-soft-danger d-flex align-items-center justify-content-center mb-2">
                                    <i class="las la-heart la-lg text-danger"></i>
                                </div>
                                <span class="fs-11 fw-500 text-dark">{{ translate('Wishlist') }}</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('compare') }}"
                               class="d-flex flex-column align-items-center text-center p-2 rounded-lg hover-bg-soft-primary">
                                <div class="size-45px rounded-circle bg-soft-success d-flex align-items-center justify-content-center mb-2">
                                    <i class="las la-exchange-alt la-lg text-success"></i>
                                </div>
                                <span class="fs-11 fw-500 text-dark">{{ translate('Compare') }}</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('profile') }}"
                               class="d-flex flex-column align-items-center text-center p-2 rounded-lg hover-bg-soft-primary">
                                <div class="size-45px rounded-circle bg-soft-primary d-flex align-items-center justify-content-center mb-2">
                                    <i class="las la-user la-lg text-primary"></i>
                                </div>
                                <span class="fs-11 fw-500 text-dark">{{ translate('Profile') }}</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('logout') }}"
                               class="d-flex flex-column align-items-center text-center p-2 rounded-lg hover-bg-soft-primary">
                                <div class="size-45px rounded-circle bg-soft-danger d-flex align-items-center justify-content-center mb-2">
                                    <i class="las la-sign-out-alt la-lg text-dark"></i>
                                </div>
                                <span class="fs-11 fw-500 text-dark">{{ translate('Logout') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            @endauth

            <!-- Main Menu -->
            @if (get_setting('header_menu_labels') != null)
                <div class="px-3 pt-3">
                    <h6 class="fs-12 text-uppercase text-muted fw-600 mb-3">{{ translate('Main Menu') }}</h6>
                    <div class="bg-white rounded-lg border">
                        @foreach (json_decode(get_setting('header_menu_labels'), true) as $key => $value)
                            <a href="{{ json_decode(get_setting('header_menu_links'), true)[$key] }}"
                                class="d-flex align-items-center px-3 py-2 border-bottom border-soft-light
                                        @if (url()->current() == json_decode(get_setting('header_menu_links'), true)[$key]) active bg-primary text-white @endif">
                                <i class="las la-arrow-right mr-2 fs-14
                                    @if (url()->current() == json_decode(get_setting('header_menu_links'), true)[$key]) text-white @else text-primary @endif"></i>
                                <span class="fs-13 fw-500">{{ translate($value) }}</span>
                                @if (url()->current() == json_decode(get_setting('header_menu_links'), true)[$key])
                                    <span class="ml-auto"><i class="las la-check-circle"></i></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @auth
                @if (isAdmin())
                    <div class="px-3 pt-3">
                        <div class="bg-gradient-info rounded-lg p-3">
                            <div class="d-flex align-items-center">
                                <div class="size-45px rounded-circle bg-white d-flex align-items-center justify-content-center">
                                    <i class="las la-crown la-lg text-info"></i>
                                </div>
                                <div class="ml-3">
                                    <h6 class="text-white fs-14 fw-600 mb-0">{{ translate('Admin Panel') }}</h6>
                                    <p class="text-white opacity-80 fs-11 mb-0">{{ translate('Access dashboard') }}</p>
                                </div>
                                <a href="{{ route('admin.dashboard') }}" class="ml-auto btn btn-sm btn-light">
                                    <i class="las la-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Support Section -->
            <div class="px-3 pt-4">
                <h6 class="fs-12 text-uppercase text-muted fw-600 mb-3">{{ translate('Need Help?') }}</h6>
                <div class="bg-soft-primary rounded-lg p-3">
                    <div class="d-flex align-items-center">
                        <div class="size-45px rounded-circle bg-primary d-flex align-items-center justify-content-center">
                            <i class="las la-headset la-lg text-white"></i>
                        </div>
                        <div class="ml-3">
                            <h6 class="fs-14 fw-600 text-dark mb-0">{{ translate('Customer Support') }}</h6>
                            <p class="fs-11 text-muted mb-0">{{ translate('24/7 Available') }}</p>
                        </div>
                        <a href="{{ route('contacts') }}" class="ml-auto btn btn-sm btn-primary">
                            <i class="las la-phone"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="px-3 pt-4">
            
            </div>
        </div>

        <!-- Modern Menu Footer -->
        <div class="modern-menu-footer bg-white border-top p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fs-12 text-muted">
                    &copy; {{ date('Y') }} {{ env('APP_NAME') }}
                </div>
                <div class="d-flex">
                    <a href="https://www.facebook.com/vexlinashoppingzone/" class="btn btn-circle btn-sm btn-soft-primary mr-2">
                        <i class="lab la-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/vexlinashoppingzone/" class="btn btn-circle btn-sm btn-soft-danger mr-2">
                        <i class="lab la-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/vexlinashoppingzone/" class="btn btn-circle btn-sm btn-soft-info mr-2">
                        <i class="lab la-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
        <div>
            
        </div>
    </div>
</div>
@endif

<!-- Add these CSS styles to your stylesheet -->
<style>
/* Classic Menu Styles */
.classic-mobile-menu .classic-menu-content {
    width: 300px;
    background: #fff;
    box-shadow: 0 0 50px rgba(0,0,0,.1);
}

.classic-menu-header {
    min-height: 180px;
}

.classic-menu-body {
    max-height: calc(100vh - 280px);
    overflow-y: auto;
}

.classic-menu-body ul li a.active {
    border-left: 4px solid var(--primary);
    padding-left: 16px !important;
}

.classic-menu-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}

/* Modern Menu Styles */
.modern-mobile-menu .modern-menu-content {
    width: 320px;
    background: #f8f9fa;
}

.modern-menu-body {
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}

.modern-menu-body .hover-bg-soft-primary:hover {
    background-color: rgba(var(--primary-rgb), 0.1);
}

.modern-menu-body a.active {
    border-radius: 8px;
}

/* Common Styles */
.btn-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.rounded-lg {
    border-radius: 12px !important;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, rgb(230, 46, 4), rgb(255, 153, 0));
}

.bg-gradient-info {
    background: linear-gradient(135deg, var(--info) 0%, #17a2b8 100%);
}

.size-40px { width: 40px; height: 40px; }
.size-45px { width: 45px; height: 45px; }
.size-60px { width: 60px; height: 60px; }
.size-70px { width: 70px; height: 70px; }

.hover-bg-soft-primary:hover {
    background-color: rgba(var(--primary-rgb), 0.1);
    transition: all 0.3s ease;
}

/* Smooth transitions */
.classic-mobile-menu .collapse-sidebar,
.modern-mobile-menu .collapse-sidebar {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar styling */
.c-scrollbar-light::-webkit-scrollbar {
    width: 4px;
}

.c-scrollbar-light::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.c-scrollbar-light::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.c-scrollbar-light::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<!-- Add this JavaScript for better interaction -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add ripple effect to menu items
    const menuItems = document.querySelectorAll('.modern-menu-body a, .classic-menu-body a');
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.7);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add animation for menu opening
    const menuButtons = document.querySelectorAll('[data-target=".aiz-top-menu-sidebar"]');
    menuButtons.forEach(button => {
        button.addEventListener('click', function() {
            const menu = document.querySelector('.aiz-top-menu-sidebar');
            if (menu.classList.contains('show')) {
                menu.style.transform = 'translateX(-100%)';
            } else {
                menu.style.transform = 'translateX(0)';
            }
        });
    });
});

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

<!-- Modal -->
<div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div id="order-details-modal-body">

            </div>
        </div>
    </div>
</div>

<!--modal for language and currency select-->

<!--modal for language and currency select-->

<div class="modal fade" id="langCurrencyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">

            <!-- Header -->
            <div class="modal-header text-white"
                style="background:#fa3e00; border:none;">
                <h5 class="modal-title w-100 text-center">
                    Choose Language & Currency
                </h5>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                @if (get_setting('show_language_switcher') == 'on')
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Language</label>
                        <select class="form-control" id="modal_language">
                            @foreach (get_all_active_language() as $language)
                                <option value="{{ $language->code }}"
                                    @selected($system_language->code == $language->code)>
                                    {{ $language->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif


                @if (get_setting('show_currency_switcher') == 'on')
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Currency</label>
                        <select class="form-control" id="modal_currency">
                            @foreach (get_all_active_currency() as $currency)
                                <option value="{{ $currency->code }}"
                                    @selected(get_system_currency()->code == $currency->code)>
                                    {{ $currency->name }} ({{ $currency->symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <small class="text-muted d-block text-center">
                    You can change these anytime from settings
                </small>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" id="saveLangCurrency"
                    class="btn w-100 text-white"
                    style="background:#fa3e00; border-radius:8px;">
                    Continue
                </button>
                <button type="button" id="skipLangCurrency"
                    class="btn w-100 btn-outline-secondary"
                    style="border-radius:8px;">
                    Skip for now
                </button>
            </div>

        </div>
    </div>
</div>





@section('script')
    <script type="text/javascript">
        function show_order_details(order_id) {
            $('#order-details-modal-body').html(null);

            if (!$('#modal-size').hasClass('modal-lg')) {
                $('#modal-size').addClass('modal-lg');
            }

            $.post('{{ route('orders.details') }}', {
                _token: AIZ.data.csrf,
                order_id: order_id
            }, function (data) {
                $('#order-details-modal-body').html(data);
                $('#order_details').modal();
                $('.c-preloader').hide();
                AIZ.plugins.bootstrapSelect('refresh');
            });
        }
    </script>
@endsection