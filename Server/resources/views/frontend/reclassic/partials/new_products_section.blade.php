@if (count($new_products) > 0)
<section class="py-0">
    <div class="container">
        <div class="row px-3" id="new-products-list">
            @foreach ($new_products as $new_product)

                @php
                    $ratingAvg = \App\Models\Review::where('product_id', $new_product->id)->avg('rating') ?? 0;
                    $ratingAvg = round($ratingAvg, 1);

                    $reviewCount = \App\Models\Review::where('product_id', $new_product->id)->count();

                    $fullStars = floor($ratingAvg);
                    $hasHalfStar = ($ratingAvg - $fullStars) >= 0.5;
                @endphp

                <div class="col-md-3 col-lg-3 col-xl-2 col-sm-4 col-6 d-flex product-card hov-animate-outline-2 justify-content-center mx-auto">
                    <div class="carousel-box has-transition rounded-2 text-center">

                        {{-- Product box --}}
                        @include(
                            'frontend.' . get_setting('homepage_select') . '.partials.home_product_box',
                            ['product' => $new_product]
                        )

                        {{-- Rating --}}
                        <div class="mt-1" style="font-size:13px; color:#444;">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $fullStars)
                                    <span style="color:#f5c518;">&#9733;</span>
                                @elseif ($i == $fullStars + 1 && $hasHalfStar)
                                    <span style="color:#f5c518;">&#9733;</span>
                                @else
                                    <span style="color:#ddd;">&#9733;</span>
                                @endif
                            @endfor

                            <span style="margin-left:5px; font-weight:600;">
                                {{ $ratingAvg }}
                            </span>
                            <span style="color:#777;">
                                ({{ $reviewCount }}) reviews
                            </span>
                        </div>

                    </div>
                </div>

            @endforeach
        </div>
    </div>
</section>
@endif

