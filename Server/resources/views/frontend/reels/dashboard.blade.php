@extends('frontend.layouts.app')

@section('meta_title'){{ translate('My Reels') }}@stop

@section('content')
    <section class="py-4 bg-light">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">{{ translate('My Reels') }}</h1>
                    <p class="mb-0 text-muted">{{ translate('Manage the reels you posted and create new ones.') }}</p>
                </div>
                <a href="{{ route('reels.index') }}" class="btn btn-outline-dark rounded-pill">{{ translate('View Public Feed') }}</a>
            </div>

            @if ($canPost)
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ translate('Create New Reel') }}</h2>
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
                                    <label class="form-label">{{ translate('Product ID') }}</label>
                                    <input type="number" name="product_id" class="form-control" placeholder="{{ translate('Optional product id') }}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">{{ translate('Caption') }}</label>
                                    <textarea name="caption" rows="3" class="form-control"></textarea>
                                </div>
                                <div class="col-12 d-flex align-items-center justify-content-between">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="dashboard_allow_comments" name="allow_comments" value="1" checked>
                                        <label class="custom-control-label" for="dashboard_allow_comments">{{ translate('Allow Comments') }}</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">{{ translate('Publish Reel') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">{{ translate('You need a seller account or active classified package to post reels.') }}</div>
            @endif

            <div class="row">
                @forelse ($reels as $reel)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <video class="w-100" controls preload="metadata" poster="{{ $reel->thumbnail_upload_id ? uploaded_asset($reel->thumbnail_upload_id) : '' }}">
                                <source src="{{ uploaded_asset($reel->video_upload_id) }}">
                            </video>
                            <div class="card-body">
                                <p>{{ $reel->caption ?: translate('No caption added.') }}</p>
                                <div class="d-flex flex-wrap text-muted mb-3" style="gap:12px;">
                                    <span>{{ $reel->views_count }} {{ translate('views') }}</span>
                                    <span>{{ $reel->likes_count }} {{ translate('likes') }}</span>
                                    <span>{{ $reel->comments_count }} {{ translate('comments') }}</span>
                                </div>
                                <div class="d-flex flex-wrap" style="gap:10px;">
                                    <a href="{{ route('reels.show', $reel->id) }}" class="btn btn-outline-dark rounded-pill flex-grow-1">{{ translate('Open') }}</a>
                                    <form action="{{ route('reels.destroy', $reel->id) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button class="btn btn-soft-danger rounded-pill w-100">{{ translate('Delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">{{ translate('You have not posted any reel yet.') }}</div>
                    </div>
                @endforelse
            </div>

            <div class="aiz-pagination aiz-pagination-center">
                {{ $reels->links() }}
            </div>
        </div>
    </section>
@endsection
