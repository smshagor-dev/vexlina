<div class="modal-header" style="border-bottom: 2px solid #fa3e00; background: #fff; padding: 15px 20px;">
    <h5 class="modal-title h6" style="color: #333; font-weight: 600;">{{translate('Review')}}</h5>
    <button type="button" class="close" data-dismiss="modal" style="color: #fa3e00;">
        <span style="font-size: 28px;"></span>
    </button>
</div>

@if($review == null)
    <!-- Add new review -->
    <form action="{{ route('reviews.store') }}" method="POST" >
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="order_id" value="{{ $order_id }}">
        <div class="modal-body" style="padding: 20px;">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="opacity-60" style="color: #555; font-weight: 500; display: block; margin-bottom: 5px;">{{ translate('Product')}}</label>
                <div style="background: #f8f9fa; padding: 10px 15px; border-radius: 8px; border-left: 3px solid #fa3e00;">
                    <p style="margin: 0; color: #333; font-weight: 500;">{{ $product->getTranslation('name') }}</p>
                </div>
            </div>
            <!-- Rating -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="opacity-60" style="color: #555; font-weight: 500; display: block; margin-bottom: 10px;">{{ translate('Rating')}}</label>
                <div class="rating rating-input" style="display: flex; gap: 5px;">
                    <label>
                        <input type="radio" name="rating" value="1" required>
                        <i class="las la-star"></i>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="2">
                        <i class="las la-star"></i>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="3">
                        <i class="las la-star"></i>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="4">
                        <i class="las la-star"></i>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="5">
                        <i class="las la-star"></i>
                    </label>
                </div>
            </div>
            <!-- Comment -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="opacity-60" tyle="color: #555; font-weight: 500; display: block; margin-bottom: 5px;">{{ translate('Comment')}}</label>
                <textarea class="form-control rounded-0" rows="4" name="comment" placeholder="{{ translate('Your review')}}" required 
                    style="border-radius: 8px; border: 1px solid #ddd; padding: 10px; transition: all 0.3s; resize: none;"></textarea>
            </div>
            <!-- Review Images -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="" for="photos" style="color: #555; font-weight: 500; display: block; margin-bottom: 5px;">{{translate('Review Images')}}</label>
                <div class="">
                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true"
                        style="border: 2px dashed #ddd; border-radius: 8px; overflow: hidden; transition: all 0.3s;">
                        <div class="input-group-prepend">
                            <div class="input-group-text" style="background: linear-gradient(135deg, #fa3e00 0%, #ff6b3d 100%); color: white; border: none; padding: 10px 20px; font-weight: 500;">
                                {{ translate('Browse')}}
                            </div>
                        </div>
                        <div class="form-control file-amount" style="border: none; color: #666; padding: 10px;">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="photos[]" class="selected-files">
                    </div>
                    <div class="file-preview box sm" style="margin-top: 10px;">
                    </div>
                    <small class="text-muted" style="color: #888 !important; font-size: 12px; display: block; margin-top: 5px;">
                        {{translate('These images are visible in product review page gallery. Upload square images')}}
                    </small>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 20px; background: #fafafa;">
            <button type="button" class="btn btn-sm" data-dismiss="modal" 
                style="background: white; color: #666; border: 1px solid #ddd; border-radius: 6px; padding: 8px 20px; font-weight: 500; transition: all 0.3s;">
                {{translate('Cancel')}}
            </button>
            <button type="submit" class="btn btn-sm" 
                style="background: linear-gradient(135deg, #fa3e00 0%, #ff6b3d 100%); color: white; border: none; border-radius: 6px; padding: 8px 20px; font-weight: 500; transition: all 0.3s;">
                {{translate('Submit Review')}}
            </button>
        </div>
    </form>
@else
    <div class="modal-body" style="padding: 20px;">
        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <!-- Rating -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="opacity-60" style="color: #555; font-weight: 500; display: block; margin-bottom: 10px;">{{ translate('Rating')}}</label>
                <p class="rating rating-sm" style="display: flex; align-items: center; gap: 5px;">
                    @for ($i=0; $i < $review->rating; $i++)
                        <i class="las la-star active" style="color: #fa3e00; font-size: 24px;"></i>
                    @endfor
                    @for ($i=0; $i < 5-$review->rating; $i++)
                        <i class="las la-star" style="color: #ddd; font-size: 24px;"></i>
                    @endfor
                    <span style="margin-left: 10px; color: #fa3e00; font-weight: 600; background: rgba(250, 62, 0, 0.1); padding: 4px 12px; border-radius: 20px; font-size: 14px;">
                        {{ $review->rating }}/5
                    </span>
                </p>
            </div>
            
            <!-- Comment -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="opacity-60" style="color: #555; font-weight: 500; display: block; margin-bottom: 5px;">{{ translate('Comment')}}</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 3px solid #fa3e00;">
                    <p class="comment-text" style="margin: 0; color: #444; line-height: 1.6;">
                        {{ $review->comment }}
                    </p>
                </div>
            </div>
            
            <!-- Review Images -->
            @if($review->photos != null)
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="opacity-60" style="color: #555; font-weight: 500; display: block; margin-bottom: 10px;">{{ translate('Images')}}</label>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        @foreach (explode(',', $review->photos) as $photo)
                            <div style="width: 90px; height: 90px; overflow: hidden; border-radius: 8px; border: 1px solid #e0e0e0;">
                                <img class="img-fit h-100 lazyload"
                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ uploaded_asset($photo) }}"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; color: #888; font-size: 14px;">
                <i class="las la-clock" style="color: #fa3e00; margin-right: 5px;"></i>
                {{ \Carbon\Carbon::parse($review->created_at)->format('F j, Y') }}
            </div>
        </div>
    </div>
@endif

