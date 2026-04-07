<?php

namespace App\Http\Controllers;

use App\Models\ReelPost;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class AdminReelController extends Controller
{
    public function __construct()
    {
        Permission::findOrCreate('view_reels');
        Permission::findOrCreate('delete_reels');

        $this->middleware(['permission:view_reels'])->only('index');
        $this->middleware(['permission:delete_reels'])->only('destroy');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $reels = ReelPost::with(['user', 'product', 'thumbnail'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('caption', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        return view('backend.reels.index', compact('reels', 'search'));
    }

    public function destroy($id)
    {
        $reel = ReelPost::findOrFail($id);
        $reel->status = 'deleted';
        $reel->save();

        flash(translate('Reel removed successfully'))->success();
        return back();
    }
}
