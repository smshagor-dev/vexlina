@extends('frontend.layouts.app')

@section('meta_title'){{ translate('Reel Details') }}@stop

@section('content')
    @php
        $videoUpload = $reel->video;
        $thumbnailUpload = $reel->thumbnail;
        $videoUrl = $videoUpload ? my_asset($videoUpload->file_name) : null;
        $thumbnailUrl = $thumbnailUpload ? my_asset($thumbnailUpload->file_name) : '';
        $videoMime = $videoUpload && $videoUpload->extension === 'webm' ? 'video/webm' : 'video/mp4';
    @endphp
    <section class="py-4 bg-light">
        <div class="container">
            <div class="mb-3">
                <a href="{{ route('reels.index') }}" class="text-reset">
                    <i class="las la-angle-left"></i> {{ translate('Back to reels') }}
                </a>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row no-gutters">
                    <div class="col-lg-6 bg-dark">
                        <video class="w-100 h-100" controls autoplay preload="metadata" playsinline poster="{{ $thumbnailUrl }}">
                            @if ($videoUrl)
                                <source src="{{ $videoUrl }}" type="{{ $videoMime }}">
                            @endif
                            <a href="{{ $videoUrl ?: route('reels.index') }}">{{ translate('Open reel video') }}</a>
                        </video>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-body h-100 d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $reel->user && $reel->user->avatar_original ? uploaded_asset($reel->user->avatar_original) : static_asset('assets/img/avatar-place.png') }}"
                                     class="rounded-circle mr-2" width="46" height="46" alt="{{ $reel->user ? $reel->user->name : 'User' }}">
                                <div>
                                    <div class="fw-700">{{ $reel->user ? $reel->user->name : translate('Unknown user') }}</div>
                                    <small class="text-muted">{{ optional($reel->created_at)->diffForHumans() }}</small>
                                </div>
                            </div>

                            <p class="text-dark">{{ $reel->caption ?: translate('No caption added.') }}</p>

                            <div class="d-flex flex-wrap mb-3" style="gap:16px;">
                                <span><strong>{{ $reel->views_count }}</strong> {{ translate('views') }}</span>
                                <span><strong>{{ $reel->likes_count }}</strong> {{ translate('likes') }}</span>
                                <span><strong>{{ $reel->comments_count }}</strong> {{ translate('comments') }}</span>
                                <span><strong>{{ $reel->shares_count }}</strong> {{ translate('shares') }}</span>
                                <span><strong>{{ $reel->saves_count }}</strong> {{ translate('saves') }}</span>
                            </div>

                            @auth
                                <div class="d-flex flex-wrap mb-3" style="gap:10px;">
                                    <form action="{{ route('reels.like', $reel->id) }}" method="POST">@csrf
                                        <button class="btn btn-soft-danger rounded-pill">{{ translate('Like / Unlike') }}</button>
                                    </form>
                                    <form action="{{ route('reels.save', $reel->id) }}" method="POST">@csrf
                                        <button class="btn btn-soft-info rounded-pill">{{ translate('Save / Unsave') }}</button>
                                    </form>
                                    <form action="{{ route('reels.share', $reel->id) }}" method="POST">@csrf
                                        <input type="hidden" name="platform" value="web">
                                        <button class="btn btn-soft-primary rounded-pill">{{ translate('Share Count +1') }}</button>
                                    </form>
                                    @if ((int) auth()->id() === (int) $reel->user_id)
                                        <form action="{{ route('reels.destroy', $reel->id) }}" method="POST">@csrf
                                            <button class="btn btn-soft-dark rounded-pill">{{ translate('Delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            @endauth

                            @if ($reel->product)
                                <div class="border rounded-3 p-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ uploaded_asset($reel->product->thumbnail_img) }}" width="64" height="64" class="rounded mr-3" alt="{{ $reel->product->name }}">
                                        <div class="flex-grow-1">
                                            <div class="fw-700">{{ $reel->product->name }}</div>
                                            <small class="text-primary">{{ single_price($reel->product->unit_price) }}</small>
                                        </div>
                                        <a href="{{ route('product', $reel->product->slug) }}" class="btn btn-primary rounded-pill">{{ translate('Buy Now') }}</a>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-auto">
                                <h5 class="mb-3">{{ translate('Comments') }}</h5>
                                @auth
                                    @if ($reel->allow_comments)
                                        <form action="{{ route('reels.comment', $reel->id) }}" method="POST" class="mb-3">
                                            @csrf
                                            <textarea name="comment" class="form-control mb-2" rows="3" placeholder="{{ translate('Write a comment') }}"></textarea>
                                            <button class="btn btn-primary rounded-pill">{{ translate('Post Comment') }}</button>
                                        </form>
                                    @endif
                                @endauth

                                <div style="max-height: 320px; overflow:auto;">
                                    @forelse ($reel->comments->where('parent_id', null)->where('status', 'published')->sortByDesc('id') as $comment)
                                        <div class="border rounded-3 p-3 mb-2">
                                            <div class="fw-700">{{ $comment->user ? $comment->user->name : translate('Unknown user') }}</div>
                                            <div class="text-muted small mb-2">{{ optional($comment->created_at)->diffForHumans() }}</div>
                                            <div>{{ $comment->comment }}</div>
                                            @foreach ($comment->replies->where('status', 'published') as $reply)
                                                <div class="bg-light rounded p-2 mt-2 ml-3">
                                                    <div class="fw-700">{{ $reply->user ? $reply->user->name : translate('Unknown user') }}</div>
                                                    <div class="small text-muted">{{ optional($reply->created_at)->diffForHumans() }}</div>
                                                    <div>{{ $reply->comment }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @empty
                                        <div class="text-muted">{{ translate('No comments yet.') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
