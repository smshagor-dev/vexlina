@extends('frontend.layouts.app')

@section('meta_title'){{ get_setting('meta_title') }} | Reels @stop

@section('content')
    <section class="py-4 bg-light">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">{{ translate('Reels') }}</h1>
                    <p class="mb-0 text-muted">{{ translate('Watch short product videos, like, save, comment and buy instantly.') }}</p>
                </div>
                <div class="d-flex align-items-center" style="gap:12px;">
                    @auth
                        <a href="{{ route('reels.dashboard') }}" class="btn btn-soft-primary rounded-pill">
                            {{ translate('My Reels') }}
                        </a>
                    @endauth
                </div>
            </div>

            @auth
                @if ($canPost)
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-body">
                            <h2 class="h5 mb-3">{{ translate('Post A Reel') }}</h2>
                            <form action="{{ route('reels.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label">{{ translate('Video') }}</label>
                                        <input type="file" name="video" class="form-control" accept="video/*" required>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label">{{ translate('Thumbnail') }}</label>
                                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label">{{ translate('Linked Product ID') }}</label>
                                        <input type="number" name="product_id" class="form-control" placeholder="{{ translate('Optional product id') }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ translate('Caption') }}</label>
                                        <textarea name="caption" rows="3" class="form-control" placeholder="{{ translate('Write something about this reel') }}"></textarea>
                                    </div>
                                    <div class="col-12 d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="allow_comments" name="allow_comments" value="1" checked>
                                            <label class="custom-control-label" for="allow_comments">{{ translate('Allow Comments') }}</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ translate('Publish Reel') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        {{ translate('Only sellers or customers with an active classified package can post reels.') }}
                    </div>
                @endif
            @endauth

            <div class="row">
                @forelse ($reels as $reel)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100 overflow-hidden">
                            <div class="bg-dark">
                                <video class="w-100" controls preload="metadata" poster="{{ $reel->thumbnail_upload_id ? uploaded_asset($reel->thumbnail_upload_id) : '' }}">
                                    <source src="{{ uploaded_asset($reel->video_upload_id) }}">
                                </video>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $reel->user && $reel->user->avatar_original ? uploaded_asset($reel->user->avatar_original) : static_asset('assets/img/avatar-place.png') }}"
                                         class="rounded-circle mr-2" width="42" height="42" alt="{{ $reel->user ? $reel->user->name : 'User' }}">
                                    <div>
                                        <div class="fw-700">{{ $reel->user ? $reel->user->name : translate('Unknown user') }}</div>
                                        <small class="text-muted">{{ optional($reel->created_at)->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <p class="text-dark flex-grow-1">{{ $reel->caption ?: translate('No caption added.') }}</p>

                                <div class="d-flex flex-wrap text-muted mb-3" style="gap:14px;">
                                    <span>{{ $reel->views_count }} {{ translate('views') }}</span>
                                    <span>{{ $reel->likes_count }} {{ translate('likes') }}</span>
                                    <span>{{ $reel->comments_count }} {{ translate('comments') }}</span>
                                    <span>{{ $reel->saves_count }} {{ translate('saves') }}</span>
                                </div>

                                @if ($reel->product)
                                    <a href="{{ route('product', $reel->product->slug) }}" class="btn btn-soft-primary rounded-pill mb-3">
                                        {{ translate('Buy Now') }}: {{ $reel->product->name }}
                                    </a>
                                @endif

                                <div class="d-flex flex-wrap" style="gap:10px;">
                                    <a href="{{ route('reels.show', $reel->id) }}" class="btn btn-outline-dark rounded-pill flex-grow-1">
                                        {{ translate('Open Reel') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">{{ translate('No reels found yet.') }}</div>
                    </div>
                @endforelse
            </div>

            <div class="aiz-pagination aiz-pagination-center">
                {{ $reels->links() }}
            </div>
        </div>
    </section>
@endsection
