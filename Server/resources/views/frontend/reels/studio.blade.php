@extends('frontend.layouts.app')

@section('meta_title'){{ translate('Upload Reel') }}@stop

@section('content')
    @php
        $isEdit = !empty($reel);
    @endphp
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
        .upload-progress-card { display: none; margin-top: 18px; border-radius: 22px; padding: 18px 20px; background: linear-gradient(180deg, #fff8f4 0%, #ffffff 100%); border: 1px solid rgba(250, 62, 0, .14); }
        .upload-progress-card.is-active { display: block; }
        .upload-progress-bar { height: 10px; border-radius: 999px; background: #ffe1d4; overflow: hidden; margin-top: 12px; }
        .upload-progress-bar > span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, #fa3e00 0%, #ff7a30 100%); transition: width .18s ease; }
        .upload-progress-meta { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: #475467; font-weight: 700; }
        .upload-file-note { color: #667085; font-size: 12px; margin-top: 8px; }
        @media (max-width: 991.98px) {
            .upload-grid { grid-template-columns: 1fr; }
            .upload-hero h1 { font-size: 30px; }
        }
    </style>

    <section class="reels-upload-page py-4">
        <div class="container upload-shell">
            <div class="upload-hero mb-4">
                <h1>{{ $isEdit ? translate('Edit Reel') : translate('Upload A New Reel') }}</h1>
                <p>{{ $isEdit ? translate('Update the caption, linked product, and comments setting from your studio without changing the public flow for viewers.') : translate('This page is dedicated to creation only. Your public audience sees the reels feed, while you get a cleaner studio workflow for uploading, linking products, and publishing short-form content.') }}</p>
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
                                <h2 class="upload-title">{{ $isEdit ? translate('Reel Settings') : translate('Reel Details') }}</h2>
                                <p class="upload-subtitle">{{ $isEdit ? translate('Edit caption, linked product, and comments preference. Your existing video stays published.') : translate('Add your video, optional thumbnail, a product reference, and a caption that helps viewers understand what they are seeing.') }}</p>
                            </div>
                            <form action="{{ $isEdit ? route('reels.update', $reel->id) : route('reels.store') }}" method="POST" enctype="multipart/form-data" class="upload-form" id="reelStudioForm" data-dashboard-url="{{ route('reels.dashboard') }}">
                                @csrf
                                <input type="hidden" name="duration_seconds" id="reel_duration_seconds" value="{{ old('duration_seconds') }}">
                                <div class="row">
                                    @if (!$isEdit)
                                        <div class="col-lg-6 mb-3">
                                            <label>{{ translate('Video File') }}</label>
                                            <input type="file" name="video" id="reel_video_input" class="form-control" accept=".mp4,.webm,video/mp4,video/webm" required>
                                            <div class="upload-file-note">{{ translate('Maximum video duration: 30 seconds. For web playback, use MP4 or WebM.') }}</div>
                                        </div>
                                        <div class="col-lg-6 mb-3">
                                            <label>{{ translate('Thumbnail Image') }}</label>
                                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                        </div>
                                    @endif
                                    <div class="col-12 mb-3">
                                        <label>{{ translate('Linked Product ID') }}</label>
                                        <input type="number" name="product_id" class="form-control" placeholder="{{ translate('Optional product id for direct shopping') }}" value="{{ old('product_id', optional($reel)->product_id) }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>{{ translate('Caption') }}</label>
                                        <textarea name="caption" class="form-control" placeholder="{{ translate('Describe the product, highlight the offer, or add a short hook for viewers') }}">{{ old('caption', optional($reel)->caption) }}</textarea>
                                    </div>
                                    <div class="col-12 d-flex flex-wrap align-items-center justify-content-between mt-2" style="gap:16px;">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="studio_allow_comments" name="allow_comments" value="1" {{ old('allow_comments', optional($reel)->allow_comments ?? true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="studio_allow_comments">{{ translate('Allow Comments') }}</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-700" id="reelStudioSubmit">{{ $isEdit ? translate('Save Changes') : translate('Publish Reel') }}</button>
                                    </div>
                                </div>
                            </form>
                            @if (!$isEdit)
                                <div class="upload-progress-card" id="reelUploadProgressCard">
                                    <div class="upload-progress-meta">
                                        <span id="reelUploadProgressLabel">{{ translate('Uploading reel... 0%') }}</span>
                                        <span id="reelUploadProgressPercent">0%</span>
                                    </div>
                                    <div class="upload-progress-bar"><span id="reelUploadProgressBar"></span></div>
                                </div>
                            @endif
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

    @if (!$isEdit)
        <script>
            (function () {
                const form = document.getElementById('reelStudioForm');
                const videoInput = document.getElementById('reel_video_input');
                const durationInput = document.getElementById('reel_duration_seconds');
                const submitButton = document.getElementById('reelStudioSubmit');
                const progressCard = document.getElementById('reelUploadProgressCard');
                const progressBar = document.getElementById('reelUploadProgressBar');
                const progressLabel = document.getElementById('reelUploadProgressLabel');
                const progressPercent = document.getElementById('reelUploadProgressPercent');

                if (!form || !videoInput || !durationInput) {
                    return;
                }

                const setProgress = (value) => {
                    const safeValue = Math.max(0, Math.min(100, value));
                    if (progressCard) {
                        progressCard.classList.add('is-active');
                    }
                    if (progressBar) {
                        progressBar.style.width = safeValue + '%';
                    }
                    if (progressLabel) {
                        progressLabel.textContent = 'Uploading reel... ' + safeValue + '%';
                    }
                    if (progressPercent) {
                        progressPercent.textContent = safeValue + '%';
                    }
                };

                videoInput.addEventListener('change', function () {
                    durationInput.value = '';

                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    const objectUrl = URL.createObjectURL(file);
                    const probe = document.createElement('video');
                    probe.preload = 'metadata';
                    probe.src = objectUrl;

                    probe.onloadedmetadata = function () {
                        const seconds = Math.ceil(probe.duration || 0);
                        URL.revokeObjectURL(objectUrl);

                        if (seconds > 30) {
                            videoInput.value = '';
                            durationInput.value = '';
                            alert('{{ translate('Please select a reel video within 30 seconds.') }}');
                            return;
                        }

                        durationInput.value = seconds;
                    };

                    probe.onerror = function () {
                        URL.revokeObjectURL(objectUrl);
                        videoInput.value = '';
                        durationInput.value = '';
                        alert('{{ translate('Unable to read the selected video duration.') }}');
                    };
                });

                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (!videoInput.files || !videoInput.files.length) {
                        alert('{{ translate('Please choose a reel video.') }}');
                        return;
                    }

                    submitButton.disabled = true;
                    setProgress(0);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.addEventListener('progress', function (event) {
                        if (!event.lengthComputable) {
                            return;
                        }

                        setProgress(Math.round((event.loaded / event.total) * 100));
                    });

                    xhr.addEventListener('load', function () {
                        setProgress(100);
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText || '{}');
                                window.location.href = response.redirect || form.dataset.dashboardUrl;
                                return;
                            } catch (error) {
                                window.location.href = form.dataset.dashboardUrl;
                                return;
                            }
                        }

                        if (xhr.status === 422) {
                            submitButton.disabled = false;
                            let message = '{{ translate('Upload failed. Please check the reel file and try again.') }}';
                            try {
                                const response = JSON.parse(xhr.responseText || '{}');
                                const errors = response.errors || {};
                                const firstKey = Object.keys(errors)[0];
                                if (firstKey && errors[firstKey] && errors[firstKey][0]) {
                                    message = errors[firstKey][0];
                                } else if (response.message) {
                                    message = response.message;
                                }
                            } catch (error) {}
                            alert(message);
                            return;
                        }

                        submitButton.disabled = false;
                        alert('{{ translate('Upload failed. Please check the reel file and try again.') }}');
                    });

                    xhr.addEventListener('error', function () {
                        submitButton.disabled = false;
                        alert('{{ translate('Upload failed. Please try again.') }}');
                    });

                    xhr.send(new FormData(form));
                });
            })();
        </script>
    @endif
@endsection
