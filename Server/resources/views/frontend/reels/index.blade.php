@extends('frontend.layouts.app')

@section('meta_title'){{ get_setting('meta_title') }} | Reels @stop

@section('content')
    <style>
        .reels-feed-page {
            background:
                radial-gradient(circle at top left, rgba(250, 62, 0, .08), transparent 32%),
                radial-gradient(circle at top right, rgba(15, 23, 42, .06), transparent 24%),
                linear-gradient(180deg, #f7f3ef 0%, #ffffff 26%, #ffffff 100%);
        }
        .reels-shell { max-width: 1240px; margin: 0 auto; }
        .reels-hero { padding: 42px 0 26px; }
        .reels-hero__card {
            border: 0;
            border-radius: 32px;
            overflow: hidden;
            background: linear-gradient(135deg, #111827 0%, #202938 48%, #fa3e00 140%);
            box-shadow: 0 26px 60px rgba(15, 23, 42, .14);
        }
        .reels-hero__content { padding: 34px; }
        .reels-kicker {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px;
            background: rgba(255,255,255,.1); color: #fff; font-size: 12px; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase;
        }
        .reels-hero h1 { color: #fff; font-size: 40px; line-height: 1.05; font-weight: 800; margin: 18px 0 12px; }
        .reels-hero p { color: rgba(255,255,255,.76); max-width: 640px; margin-bottom: 0; font-size: 15px; }
        .reels-hero__actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .reels-btn-primary {
            border: 0; color: #fff !important; border-radius: 999px; padding: 12px 22px; font-weight: 700;
            background: linear-gradient(135deg, #fa3e00, #ff6a2a); box-shadow: 0 18px 32px rgba(250, 62, 0, .28);
        }
        .reels-btn-soft {
            border-radius: 999px; padding: 12px 22px; font-weight: 700; border: 1px solid rgba(255,255,255,.18);
            color: #fff !important; background: rgba(255,255,255,.08);
        }
        .reels-statbar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 24px; }
        .reels-stat { border-radius: 20px; background: rgba(255,255,255,.08); padding: 16px 18px; color: #fff; }
        .reels-stat__value { display: block; font-size: 22px; line-height: 1; font-weight: 800; margin-bottom: 6px; }
        .reels-stat__label { color: rgba(255,255,255,.68); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .reels-feed-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .reels-feed-toolbar h2 { font-size: 24px; margin: 0; font-weight: 800; color: #101828; }
        .reels-feed-toolbar p { margin: 0; color: #667085; }
        .reel-card {
            border: 0; border-radius: 28px; overflow: hidden; background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .08); height: 100%;
        }
        .reel-card__media { position: relative; background: #0f172a; }
        .reel-card__media video { width: 100%; aspect-ratio: 9/15; object-fit: cover; display: block; background: #0f172a; }
        .reel-card__overlay {
            position: absolute; inset: auto 16px 16px 16px; display: flex; justify-content: space-between;
            align-items: center; gap: 12px;
        }
        .reel-card__pill {
            border-radius: 999px; background: rgba(17, 24, 39, .58); color: #fff; padding: 7px 12px;
            font-size: 12px; font-weight: 700; backdrop-filter: blur(10px);
        }
        .reel-card__body { padding: 18px 18px 20px; }
        .reel-card__author { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .reel-card__avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(250, 62, 0, .14); }
        .reel-card__name { margin: 0; font-size: 15px; font-weight: 700; color: #101828; }
        .reel-card__meta { margin: 0; font-size: 12px; color: #667085; }
        .reel-card__caption { color: #344054; line-height: 1.6; min-height: 72px; margin-bottom: 16px; }
        .reel-card__stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
        .reel-card__stats span {
            border-radius: 16px; background: #f8fafc; color: #475467; padding: 10px 8px; text-align: center;
            font-size: 12px; font-weight: 700;
        }
        .reel-card__actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .reel-card__actions .btn { border-radius: 999px; font-weight: 700; }
        @media (max-width: 991.98px) {
            .reels-hero h1 { font-size: 32px; }
            .reels-statbar { grid-template-columns: 1fr; }
        }
    </style>

    <section class="reels-feed-page py-4">
        <div class="container reels-shell">
            <div class="reels-hero">
                <div class="reels-hero__card">
                    <div class="reels-hero__content">
                        <span class="reels-kicker">{{ translate('Short Video Commerce') }}</span>
                        <h1>{{ translate('Watch reels, discover products, and move faster from inspiration to checkout.') }}</h1>
                        <p>{{ translate('The public reels feed is built for discovery first. Browse immersive short videos, open details, and jump directly into the linked product when something catches your eye.') }}</p>
                        <div class="reels-hero__actions">
                            @auth
                                <a href="{{ route('reels.dashboard') }}" class="btn reels-btn-primary">{{ translate('Open Reels Studio') }}</a>
                            @else
                                <a href="{{ route('user.login') }}" class="btn reels-btn-primary">{{ translate('Login To Create') }}</a>
                            @endauth
                            <a href="{{ route('categories.all') }}" class="btn reels-btn-soft">{{ translate('Explore Categories') }}</a>
                        </div>
                        <div class="reels-statbar">
                            <div class="reels-stat"><span class="reels-stat__value">{{ $reels->total() }}</span><span class="reels-stat__label">{{ translate('Published Reels') }}</span></div>
                            <div class="reels-stat"><span class="reels-stat__value">{{ translate('Public') }}</span><span class="reels-stat__label">{{ translate('Viewing Mode') }}</span></div>
                            <div class="reels-stat"><span class="reels-stat__value">{{ $canPost ? translate('Enabled') : translate('Viewer') }}</span><span class="reels-stat__label">{{ translate('Creator Access') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reels-feed-toolbar">
                <div>
                    <h2>{{ translate('Trending Feed') }}</h2>
                    <p>{{ translate('A cleaner public viewing area, separate from your upload workspace.') }}</p>
                </div>
                @auth
                    <a href="{{ route('reels.create') }}" class="btn btn-outline-dark rounded-pill px-4 fw-700">{{ translate('Upload New Reel') }}</a>
                @endauth
            </div>

            <div class="row">
                @forelse ($reels as $reel)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        @php
                            $videoUpload = $reel->video;
                            $thumbnailUpload = $reel->thumbnail;
                            $videoUrl = $videoUpload ? my_asset($videoUpload->file_name) : null;
                            $thumbnailUrl = $thumbnailUpload ? my_asset($thumbnailUpload->file_name) : '';
                            $videoMime = $videoUpload && $videoUpload->extension === 'webm' ? 'video/webm' : 'video/mp4';
                        @endphp
                        <div class="reel-card">
                            <div class="reel-card__media">
                                <video controls preload="metadata" playsinline poster="{{ $thumbnailUrl }}">
                                    @if ($videoUrl)
                                        <source src="{{ $videoUrl }}" type="{{ $videoMime }}">
                                    @endif
                                    <a href="{{ $videoUrl ?: route('reels.show', $reel->id) }}">{{ translate('Open reel video') }}</a>
                                </video>
                                <div class="reel-card__overlay">
                                    <span class="reel-card__pill">{{ optional($reel->created_at)->diffForHumans() }}</span>
                                    <span class="reel-card__pill">{{ $reel->duration_seconds ? $reel->duration_seconds . 's' : translate('Short') }}</span>
                                </div>
                            </div>
                            <div class="reel-card__body">
                                <div class="reel-card__author">
                                    <img src="{{ $reel->user && $reel->user->avatar_original ? uploaded_asset($reel->user->avatar_original) : static_asset('assets/img/avatar-place.png') }}" class="reel-card__avatar" alt="{{ $reel->user ? $reel->user->name : 'User' }}">
                                    <div>
                                        <p class="reel-card__name">{{ $reel->user ? $reel->user->name : translate('Unknown user') }}</p>
                                        <p class="reel-card__meta">{{ $reel->product ? $reel->product->name : translate('No linked product') }}</p>
                                    </div>
                                </div>
                                <div class="reel-card__caption">{{ \Illuminate\Support\Str::limit($reel->caption ?: translate('No caption added.'), 120) }}</div>
                                <div class="reel-card__stats">
                                    <span>{{ $reel->views_count }} {{ translate('Views') }}</span>
                                    <span>{{ $reel->likes_count }} {{ translate('Likes') }}</span>
                                    <span>{{ $reel->comments_count }} {{ translate('Comments') }}</span>
                                    <span>{{ $reel->saves_count }} {{ translate('Saves') }}</span>
                                </div>
                                <div class="reel-card__actions">
                                    <a href="{{ route('reels.show', $reel->id) }}" class="btn btn-dark flex-grow-1">{{ translate('Open Reel') }}</a>
                                    @if ($reel->product)
                                        <a href="{{ route('product', $reel->product->slug) }}" class="btn btn-soft-primary flex-grow-1">{{ translate('Buy Now') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="alert alert-info rounded-3 border-0 shadow-sm">{{ translate('No reels found yet.') }}</div></div>
                @endforelse
            </div>

            <div class="aiz-pagination aiz-pagination-center">
                {{ $reels->links() }}
            </div>
        </div>
    </section>
@endsection
