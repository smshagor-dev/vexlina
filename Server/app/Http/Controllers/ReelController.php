<?php

namespace App\Http\Controllers;

use App\Models\ReelComment;
use App\Models\ReelLike;
use App\Models\ReelPost;
use App\Models\ReelSave;
use App\Models\ReelShare;
use App\Models\ReelView;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReelController extends Controller
{
    public function index()
    {
        $reels = ReelPost::with(['user', 'product', 'video', 'thumbnail'])
            ->where('status', 'published')
            ->latest()
            ->paginate(12);

        return view('frontend.reels.index', [
            'reels' => $reels,
            'canPost' => auth()->check() ? $this->canPost(auth()->user()) : false,
        ]);
    }

    public function show($id)
    {
        $reel = ReelPost::with(['user', 'product', 'video', 'thumbnail', 'comments.user', 'comments.replies.user'])
            ->where('status', 'published')
            ->findOrFail($id);

        $this->recordWebView($reel, request());

        return view('frontend.reels.show', [
            'reel' => $reel->fresh(['user', 'product', 'video', 'thumbnail', 'comments.user', 'comments.replies.user']),
            'canPost' => auth()->check() ? $this->canPost(auth()->user()) : false,
        ]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        $reels = ReelPost::with(['product', 'video', 'thumbnail'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->latest()
            ->paginate(12);

        return view('frontend.reels.dashboard', [
            'reels' => $reels,
            'canPost' => $this->canPost($user),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        return view('frontend.reels.studio', [
            'canPost' => $this->canPost($user),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$this->canPost($user)) {
            flash(translate('Only sellers or classified subscribers can post reels.'))->error();
            return back();
        }

        $request->validate([
            'caption' => 'nullable|string|max:2000',
            'product_id' => 'nullable|integer|exists:products,id',
            'allow_comments' => 'nullable|boolean',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm|max:51200',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $videoUpload = $this->storeUploadFromRequest($request->file('video'), $user, 'video');
        $thumbnailUpload = $request->hasFile('thumbnail')
            ? $this->storeUploadFromRequest($request->file('thumbnail'), $user, 'image')
            : null;

        ReelPost::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'video_upload_id' => $videoUpload->id,
            'thumbnail_upload_id' => optional($thumbnailUpload)->id,
            'caption' => $request->caption,
            'duration_seconds' => (int) $request->get('duration_seconds', 0),
            'allow_comments' => $request->boolean('allow_comments', true),
            'status' => 'published',
        ]);

        flash(translate('Reel posted successfully.'))->success();
        return redirect()->route('reels.dashboard');
    }

    public function destroy($id)
    {
        $reel = ReelPost::where('user_id', auth()->id())->findOrFail($id);
        $reel->status = 'deleted';
        $reel->save();

        flash(translate('Reel removed successfully.'))->success();
        return back();
    }

    public function toggleLike($id)
    {
        $reel = ReelPost::findOrFail($id);
        $existing = ReelLike::where('reel_post_id', $reel->id)->where('user_id', auth()->id())->first();

        if ($existing) {
            $existing->delete();
        } else {
            ReelLike::create([
                'reel_post_id' => $reel->id,
                'user_id' => auth()->id(),
            ]);
        }

        $this->syncCounts($reel);
        return back();
    }

    public function toggleSave($id)
    {
        $reel = ReelPost::findOrFail($id);
        $existing = ReelSave::where('reel_post_id', $reel->id)->where('user_id', auth()->id())->first();

        if ($existing) {
            $existing->delete();
        } else {
            ReelSave::create([
                'reel_post_id' => $reel->id,
                'user_id' => auth()->id(),
            ]);
        }

        $this->syncCounts($reel);
        return back();
    }

    public function storeComment(Request $request, $id)
    {
        $reel = ReelPost::findOrFail($id);
        if (!$reel->allow_comments) {
            flash(translate('Comments are disabled for this reel.'))->error();
            return back();
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:reels_comments,id',
        ]);

        ReelComment::create([
            'reel_post_id' => $reel->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
            'status' => 'published',
        ]);

        $this->syncCounts($reel);
        flash(translate('Comment added successfully.'))->success();
        return back();
    }

    public function share(Request $request, $id)
    {
        $reel = ReelPost::findOrFail($id);

        ReelShare::create([
            'reel_post_id' => $reel->id,
            'user_id' => auth()->id(),
            'platform' => $request->get('platform', 'web'),
        ]);

        $this->syncCounts($reel);

        return redirect()->away(route('reels.show', $reel->id));
    }

    protected function canPost($user)
    {
        if ($user->user_type === 'seller') {
            return true;
        }

        return !empty($user->customer_package_id) && (int) $user->remaining_uploads > 0;
    }

    protected function recordWebView(ReelPost $reel, Request $request)
    {
        $query = ReelView::where('reel_post_id', $reel->id);
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('ip_address', $request->ip());
        }

        if (!$query->where('created_at', '>=', now()->subHours(12))->exists()) {
            ReelView::create([
                'reel_post_id' => $reel->id,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
            $this->syncCounts($reel);
        }
    }

    protected function syncCounts(ReelPost $reel)
    {
        $reel->likes_count = ReelLike::where('reel_post_id', $reel->id)->count();
        $reel->comments_count = ReelComment::where('reel_post_id', $reel->id)->where('status', 'published')->count();
        $reel->views_count = ReelView::where('reel_post_id', $reel->id)->count();
        $reel->shares_count = ReelShare::where('reel_post_id', $reel->id)->count();
        $reel->saves_count = ReelSave::where('reel_post_id', $reel->id)->count();
        $reel->save();
    }

    protected function storeUploadFromRequest($file, $user, $expectedType)
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
