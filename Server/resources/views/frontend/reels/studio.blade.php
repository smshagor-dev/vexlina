@extends('frontend.layouts.app')

@section('meta_title'){{ translate('Upload Reel') }}@stop

@section('content')
    <style>
        .reels-upload-page { background: radial-gradient(circle at top left, rgba(250, 62, 0, .08), transparent 28%), linear-gradient(180deg, #f8f5f1 0%, #ffffff 24%, #ffffff 100%); }
        .upload-shell { max-width: 1120px; margin: 0 auto; }
        .upload-hero {
            border-radius: 30px; background: linear-gradient(135deg, #fff4ee 0%, #ffffff 52%, #fff 100%);
            border: 1px solid rgba(250, 62, 0, .1); box-shadow: 0 24px 56px rgba(15, 23, 42, .08); padding: 34px;
        }
        .upload-hero h1 { font-size: 34px; font-weight: 800; color: #101828; margin-bottom: 10px; }
        .upload-hero p { color: #667085; max-width: 700px; margin-bottom: 0; }
        .upload-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr); gap: 24px; }
        .upload-card { border: 0; border-radius: 28px; box-shadow: 0 18px 46px rgba(15, 23, 42, .08); }
        .upload-card .card-body { padding: 26px; }
        .upload-title { font-size: 24px; font-weight: 800; color: #101828; margin-bottom: 4px; }
        .upload-subtitle { color: #667085; margin-bottom: 0; }
        .upload-tips { border-radius: 24px; background: linear-gradient(180deg, #111827 0%, #1f2937 100%); color: #fff; height: 100%; }
        .upload-tips h3 { font-size: 20px; font-weight: 800; margin-bottom: 14px; }
        .upload-tips p { color: rgba(255,255,255,.72); }
        .upload-tip { border-radius: 18px; background: rgba(255,255,255,.07); padding: 16px; margin-top: 12px; }
        .upload-tip strong { display: block; font-size: 14px; margin-bottom: 6px; }
        .upload-tip span { color: rgba(255,255,255,.7); font-size: 13px; line-height: 1.6; }
        .upload-form .form-control, .upload-form textarea { border-radius: 18px; min-height: 52px; border-color: #e4e7ec; box-shadow: none !important; }
        .upload-form textarea { min-height: 140px; resize: vertical; }
        .upload-form label { font-weight: 700; color: #344054; margin-bottom: 8px; }
        @media (max-width: 991.98px) {
            .upload-grid { grid-template-columns: 1fr; }
            .upload-hero h1 { font-size: 30px; }
        }
    </style>

    <section class="reels-upload-page py-4">
        <div class="container upload-shell">
            <div class="upload-hero mb-4">
                <h1>{{ translate('Upload A New Reel') }}</h1>
                <p>{{ translate('This page is dedicated to creation only. Your public audience sees the reels feed, while you get a cleaner studio workflow for uploading, linking products, and publishing short-form content.') }}</p>
                <div class="d-flex flex-wrap mt-4" style="gap:12px;">
                    <a href="{{ route('reels.dashboard') }}" class="btn btn-outline-dark rounded-pill px-4 fw-700">{{ translate('Back To Studio') }}</a>
                    <a href="{{ route('reels.index') }}" class="btn btn-soft-primary rounded-pill px-4 fw-700">{{ translate('View Public Feed') }}</a>
                </div>
            </div>

            @if (!$canPost)
                <div class="alert alert-warning rounded-3 border-0 shadow-sm">{{ translate('Only sellers or customers with an active classified package can upload reels.') }}</div>
            @else
                <div class="upload-grid">
                    <div class="card upload-card">
                        <div class="card-body">
                            <div class="mb-4">
                                <h2 class="upload-title">{{ translate('Reel Details') }}</h2>
                                <p class="upload-subtitle">{{ translate('Add your video, optional thumbnail, a product reference, and a caption that helps viewers understand what they are seeing.') }}</p>
                            </div>
                            <form action="{{ route('reels.store') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <label>{{ translate('Video File') }}</label>
                                        <input type="file" name="video" class="form-control" accept="video/*" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label>{{ translate('Thumbnail Image') }}</label>
                                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>{{ translate('Linked Product ID') }}</label>
                                        <input type="number" name="product_id" class="form-control" placeholder="{{ translate('Optional product id for direct shopping') }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>{{ translate('Caption') }}</label>
                                        <textarea name="caption" class="form-control" placeholder="{{ translate('Describe the product, highlight the offer, or add a short hook for viewers') }}"></textarea>
                                    </div>
                                    <div class="col-12 d-flex flex-wrap align-items-center justify-content-between mt-2" style="gap:16px;">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="studio_allow_comments" name="allow_comments" value="1" checked>
                                            <label class="custom-control-label" for="studio_allow_comments">{{ translate('Allow Comments') }}</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-700">{{ translate('Publish Reel') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card upload-card upload-tips">
                        <div class="card-body">
                            <h3>{{ translate('Publishing Guide') }}</h3>
                            <p>{{ translate('A professional reel performs best when it is easy to understand without effort and connects quickly to a product or offer.') }}</p>
                            <div class="upload-tip"><strong>{{ translate('Keep it short and focused') }}</strong><span>{{ translate('Lead with the strongest visual in the first few seconds and keep one clear message per reel.') }}</span></div>
                            <div class="upload-tip"><strong>{{ translate('Link a product when possible') }}</strong><span>{{ translate('A linked product helps the viewer go from discovery to product page with less friction.') }}</span></div>
                            <div class="upload-tip"><strong>{{ translate('Use a strong thumbnail') }}</strong><span>{{ translate('Choose a clean still frame or upload a custom image that looks good in grid view.') }}</span></div>
                            <div class="upload-tip"><strong>{{ translate('Write a useful caption') }}</strong><span>{{ translate('Keep captions concise, specific, and product-aware instead of generic filler text.') }}</span></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
