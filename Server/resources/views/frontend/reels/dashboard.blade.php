@extends('frontend.layouts.app')

@section('meta_title'){{ translate('Reels Studio') }}@stop

@section('content')
    <style>
        .reels-studio-page { background: radial-gradient(circle at top right, rgba(250, 62, 0, .08), transparent 24%), linear-gradient(180deg, #f8f5f1 0%, #ffffff 24%, #ffffff 100%); }
        .studio-shell { max-width: 1240px; margin: 0 auto; }
        .studio-hero {
            border-radius: 30px; background: linear-gradient(135deg, #101828 0%, #1d2939 52%, #243b53 100%);
            color: #fff; padding: 32px; box-shadow: 0 24px 54px rgba(15, 23, 42, .14);
        }
        .studio-hero h1 { font-size: 36px; font-weight: 800; margin-bottom: 10px; }
        .studio-hero p { color: rgba(255,255,255,.74); margin-bottom: 0; max-width: 700px; }
        .studio-hero__actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .studio-kpis { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 14px; margin-top: 26px; }
        .studio-kpi { border-radius: 20px; background: rgba(255,255,255,.08); padding: 18px; }
        .studio-kpi strong { display: block; font-size: 24px; line-height: 1; margin-bottom: 6px; }
        .studio-kpi span { color: rgba(255,255,255,.65); font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .studio-panel { border: 0; border-radius: 26px; box-shadow: 0 18px 46px rgba(15, 23, 42, .08); }
        .studio-panel__head { padding: 22px 24px 0; }
        .studio-panel__title { font-size: 22px; font-weight: 800; color: #101828; margin-bottom: 4px; }
        .studio-panel__sub { color: #667085; margin-bottom: 0; }
        .studio-empty {
            border-radius: 24px; background: linear-gradient(180deg, #fff8f4 0%, #ffffff 100%);
            border: 1px dashed rgba(250, 62, 0, .22); padding: 36px 28px; text-align: center;
        }
        .studio-grid-card {
            border: 0; border-radius: 24px; overflow: hidden; background: #fff;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08); height: 100%;
        }
        .studio-grid-card video { width: 100%; aspect-ratio: 9/15; object-fit: cover; background: #0f172a; }
        .studio-grid-card__body { padding: 18px; }
        .studio-grid-card__caption { min-height: 72px; color: #344054; line-height: 1.6; }
        .studio-grid-card__meta { color: #667085; font-size: 13px; margin-top: 10px; }
        .studio-grid-card__stats { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 8px; margin: 16px 0; }
        .studio-grid-card__stats span { border-radius: 14px; background: #f8fafc; padding: 10px 6px; text-align: center; font-size: 12px; font-weight: 700; color: #475467; }
        .studio-grid-card__actions { display: flex; gap: 10px; }
        .studio-grid-card__actions .btn { border-radius: 999px; font-weight: 700; }
        @media (max-width: 991.98px) {
            .studio-kpis { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .studio-hero h1 { font-size: 30px; }
        }
    </style>

    @php
        $totalReels = $reels->total();
        $totalViews = $reels->sum('views_count');
        $totalLikes = $reels->sum('likes_count');
        $totalComments = $reels->sum('comments_count');
    @endphp

    <section class="reels-studio-page py-4">
        <div class="container studio-shell">
            <div class="studio-hero mb-4">
                <h1>{{ translate('Reels Studio') }}</h1>
                <p>{{ translate('Keep public viewing and creator workflow separate. This studio is your focused workspace for uploading, reviewing performance, and managing published reels professionally.') }}</p>
                <div class="studio-hero__actions">
                    @if ($canPost)
                        <a href="{{ route('reels.create') }}" class="btn btn-primary rounded-pill px-4 fw-700">{{ translate('Upload New Reel') }}</a>
                    @endif
                    <a href="{{ route('reels.index') }}" class="btn btn-outline-light rounded-pill px-4 fw-700">{{ translate('Open Public Feed') }}</a>
                </div>
                <div class="studio-kpis">
                    <div class="studio-kpi"><strong>{{ $totalReels }}</strong><span>{{ translate('My Reels') }}</span></div>
                    <div class="studio-kpi"><strong>{{ $totalViews }}</strong><span>{{ translate('Total Views') }}</span></div>
                    <div class="studio-kpi"><strong>{{ $totalLikes }}</strong><span>{{ translate('Total Likes') }}</span></div>
                    <div class="studio-kpi"><strong>{{ $totalComments }}</strong><span>{{ translate('Comments') }}</span></div>
                </div>
            </div>

            @if (!$canPost)
                <div class="alert alert-warning rounded-3 border-0 shadow-sm mb-4">{{ translate('You need a seller account or active classified package to post reels.') }}</div>
            @endif

            <div class="card studio-panel mb-4">
                <div class="studio-panel__head">
                    <h2 class="studio-panel__title">{{ translate('Content Library') }}</h2>
                    <p class="studio-panel__sub">{{ translate('Everything you have posted lives here. Open a reel to engage with it, or remove one that no longer fits your storefront.') }}</p>
                </div>
                <div class="card-body pt-4">
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
                                <div class="studio-grid-card">
                                    <video controls preload="metadata" playsinline poster="{{ $thumbnailUrl }}">
                                        @if ($videoUrl)
                                            <source src="{{ $videoUrl }}" type="{{ $videoMime }}">
                                        @endif
                                        <a href="{{ $videoUrl ?: route('reels.show', $reel->id) }}">{{ translate('Open reel video') }}</a>
                                    </video>
                                    <div class="studio-grid-card__body">
                                        <div class="small text-muted mb-2">{{ optional($reel->created_at)->diffForHumans() }}</div>
                                        <div class="studio-grid-card__caption">{{ \Illuminate\Support\Str::limit($reel->caption ?: translate('No caption added.'), 120) }}</div>
                                        <div class="studio-grid-card__meta">
                                            <div>{{ $reel->product ? $reel->product->name : translate('No linked product') }}</div>
                                            <div>{{ $reel->allow_comments ? translate('Comments on') : translate('Comments off') }}</div>
                                        </div>
                                        <div class="studio-grid-card__stats">
                                            <span>{{ $reel->views_count }} {{ translate('Views') }}</span>
                                            <span>{{ $reel->likes_count }} {{ translate('Likes') }}</span>
                                            <span>{{ $reel->comments_count }} {{ translate('Comments') }}</span>
                                            <span>{{ $reel->shares_count }} {{ translate('Shares') }}</span>
                                        </div>
                                        <div class="studio-grid-card__actions">
                                            <a href="{{ route('reels.show', $reel->id) }}" class="btn btn-dark flex-grow-1">{{ translate('Open') }}</a>
                                            <a href="{{ route('reels.edit', $reel->id) }}" class="btn btn-soft-primary flex-grow-1">{{ translate('Edit') }}</a>
                                            <form action="{{ route('reels.destroy', $reel->id) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button class="btn btn-soft-danger w-100">{{ translate('Delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="studio-empty">
                                    <h3 class="h5 fw-800 mb-2">{{ translate('No reels in your studio yet') }}</h3>
                                    <p class="text-muted mb-3">{{ translate('Start with a short product video, add a caption, and publish it from your dedicated upload workspace.') }}</p>
                                    @if ($canPost)
                                        <a href="{{ route('reels.create') }}" class="btn btn-primary rounded-pill px-4 fw-700">{{ translate('Create First Reel') }}</a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <div class="aiz-pagination aiz-pagination-center">{{ $reels->links() }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection
