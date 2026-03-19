@extends('frontend.layouts.app')

@section('content')
    <!-- Header Section -->
    <section class="py-3 mb-2" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.85) 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-left mb-4 mb-lg-0">
                    <h1 class="fw-800 fs-32 fs-md-40 text-dark mb-3" style="
                        background: linear-gradient(45deg, #e62e04, #ff9900);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        letter-spacing: -0.5px;
                    ">
                        {{ translate('Explore Our Categories') }}
                    </h1>
                    <p class="text-muted fs-16 mb-0">{{ translate('Discover amazing products from our wide range of categories') }}</p>
                </div>
                <div class="col-lg-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0" style="background: transparent;">
                            <li class="breadcrumb-item">
                                <a class="text-reset d-flex align-items-center hov-text-primary" href="{{ route('home') }}">
                                    <i class="las la-home fs-18 mr-2"></i>
                                    {{ translate('Home') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-dark fw-600" aria-current="page">
                                {{ translate('Categories') }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- All Categories -->
    <section class="mb-5 pb-5">
        <div class="container">
            <div class="row">
                @foreach ($categories as $key => $category)
                    <div class="col-12 mb-5">
                        <!-- Main Category Card -->
                        <div class="category-card rounded-lg overflow-hidden shadow-lg hover-lift" 
                             style="background: white; border: 1px solid rgba(230, 46, 4, 0.1);">
                            
                            <!-- Category Header -->
                            <div class="category-header p-4 p-md-5" style="
                                background: linear-gradient(135deg, rgba(230, 46, 4, 0.05), rgba(255, 153, 0, 0.05));
                                border-bottom: 1px solid rgba(230, 46, 4, 0.1);
                            ">
                                <div class="row align-items-center">
                                    <div class="col-md-auto mb-3 mb-md-0">
                                        <div class="category-icon-wrapper" style="
                                            width: 80px;
                                            height: 80px;
                                            background: white;
                                            border-radius: 16px;
                                            padding: 12px;
                                            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.1);
                                            border: 2px solid rgba(230, 46, 4, 0.2);
                                        ">
                                            <img src="{{ uploaded_asset($category->banner) }}" 
                                                 alt="{{ $category->getTranslation('name') }}"
                                                 class="img-fit h-100 rounded-lg"
                                                 onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <h2 class="fw-800 fs-24 fs-md-28 mb-2">
                                            <a href="{{ route('products.category', $category->slug) }}" 
                                               class="text-dark hov-text-primary" 
                                               style="text-decoration: none;">
                                                {{ $category->getTranslation('name') }}
                                            </a>
                                        </h2>
                                        <p class="text-muted mb-0">
                                            {{ translate('Explore all sub-categories and products') }}
                                        </p>
                                    </div>
                                    <div class="col-md-auto">
                                        <a href="{{ route('products.category', $category->slug) }}" 
                                           class="btn btn-primary btn-lg px-4 py-3 fw-700 rounded-lg d-flex align-items-center"
                                           style="
                                                background: linear-gradient(45deg, #e62e04, #ff9900);
                                                border: none;
                                                box-shadow: 0 4px 15px rgba(230, 46, 4, 0.2);
                                            ">
                                            <span>{{ translate('View All') }}</span>
                                            <i class="las la-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub Categories Grid -->
                            <div class="category-body p-4 p-md-5">
                                <div class="row row-cols-xxl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1 g-4">
                                    @foreach ($category->childrenCategories as $key => $child_category)
                                        <div class="col">
                                            <div class="sub-category-card h-100 p-4 rounded-lg" 
                                                 style="
                                                    background: rgba(255, 255, 255, 0.9);
                                                    border: 1px solid rgba(230, 46, 4, 0.1);
                                                    transition: all 0.3s ease;
                                                 ">
                                                <!-- Sub Category Header -->
                                                <div class="mb-4">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="sub-category-icon mr-3" style="
                                                            width: 40px;
                                                            height: 40px;
                                                            background: rgba(230, 46, 4, 0.1);
                                                            border-radius: 10px;
                                                            padding: 8px;
                                                            border: 1px solid rgba(230, 46, 4, 0.2);
                                                        ">
                                                            <img src="{{ uploaded_asset($child_category->icon ?? $child_category->banner) }}" 
                                                                 alt="{{ $child_category->getTranslation('name') }}"
                                                                 class="img-fit h-100"
                                                                 onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                                        </div>
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('products.category', $child_category->slug) }}" 
                                                               class="text-dark fw-700 fs-16 hov-text-primary"
                                                               style="text-decoration: none;">
                                                                {{ $child_category->getTranslation('name') }}
                                                            </a>
                                                        </h6>
                                                    </div>
                                                    <p class="text-muted fs-12 mb-0">
                                                        {{ $child_category->products_count ?? 0 }} {{ translate('Products') }}
                                                    </p>
                                                </div>

                                                <!-- Sub-sub Categories List -->
                                                <div class="sub-category-list">
                                                    <ul class="list-unstyled mb-0 mh-200px 
                                                        @if ($child_category->childrenCategories->count() > 5) less @endif"
                                                        style="overflow: hidden; transition: all 0.3s ease;">
                                                        
                                                        @foreach ($child_category->childrenCategories as $key => $second_level_category)
                                                            <li class="mb-3">
                                                                <a href="{{ route('products.category', $second_level_category->slug) }}" 
                                                                   class="text-reset d-flex align-items-center hov-text-primary"
                                                                   style="text-decoration: none;">
                                                                    <div class="mr-3" style="
                                                                        width: 28px;
                                                                        height: 28px;
                                                                        background: rgba(230, 46, 4, 0.05);
                                                                        border-radius: 8px;
                                                                        padding: 4px;
                                                                        border: 1px solid rgba(230, 46, 4, 0.1);
                                                                    ">
                                                                        <img src="{{ uploaded_asset($second_level_category->icon ?? $second_level_category->banner) }}" 
                                                                             alt="{{ $second_level_category->getTranslation('name') }}"
                                                                             class="img-fit h-100 rounded"
                                                                             onerror="this.style.display='none'">
                                                                    </div>
                                                                    <span class="fw-500 fs-14 text-truncate" style="flex: 1;">
                                                                        {{ $second_level_category->getTranslation('name') }}
                                                                    </span>
                                                                    <i class="las la-angle-right text-muted ml-2 fs-12"></i>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                    <!-- Show More/Less Button -->
                                                    @if ($child_category->childrenCategories->count() > 5)
                                                        <div class="text-center mt-3">
                                                            <a href="javascript:void(0)"
                                                               class="show-hide-category text-primary fw-600 fs-12 d-inline-flex align-items-center"
                                                               data-target=".sub-category-list ul">
                                                                {{ translate('Show More') }}
                                                                <i class="las la-angle-down ml-1"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- No Categories Message -->
            @if($categories->count() == 0)
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="las la-folder-open la-4x text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">{{ translate('No Categories Found') }}</h4>
                        <p class="text-muted mb-4">{{ translate('There are no categories available at the moment.') }}</p>
                        <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2">
                            <i class="las la-home mr-2"></i>
                            {{ translate('Back to Home') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 mb-5" style="
        background: linear-gradient(135deg, rgba(230, 46, 4, 0.05), rgba(255, 153, 0, 0.05));
        border-top: 1px solid rgba(230, 46, 4, 0.1);
        border-bottom: 1px solid rgba(230, 46, 4, 0.1);
    ">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h3 class="fw-700 fs-24 text-dark mb-2">{{ translate('Can\'t find what you\'re looking for?') }}</h3>
                    <p class="text-muted mb-0">{{ translate('Try searching for specific products or browse our entire collection') }}</p>
                </div>
                <div class="col-lg-4 text-lg-right">
                    <a href="{{ route('inhouse.all') }}" class="btn btn-outline-primary btn-lg px-4 py-3 fw-700 rounded-lg d-inline-flex align-items-center">
                        <i class="las la-arrow-alt-circle-right mr-2"></i>
                        {{ translate('All Products') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Show/Hide Sub-sub Categories
            $('.show-hide-category').on('click', function() {
                var $button = $(this);
                var $list = $button.closest('.sub-category-list').find('ul');
                
                if ($list.hasClass('less')) {
                    $list.removeClass('less');
                    $list.css('max-height', 'none');
                    $button.html('{{ translate("Show Less") }} <i class="las la-angle-up ml-1"></i>');
                } else {
                    $list.addClass('less');
                    $list.css('max-height', '200px');
                    $button.html('{{ translate("Show More") }} <i class="las la-angle-down ml-1"></i>');
                }
            });

            // Initialize lists with less class
            $('.sub-category-list ul.less').css('max-height', '200px');

            // Hover effects for cards
            $('.sub-category-card').hover(
                function() {
                    $(this).css({
                        'transform': 'translateY(-5px)',
                        'box-shadow': '0 10px 25px rgba(230, 46, 4, 0.1)',
                        'border-color': 'rgba(230, 46, 4, 0.3)'
                    });
                },
                function() {
                    $(this).css({
                        'transform': 'translateY(0)',
                        'box-shadow': 'none',
                        'border-color': 'rgba(230, 46, 4, 0.1)'
                    });
                }
            );

            // Hover effect for main category card
            $('.category-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-8px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });

        // Image error handling
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('img').forEach(img => {
                img.onerror = function() {
                    if (!this.hasAttribute('data-placeholder')) {
                        this.src = '{{ static_asset("assets/img/placeholder.jpg") }}';
                        this.setAttribute('data-placeholder', 'true');
                    }
                };
            });
        });
    </script>
@endsection

<style>
    /* Custom Styles */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
    }

    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    }

    .mh-200px {
        max-height: 200px;
        overflow: hidden;
    }

    .sub-category-card {
        transition: all 0.3s ease;
    }

    .category-icon-wrapper img,
    .sub-category-icon img {
        object-fit: contain;
    }

    /* Scrollbar styling for sub-category lists */
    .sub-category-list ul {
        scrollbar-width: thin;
        scrollbar-color: rgba(230, 46, 4, 0.3) transparent;
    }

    .sub-category-list ul::-webkit-scrollbar {
        width: 4px;
    }

    .sub-category-list ul::-webkit-scrollbar-track {
        background: transparent;
    }

    .sub-category-list ul::-webkit-scrollbar-thumb {
        background-color: rgba(230, 46, 4, 0.3);
        border-radius: 10px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .category-header {
            flex-direction: column;
            text-align: center;
        }
        
        .category-icon-wrapper {
            margin: 0 auto 20px !important;
        }
        
        .btn-lg {
            padding: 12px 24px !important;
            font-size: 14px;
        }
    }

    @media (max-width: 576px) {
        .sub-category-card {
            padding: 20px !important;
        }
        
        .category-body {
            padding: 20px !important;
        }
        
        .fs-32 {
            font-size: 24px !important;
        }
        
        .fs-24 {
            font-size: 20px !important;
        }
    }

    /* Animation for page load */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .category-card {
        animation: fadeInUp 0.5s ease forwards;
    }

    /* Staggered animation for sub-categories */
    .sub-category-card {
        animation: fadeInUp 0.6s ease forwards;
        animation-delay: calc(var(--animation-order) * 0.1s);
        opacity: 0;
    }

    /* Category card numbering */
    .category-card:nth-child(1) { --animation-order: 1; }
    .category-card:nth-child(2) { --animation-order: 2; }
    .category-card:nth-child(3) { --animation-order: 3; }
    .category-card:nth-child(4) { --animation-order: 4; }
    .category-card:nth-child(5) { --animation-order: 5; }

    /* Gradient text animation */
    .hov-text-primary:hover {
        background: linear-gradient(45deg, #e62e04, #ff9900);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Button hover effect */
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(230, 46, 4, 0.3) !important;
    }
</style>