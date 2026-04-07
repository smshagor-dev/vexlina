<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ReelComment;
use App\Models\ReelLike;
use App\Models\ReelPost;
use App\Models\ReelSave;
use App\Models\ReelShare;
use App\Models\ReelView;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReelController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        $reels = ReelPost::with(['user', 'product', 'video', 'thumbnail'])
            ->where('status', 'published')
            ->latest()
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'result' => true,
            'message' => 'Reels fetched successfully.',
            'data' => collect($reels->items())->map(function ($reel) use ($user) {
                return $this->formatReel($reel, $user);
            })->values(),
            'meta' => [
                'current_page' => $reels->currentPage(),
                'last_page' => $reels->lastPage(),
                'total' => $reels->total(),
                'can_post' => $user ? $this->canPost($user) : false,
            ],
        ]);
    }

    public function show($id)
    {
        $user = auth('sanctum')->user();
        $reel = ReelPost::with(['user', 'product', 'video', 'thumbnail'])
            ->where('status', 'published')
            ->findOrFail($id);

        return response()->json([
            'result' => true,
            'data' => $this->formatReel($reel, $user),
        ]);
    }

    public function myPermissions()
    {
        $user = auth()->user();

        return response()->json([
            'result' => true,
            'data' => [
                'can_post' => $this->canPost($user),
                'is_seller' => $user->user_type === 'seller',
                'classified_package_id' => $user->customer_package_id,
                'remaining_uploads' => (int) $user->remaining_uploads,
            ],
        ]);
    }

    public function myPosts()
    {
        $user = auth()->user();
        $reels = ReelPost::with(['user', 'product', 'video', 'thumbnail'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->latest()
            ->paginate(20);

        return response()->json([
            'result' => true,
            'data' => collect($reels->items())->map(function ($reel) use ($user) {
                return $this->formatReel($reel, $user);
            })->values(),
            'meta' => [
                'current_page' => $reels->currentPage(),
                'last_page' => $reels->lastPage(),
                'total' => $reels->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$this->canPost($user)) {
            return response()->json([
                'result' => false,
                'message' => 'Only sellers or classified subscribers can post reels.',
            ], 403);
        }

        $request->validate([
            'caption' => 'nullable|string|max:2000',
            'product_id' => 'nullable|integer|exists:products,id',
            'allow_comments' => 'nullable|boolean',
            'duration_seconds' => 'nullable|integer|max:30',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm|max:51200',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $videoUpload = $this->storeUploadFromRequest($request->file('video'), $user, 'video');
        $thumbnailUpload = $request->hasFile('thumbnail')
            ? $this->storeUploadFromRequest($request->file('thumbnail'), $user, 'image')
            : null;

        $reel = ReelPost::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'video_upload_id' => $videoUpload->id,
            'thumbnail_upload_id' => optional($thumbnailUpload)->id,
            'caption' => $request->caption,
            'duration_seconds' => (int) $request->get('duration_seconds', 0),
            'allow_comments' => $request->boolean('allow_comments', true),
            'status' => 'published',
        ]);

        $reel->load(['user', 'product', 'video', 'thumbnail']);

        return response()->json([
            'result' => true,
            'message' => 'Reel posted successfully.',
            'data' => $this->formatReel($reel, $user),
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $reel = ReelPost::where('user_id', $user->id)->findOrFail($id);
        $reel->status = 'deleted';
        $reel->save();

        return response()->json([
            'result' => true,
            'message' => 'Reel removed successfully.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $reel = ReelPost::with(['user', 'product', 'video', 'thumbnail'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->findOrFail($id);

        $validated = $request->validate([
            'caption' => 'nullable|string|max:2000',
            'product_id' => 'nullable|integer|exists:products,id',
            'allow_comments' => 'nullable|boolean',
        ]);

        $reel->caption = $validated['caption'] ?? null;
        $reel->product_id = $validated['product_id'] ?? null;
        if (array_key_exists('allow_comments', $validated)) {
            $reel->allow_comments = $request->boolean('allow_comments');
        }
        $reel->save();
        $reel->load(['user', 'product', 'video', 'thumbnail']);

        return response()->json([
            'result' => true,
            'message' => 'Reel updated successfully.',
            'data' => $this->formatReel($reel, $user),
        ]);
    }

    public function toggleLike($id)
    {
        $user = auth()->user();
        $reel = ReelPost::findOrFail($id);
        $existing = ReelLike::where('reel_post_id', $reel->id)->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            ReelLike::create([
                'reel_post_id' => $reel->id,
                'user_id' => $user->id,
            ]);
            $liked = true;
        }

        $this->syncCounts($reel);

        return response()->json([
            'result' => true,
            'message' => $liked ? 'Reel liked.' : 'Like removed.',
            'data' => [
                'liked' => $liked,
                'likes_count' => $reel->fresh()->likes_count,
            ],
        ]);
    }

    public function toggleSave($id)
    {
        $user = auth()->user();
        $reel = ReelPost::findOrFail($id);
        $existing = ReelSave::where('reel_post_id', $reel->id)->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            ReelSave::create([
                'reel_post_id' => $reel->id,
                'user_id' => $user->id,
            ]);
            $saved = true;
        }

        $this->syncCounts($reel);

        return response()->json([
            'result' => true,
            'message' => $saved ? 'Reel saved.' : 'Saved reel removed.',
            'data' => [
                'saved' => $saved,
                'saves_count' => $reel->fresh()->saves_count,
            ],
        ]);
    }

    public function recordView(Request $request, $id)
    {
        $reel = ReelPost::findOrFail($id);
        $user = auth('sanctum')->user();
        $deviceKey = $request->get('device_key');

        $query = ReelView::where('reel_post_id', $reel->id);
        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($deviceKey) {
            $query->where('device_key', $deviceKey);
        } else {
            $query->where('ip_address', $request->ip());
        }

        if (!$query->where('created_at', '>=', now()->subHours(12))->exists()) {
            ReelView::create([
                'reel_post_id' => $reel->id,
                'user_id' => optional($user)->id,
                'device_key' => $deviceKey,
                'ip_address' => $request->ip(),
            ]);
            $this->syncCounts($reel);
        }

        return response()->json([
            'result' => true,
            'data' => [
                'views_count' => $reel->fresh()->views_count,
            ],
        ]);
    }

    public function recordShare(Request $request, $id)
    {
        $reel = ReelPost::findOrFail($id);

        ReelShare::create([
            'reel_post_id' => $reel->id,
            'user_id' => optional(auth('sanctum')->user())->id,
            'platform' => $request->get('platform'),
        ]);

        $this->syncCounts($reel);

        return response()->json([
            'result' => true,
            'message' => 'Share recorded.',
            'data' => [
                'shares_count' => $reel->fresh()->shares_count,
            ],
        ]);
    }

    public function comments($id)
    {
        $comments = ReelComment::with(['user', 'replies.user'])
            ->where('reel_post_id', $id)
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->latest()
            ->get();

        return response()->json([
            'result' => true,
            'data' => $comments->map(function ($comment) {
                return $this->formatComment($comment);
            })->values(),
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $user = auth()->user();
        $reel = ReelPost::findOrFail($id);

        if (!$reel->allow_comments) {
            return response()->json([
                'result' => false,
                'message' => 'Comments are disabled for this reel.',
            ], 422);
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:reels_comments,id',
        ]);

        $comment = ReelComment::create([
            'reel_post_id' => $reel->id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
            'status' => 'published',
        ]);

        $this->syncCounts($reel);

        return response()->json([
            'result' => true,
            'message' => 'Comment added successfully.',
            'data' => $this->formatComment($comment->load('user')),
        ]);
    }

    protected function canPost(User $user)
    {
        if ($user->user_type === 'seller') {
            return true;
        }

        return !empty($user->customer_package_id) && (int) $user->remaining_uploads > 0;
    }

    protected function syncCounts(ReelPost $reel)
    {
        $reel->likes_count = ReelLike::where('reel_post_id', $reel->id)->count();
        $reel->comments_count = ReelComment::where('reel_post_id', $reel->id)
            ->where('status', 'published')
            ->count();
        $reel->views_count = ReelView::where('reel_post_id', $reel->id)->count();
        $reel->shares_count = ReelShare::where('reel_post_id', $reel->id)->count();
        $reel->saves_count = ReelSave::where('reel_post_id', $reel->id)->count();
        $reel->save();
    }

    protected function formatReel(ReelPost $reel, $user = null)
    {
        $product = $reel->product;

        return [
            'id' => $reel->id,
            'caption' => $reel->caption,
            'video_url' => uploaded_asset($reel->video_upload_id),
            'thumbnail_url' => $reel->thumbnail_upload_id ? uploaded_asset($reel->thumbnail_upload_id) : null,
            'duration_seconds' => (int) $reel->duration_seconds,
            'allow_comments' => (bool) $reel->allow_comments,
            'views_count' => (int) $reel->views_count,
            'likes_count' => (int) $reel->likes_count,
            'comments_count' => (int) $reel->comments_count,
            'shares_count' => (int) $reel->shares_count,
            'saves_count' => (int) $reel->saves_count,
            'is_liked' => $user ? ReelLike::where('reel_post_id', $reel->id)->where('user_id', $user->id)->exists() : false,
            'is_saved' => $user ? ReelSave::where('reel_post_id', $reel->id)->where('user_id', $user->id)->exists() : false,
            'can_edit' => $user ? (int) $user->id === (int) $reel->user_id : false,
            'user' => [
                'id' => $reel->user ? $reel->user->id : null,
                'name' => $reel->user ? $reel->user->name : '',
                'avatar' => $reel->user && $reel->user->avatar_original ? uploaded_asset($reel->user->avatar_original) : null,
                'user_type' => $reel->user ? $reel->user->user_type : '',
            ],
            'product' => $product ? [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'thumbnail_image' => uploaded_asset($product->thumbnail_img),
                'price' => home_discounted_base_price($product),
                'main_price' => home_discounted_base_price($product),
                'stroked_price' => home_base_price($product),
                'has_discount' => home_base_price($product, false) != home_discounted_base_price($product, false),
                'discount' => "-" . discount_in_percentage($product) . "%",
                'link' => route('product', $product->slug),
            ] : null,
            'link' => route('reels.show', $reel->id),
            'created_at' => optional($reel->created_at)->toDateTimeString(),
        ];
    }

    protected function formatComment(ReelComment $comment)
    {
        return [
            'id' => $comment->id,
            'comment' => $comment->comment,
            'created_at' => optional($comment->created_at)->diffForHumans(),
            'user' => [
                'id' => $comment->user ? $comment->user->id : null,
                'name' => $comment->user ? $comment->user->name : '',
                'avatar' => $comment->user && $comment->user->avatar_original ? uploaded_asset($comment->user->avatar_original) : null,
            ],
            'replies' => $comment->relationLoaded('replies')
                ? $comment->replies
                    ->where('status', 'published')
                    ->map(function ($reply) {
                        return $this->formatComment($reply);
                    })->values()
                : [],
        ];
    }

    protected function storeUploadFromRequest($file, User $user, $expectedType)
    {
        $directory = public_path('uploads/reels');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = 'uploads/reels/' . Str::random(20) . time() . '.' . $extension;
        $file->move(public_path('uploads/reels'), basename($fileName));

        $upload = new Upload();
        $upload->file_original_name = $originalName;
        $upload->file_name = $fileName;
        $upload->user_id = $user->id;
        $upload->extension = $extension;
        $upload->type = $expectedType;
        $upload->file_size = filesize(public_path($fileName));
        $upload->save();

        return $upload;
    }
}
