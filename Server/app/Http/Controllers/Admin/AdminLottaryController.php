<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lottary;
use Carbon\Carbon;
use App\Models\LottaryPrize;
use App\Models\LottaryTicket;
use App\Models\FakePeople;
use App\Models\LottaryWinner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminLottaryController extends Controller
{
    public function index()
    {
        $lottaries = Lottary::with([
                'prizes:id,lottary_id,name,prize_value,winner_number,drew_type,description,photo'
            ])
            ->withCount('prizes')
            ->latest()
            ->paginate(15);
    
        return view('admin.lottary.index', compact('lottaries'));
    }
    
    public function create()
    {
        return view('admin.lottary.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price'         => 'required|numeric',
            'prize_number'  => 'required|integer',
            'winner_number' => 'required|integer',
            'start_date'    => 'required|date',
            'drew_date'     => 'required|date',
    
            'prize_name.*'           => 'required|string|max:255',
            'prize_value.*'          => 'required|string|max:255',
            'prize_winner_number.*'  => 'nullable|integer',
            'prize_drew_type.*' => 'nullable|in:Real,Fake',
            'prize_description.*'    => 'nullable|string',
            'prize_photo.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        DB::transaction(function () use ($request) {
            $photoPath = null;
            
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . rand(1000,9999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/all'), $filename);
                $photoPath = 'public/uploads/all/' . $filename;
            }
            
            $startDate = Carbon::parse($request->start_date);
            $now       = Carbon::now();

            $isActive = $startDate->lessThanOrEqualTo($now) ? 1 : 0;
    
            $lottary = Lottary::create([
                'title'         => $request->title,
                'description'   => $request->description,
                'photo'         => $photoPath,
                'price'         => $request->price,
                'prize_number'  => $request->prize_number,
                'winner_number' => $request->winner_number,
                'start_date'    => $request->start_date,
                'drew_date'     => $request->drew_date,
                'is_drew'       => 0,
                'is_active'     => $isActive,
            ]);
    
            foreach ($request->prize_name as $key => $name) {
                $prizePhotoPath = null;
    
                if ($request->hasFile('prize_photo') && isset($request->file('prize_photo')[$key])) {
                    $file = $request->file('prize_photo')[$key];
                    $filename = time() . rand(1000, 9999) . '_prize_' . $key . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/all'), $filename);
                    $prizePhotoPath = 'public/uploads/all/' . $filename;
                }
            
                LottaryPrize::create([
                    'lottary_id'    => $lottary->id,
                    'name'          => $name,
                    'prize_value'   => $request->prize_value[$key],
                    'winner_number' => $request->prize_winner_number[$key] ?? null,
                    'drew_type'     => $request->prize_drew_type[$key] ?? null,
                    'description'   => $request->prize_description[$key] ?? null,
                    'photo'         => $prizePhotoPath,
                ]);
            }
        });
    
        return redirect()->route('admin.lottary.index')
            ->with('success', 'Lottery created successfully');
    }
    
    public function edit($id)
    {
        $lottary = Lottary::with('prizes')->findOrFail($id);
        return view('admin.lottary.edit', compact('lottary'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price'         => 'required|numeric',
            'prize_number'  => 'required|integer',
            'winner_number' => 'required|integer',
            'start_date'    => 'required|date',
            'drew_date'     => 'required|date',
            'is_active'     => 'required|in:0,1',
    
            'prize_name.*'           => 'required|string|max:255',
            'prize_value.*'          => 'required|string|max:255',
            'prize_winner_number.*'  => 'nullable|integer',
            'prize_drew_type.*' => 'nullable|in:Real,Fake',
            'prize_description.*'    => 'nullable|string',
            'prize_photo.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        DB::transaction(function () use ($request, $id) {
            $lottary = Lottary::findOrFail($id);

            if ($request->hasFile('photo')) {
                if ($lottary->photo && file_exists(public_path($lottary->photo))) {
                    unlink(public_path($lottary->photo));
                }
            
                $file = $request->file('photo');
                $filename = time() . rand(1000,9999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/all'), $filename);
                $lottary->photo = 'public/uploads/all/' . $filename;
            }

            $lottary->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'prize_number'  => $request->prize_number,
                'winner_number' => $request->winner_number,
                'start_date'    => $request->start_date,
                'drew_date'     => $request->drew_date,
                'is_active'     => $request->is_active,
            ]);

            $oldPrizes = $lottary->prizes;

            $lottary->prizes()->delete();

            foreach ($request->prize_name as $key => $name) {
                $photoPath = null;

                if ($request->hasFile('prize_photo') && isset($request->file('prize_photo')[$key])) {
                    $file = $request->file('prize_photo')[$key];
                    $filename = time() . rand(1000,9999) . '_prize_' . $key . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/all'), $filename);
                    $photoPath = 'public/uploads/all/' . $filename;
                } 

                elseif (isset($oldPrizes[$key]) && $oldPrizes[$key]->photo) {
                    $photoPath = $oldPrizes[$key]->photo;
                }
    
                LottaryPrize::create([
                    'lottary_id'    => $lottary->id,
                    'name'          => $name,
                    'prize_value'   => $request->prize_value[$key],
                    'winner_number' => $request->prize_winner_number[$key] ?? null,
                    'drew_type'     => $request->prize_drew_type[$key] ?? null,
                    'description'   => $request->prize_description[$key] ?? null, 
                    'photo'         => $photoPath,
                ]);
            }
        });
    
        return redirect()->route('admin.lottary.index')
            ->with('success', 'Lottery updated successfully');
    }

    
    public function delete($id)
    {
        $lottary = Lottary::findOrFail($id);

        if ($lottary->photo) {
            Storage::disk('public')->delete($lottary->photo);
        }

        $lottary->delete();

        return redirect()->back()
            ->with('success', 'Lottery deleted successfully');
    }
    
    public function getPrizes($lottaryId)
    {
        $prizes = LottaryPrize::where('lottary_id', $lottaryId)
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'lottary_id',
                'name',
                'prize_value',
                'winner_number',
                'drew_type',
                'description',
                'photo',
                'created_at',
                'updated_at',
            ]);
    
        return response()->json([
            'status' => true,
            'total'  => $prizes->count(),
            'data'   => $prizes->map(function ($prize) {
                return [
                    'id'            => $prize->id,
                    'lottary_id'    => $prize->lottary_id,
                    'name'          => $prize->name,
                    'prize_value'   => $prize->prize_value,
                    'winner_number' => $prize->winner_number,
                    'drew_type'     => $prize->drew_type,
                    'description'   => $prize->description,
                    'photo'         => $prize->photo,
                    'photo_url' => $prize->photo
                        ? url($prize->photo)
                        : null,
                    'created_at'    => $prize->created_at,
                    'updated_at'    => $prize->updated_at,
                ];
            }),
        ]);
    }
    
    public function viewTicketsByLottary($lottaryId)
    {
        $tickets = LottaryTicket::with(['user', 'lottary'])
        ->where('lottary_id', $lottaryId)
        ->orderBy('created_at', 'desc')
        ->get();
        
        $lottaryTitle = $tickets->first()?->lottary->title ?? 'Lottery Tickets';
        
        return view('admin.lottary.tickets', compact('tickets', 'lottaryTitle'));
    }
    
    // Lottary Drew
    
    public function drew()
    {
        $lottaries = Lottary::where('drew_date', '<=', now())
            ->where('is_drew', 0)
            ->get();

        foreach ($lottaries as $lottary) {

            DB::transaction(function() use ($lottary) {

                $prizes = LottaryPrize::where('lottary_id', $lottary->id)->get();

                foreach ($prizes as $prize) {

                    $winner_count = $prize->winner_number;
                    $users = [];

                    if ($prize->drew_type === 'Real') {
                        $users = LottaryTicket::where('lottary_id', $lottary->id)
                            ->inRandomOrder()
                            ->take($winner_count)
                            ->get();
                    } else {
                        $users = FakePeople::inRandomOrder()
                            ->take($winner_count)
                            ->get();
                    }

                    foreach ($users as $user) {

                        if ($prize->drew_type === 'Real') {
                            LottaryWinner::create([
                                'lottary_id'       => $lottary->id,
                                'lottary_prize_id' => $prize->id,
                                'user_type'        => 'Real',
                                'user_id'          => $user->user_id,
                                'fake_people_id'   => null,
                                'lottary_tickets_id' => $user->id,
                                'ticket_number'    => $user->ticket_number,
                            ]);
                        } else {
                            $fake_ticket = strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

                            while (LottaryTicket::where('ticket_number', $fake_ticket)->exists()) {
                                $fake_ticket = strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
                            }

                            LottaryWinner::create([
                                'lottary_id'       => $lottary->id,
                                'lottary_prize_id' => $prize->id,
                                'user_type'        => 'Fake',
                                'user_id'          => null,
                                'fake_people_id'   => $user->id,
                                'lottary_tickets_id' => null,
                                'ticket_number'    => $fake_ticket,
                            ]);
                        }
                    }
                }

                $lottary->is_drew = 1;
                $lottary->is_active = 0;
                $lottary->save();

            });
        }

        return "Lottery Drew Completed!";
    }
    
    public function getWinner($lottaryId)
    {
        $winners = LottaryWinner::where('lottary_id', $lottaryId)
            ->with(['prize', 'user', 'fakePerson'])
            ->get();
    
        if ($winners->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No winners found for this lottery'
            ]);
        }
    
        $data = $winners->map(function ($winner) {
            // User data
            $userData = null;
            if ($winner->user_type === 'Real' && $winner->user) {
                $userData = [
                    'name'    => $winner->user->name,
                    'address' => $winner->user->address,
                    'city'    => $winner->user->city,
                    'state'   => $winner->user->state,
                    'country' => $winner->user->country,
                ];
            }
    
            // Fake person data
            $fakeData = null;
            if ($winner->user_type === 'Fake' && $winner->fakePerson) {
                $fakeData = [
                    'full_name' => $winner->fakePerson->full_name,
                    'upazila'   => $winner->fakePerson->upazila,
                    'district'  => $winner->fakePerson->district,
                    'country'   => $winner->fakePerson->country,
                ];
            }
    
            // Prize data
            $prizeData = null;
            if ($winner->prize) {
                $prizeData = [
                    'prize_value'   => $winner->prize->prize_value,
                    'winner_number' => $winner->prize->winner_number,
                    'photo'         => $winner->prize->photo ? url($winner->prize->photo) : null,
                ];
            }
    
            return [
                'id'                 => $winner->id,
                'user_id'            => $winner->user_id,
                'fake_people_id'     => $winner->fake_people_id,
                'user_type'          => $winner->user_type,
                'lottary_id'         => $winner->lottary_id,
                'lottary_prize_id'   => $winner->lottary_prize_id,
                'lottary_tickets_id' => $winner->lottary_tickets_id,
                'ticket_number'      => $winner->ticket_number,
                'user'               => $userData,
                'fake_person'        => $fakeData,
                'prize'              => $prizeData,
            ];
        });
    
        return response()->json([
            'success' => true,
            'total_winners' => $data->count(),
            'data' => $data
        ]);
    }
    
    
    public function claimPrizeRequest(Request $request)
    {
        $query = LottaryWinner::with([
            'user:id,name',
            'lottary:id,title',
            'prize:id,name'
        ])->where('claim_request', 1);

        if ($request->has('send_gift') && in_array($request->send_gift, [0, 1])) {
            $query->where('send_gift', $request->send_gift);
        }

        if ($request->filled('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('claim_code', 'like', "%{$search}%");
            });
        }

        $query->orderByRaw('send_gift ASC')->orderBy('id', 'desc');
    
        $claims = $query->paginate(15);

        $totalCount = LottaryWinner::where('claim_request', 1)->count();
        $pendingCount = LottaryWinner::where('claim_request', 1)->where('send_gift', 0)->count();
        $sentCount = LottaryWinner::where('claim_request', 1)->where('send_gift', 1)->count();
    
        $data = $claims->map(function ($item) {
            return [
                'id'                    => $item->id,
                'user_name'             => $item->user?->name,
                'lottary_title'         => $item->lottary?->title,
                'prize_name'            => $item->prize?->name,
                'ticket_number'         => $item->ticket_number,
                'mobile'                => $item->Mobile,
                'claim_code'            => $item->claim_code,
                'claim_request'         => $item->claim_request,
                'send_gift'             => $item->send_gift,
                'claim_request_address' => $item->claim_request_address,
                'draw_date'             => $item->created_at,
                'request_date'          => $item->updated_at,
            ];
        });
    
        return view('admin.lottary.claim_request', [
            'claims'       => $claims,
            'data'         => $data,
            'totalCount'   => $totalCount,
            'pendingCount' => $pendingCount,
            'sentCount'    => $sentCount,
        ]);
    }

    
    public function viewClaimDetails($id)
    {
        $claim = LottaryWinner::with([
            'user:id,name,email',
            'lottary:id,title,created_at',
            'prize:id,name'
        ])->where('claim_request', 1)->findOrFail($id);
    
        return view('admin.lottary.claim_details', compact('claim'));
    }

    public function sendGift(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:lottary_winners,id',
        ]);
    
        $winner = LottaryWinner::where('id', $request->id)
            ->where('claim_request', 1)
            ->first();
    
        if (!$winner) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid claim request'
            ], 404);
        }
    
        if ($winner->send_gift == 1) {
            return response()->json([
                'status' => false,
                'message' => 'Gift already sent'
            ], 409);
        }
    
        $winner->update([
            'send_gift' => 1
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Gift marked as sent successfully',
            'data' => [
                'id' => $winner->id,
                'send_gift' => 1
            ]
        ], 200);
    }


}
