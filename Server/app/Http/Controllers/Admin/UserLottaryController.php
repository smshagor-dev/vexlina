<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lottary;
use App\Models\LottaryTicket;
use App\Models\LottaryPrize;
use App\Models\LottaryWinner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class UserLottaryController extends Controller
{
    public function index()
    {
        return view('frontend.user.lottary.index');
    }

    
    public function tickets(Request $request)
    {
        $userId = Auth::id();
        $status = $request->get('status');
    
        $tickets = DB::table('lottary_tickets')
            ->join('lottaries', 'lottary_tickets.lottary_id', '=', 'lottaries.id')
            ->join('users', 'lottary_tickets.user_id', '=', 'users.id')
            ->leftJoin('lottary_winners', function ($join) use ($userId) {
                $join->on('lottary_winners.lottary_id', '=', 'lottary_tickets.lottary_id')
                     ->on('lottary_winners.user_id', '=', 'lottary_tickets.user_id')
                     ->on('lottary_winners.ticket_number', '=', 'lottary_tickets.ticket_number');
            })
            ->where('lottary_tickets.user_id', $userId)
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('lottaries.is_drew', (int) $status);
            })
            ->select(
                'lottary_tickets.ticket_number',
                'lottary_tickets.created_at as ticket_buy_date',
                'lottaries.title',
                'lottaries.description',
                'lottaries.price',
                'lottaries.drew_date',
                'lottaries.is_drew',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw("
                    TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                        NULLIF(users.address, ''),
                        NULLIF(users.city, ''),
                        NULLIF(users.state, ''),
                        NULLIF(users.country, ''),
                        NULLIF(users.postal_code, '')
                    )) as full_address
                "),
                DB::raw("IF(lottary_winners.id IS NOT NULL, 'win', 'lose') as win_status")
            )
            ->orderBy('lottary_tickets.created_at', 'desc')
            ->get();
    
        return response()->json([
            'success' => true,
            'total'   => $tickets->count(),
            'data'    => $tickets
        ]);
    }

    
    public function viewTicket($ticket_number)
    {
        $userId = Auth::id();
    
        $ticket = DB::table('lottary_tickets')
            ->join('lottaries', 'lottary_tickets.lottary_id', '=', 'lottaries.id')
            ->join('users', 'lottary_tickets.user_id', '=', 'users.id')
            ->leftJoin('lottary_winners', function ($join) use ($ticket_number, $userId) {
                $join->on('lottary_winners.lottary_id', '=', 'lottary_tickets.lottary_id')
                     ->on('lottary_winners.ticket_number', '=', 'lottary_tickets.ticket_number');
            })
            ->where('lottary_tickets.ticket_number', $ticket_number)
            ->where('lottary_tickets.user_id', $userId)
            ->select(
                'lottary_tickets.ticket_number',
                'lottary_tickets.created_at as ticket_buy_date',
                'lottary_tickets.user_id',
    
                'lottaries.title',
                'lottaries.description',
                'lottaries.price',
                'lottaries.drew_date',
                'lottaries.is_drew',
    
                'users.name',
                'users.email',
                'users.phone',
    
                DB::raw("
                    IF(
                        users.address IS NULL
                        AND users.city IS NULL
                        AND users.state IS NULL
                        AND users.country IS NULL
                        AND users.postal_code IS NULL,
                        'Address not provided',
                        TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                            users.address,
                            users.city,
                            users.state,
                            users.country,
                            users.postal_code
                        ))
                    ) as full_address
                "),
                // Check win status
                DB::raw("IF(lottary_winners.id IS NULL, 'lose', 'win') as win_status")
            )
            ->first();
    
        if (!$ticket) {
            abort(404, 'Ticket not found or you do not have access.');
        }
    
        return view('frontend.user.lottary.view_single_ticket', compact('ticket'));
    }

    
    public function viewTicketPublic($ticket_number)
    {
        $ticket = DB::table('lottary_tickets')
            ->join('lottaries', 'lottary_tickets.lottary_id', '=', 'lottaries.id')
            ->join('users', 'lottary_tickets.user_id', '=', 'users.id')
            ->where('lottary_tickets.ticket_number', $ticket_number)
            ->select(
                'lottary_tickets.ticket_number',
                'lottary_tickets.created_at as ticket_buy_date',
                'lottary_tickets.user_id',
    
                'lottaries.title',
                'lottaries.description',
                'lottaries.price',
                'lottaries.drew_date',
                'lottaries.is_drew',
    
                'users.name',
                'users.email',
                'users.phone',
                
                DB::raw("
                    IF(
                        users.address IS NULL
                        AND users.city IS NULL
                        AND users.state IS NULL
                        AND users.country IS NULL
                        AND users.postal_code IS NULL,
                        'Address not provided',
                        TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                            users.address,
                            users.city,
                            users.state,
                            users.country,
                            users.postal_code
                        ))
                    ) as full_address
                ")
            )
            ->first();
    
        if (!$ticket) {
            abort(404, 'Ticket not found.');
        }
    
        return view('frontend.user.lottary.view_ticket', compact('ticket'));
    }
    
    public function ViewLottery()
    {
        return view('frontend.user.lottary.view');
    }
    
    public function lotteryWithPrizes()
    {
        $lottaries = Lottary::where('is_drew', 0)
            ->orderBy('created_at', 'asc')
            ->get([
                'id',
                'title',
                'description',
                'photo',
                'price',
                'prize_number',
                'winner_number',
                'start_date',
                'drew_date',
                'is_drew',
                'is_active',
                'created_at'
            ]);
    
        if ($lottaries->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No lottery found'
            ]);
        }
    
        $data = $lottaries->map(function ($lottery, $index) {
    
            $prizes = LottaryPrize::where('lottary_id', $lottery->id)
                ->orderBy('created_at', 'asc') 
                ->get([
                    'id',
                    'name',
                    'description',
                    'prize_value',
                    'winner_number',
                    'photo',
                    'created_at'
                ]);
    
            return [
                'id'            => $lottery->id,
                'title'         => $lottery->title,
                'description'   => $lottery->description,
                'price'         => $lottery->price,
                'photo_url'     => $lottery->photo ? url($lottery->photo) : null,
                'start_date'     => $lottery->start_date,
                'drew_date'     => $lottery->drew_date,
                'is_drew'       => $lottery->is_drew,

                'type'          => $lottery->is_active ? 'current' : 'upcoming',
    
                'prizes'        => $prizes
            ];
        });
    
        return response()->json([
            'success' => true,
            'total_lottaries' => $data->count(),
            'data' => $data
        ]);
    }

    
    public function allDrawnLotteries()
    {
        $lottaries = Lottary::where('is_drew', 1)
            ->orderBy('drew_date', 'desc')
            ->get([
                'id',
                'title',
                'description',
                'drew_date', 
                'photo'
            ]);
    
        return view('frontend.user.lottary.drew', compact('lottaries'));
    }


    
    public function getLotteryWinner($lottaryId)
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
            // Initialize user_info array
            $userInfo = [
                'name' => 'Anonymous',
                'address' => 'N/A',
                'city' => 'N/A',
                'state' => 'N/A',
                'country' => 'N/A',
                'location' => 'N/A',
            ];
    
            // Populate user_info based on user_type
            if ($winner->user_type === 'Real' && $winner->user) {
                $userInfo['name'] = $winner->user->name ?? 'Anonymous';
                $userInfo['address'] = $winner->user->address ?? 'N/A';
                $userInfo['city'] = $winner->user->city ?? 'N/A';
                $userInfo['state'] = $winner->user->state ?? 'N/A';
                $userInfo['country'] = $winner->user->country ?? 'N/A';
                
                // Create a location string
                $locationParts = [];
                if (!empty($winner->user->city) && $winner->user->city !== 'N/A') {
                    $locationParts[] = $winner->user->city;
                }
                if (!empty($winner->user->state) && $winner->user->state !== 'N/A') {
                    $locationParts[] = $winner->user->state;
                }
                if (!empty($winner->user->country) && $winner->user->country !== 'N/A') {
                    $locationParts[] = $winner->user->country;
                }
                $userInfo['location'] = !empty($locationParts) ? implode(', ', $locationParts) : 'N/A';
            }
            
            if ($winner->user_type === 'Fake' && $winner->fakePerson) {
                $userInfo['name'] = $winner->fakePerson->full_name ?? 'Anonymous';
                $userInfo['address'] = 'N/A'; // Fake persons typically don't have detailed addresses
                $userInfo['city'] = $winner->fakePerson->upazila ?? 'N/A';
                $userInfo['state'] = $winner->fakePerson->district ?? 'N/A';
                $userInfo['country'] = $winner->fakePerson->country ?? 'N/A';
                
                // Create a location string
                $locationParts = [];
                if (!empty($winner->fakePerson->upazila) && $winner->fakePerson->upazila !== 'N/A') {
                    $locationParts[] = $winner->fakePerson->upazila;
                }
                if (!empty($winner->fakePerson->district) && $winner->fakePerson->district !== 'N/A') {
                    $locationParts[] = $winner->fakePerson->district;
                }
                if (!empty($winner->fakePerson->country) && $winner->fakePerson->country !== 'N/A') {
                    $locationParts[] = $winner->fakePerson->country;
                }
                $userInfo['location'] = !empty($locationParts) ? implode(', ', $locationParts) : 'N/A';
            }
    
            // Prize data
            $prizeData = null;
            if ($winner->prize) {
                $prizeData = [
                    'prize_value' => $winner->prize->prize_value ?? 'N/A',
                    'winner_number' => $winner->prize->winner_number ?? 1,
                    'photo' => $winner->prize->photo ? url($winner->prize->photo) : null,
                ];
            }
    
            return [
                'id' => $winner->id,
                'lottary_id' => $winner->lottary_id,
                'lottary_prize_id' => $winner->lottary_prize_id,
                'lottary_tickets_id' => $winner->lottary_tickets_id,
                'ticket_number' => $winner->ticket_number ?? 'N/A',
                'user_info' => $userInfo, // Merged user/fake person data
                'prize' => $prizeData,
            ];
        });
    
        return response()->json([
            'success' => true,
            'total_winners' => $data->count(),
            'data' => $data
        ]);
    }
    
    public function ViewMyLotteriesWin()
    {
    
        return view('frontend.user.lottary.my_drew');
    }
    
    public function getMyLotteryWins()
    {
        $userId = Auth::id();
        $cutoffDate = Carbon::now()->subDays(30);
    
        $wins = LottaryWinner::where('user_type', 'Real')
            ->where('user_id', $userId)
            ->with([
                'prize:id,prize_value,winner_number,photo',
                'lottary:id,title,drew_date'
            ])
            ->orderBy('id', 'desc')
            ->get();
    
        if ($wins->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'You have not won any lottery yet.'
            ]);
        }
    
        $data = $wins->map(function ($win) use ($cutoffDate) {
            $status = 'old';
            if ($win->lottary && $win->lottary->drew_date) {
                if (Carbon::parse($win->lottary->drew_date)->gte($cutoffDate)) {
                    $status = 'new';
                }
            }
    
            return [
                'winner_id'      => $win->id,
                'ticket_number'  => $win->ticket_number,
                'status'         => $status,
    
                'claim' => [
                    'claim_request' => $win->claim_request,
                    'claim_code'    => $win->claim_code,
                    'send_gift'     => $win->send_gift,
                    'mobile'        => $win->mobile,
                    'address'       => $win->claim_request_address,
                ],
    
                'lottary' => [
                    'id'        => $win->lottary->id ?? null,
                    'title'     => $win->lottary->title ?? null,
                    'drew_date' => $win->lottary->drew_date ?? null,
                ],
    
                'prize' => [
                    'prize_value'   => $win->prize->prize_value ?? null,
                    'winner_number' => $win->prize->winner_number ?? null,
                    'photo_url'     => $win->prize && $win->prize->photo
                        ? url($win->prize->photo)
                        : null,
                ],
    
                'lottary_tickets_id' => $win->lottary_tickets_id,
            ];
        });
    
        return response()->json([
            'success'    => true,
            'total_wins' => $data->count(),
            'data'       => $data
        ]);
    }

    
    public function claimLotteryPrize(Request $request, $winnerId)
    {
        $request->validate([
            'mobile'  => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);
    
        $userId = Auth::id();

        $winner = LottaryWinner::where('id', $winnerId)
            ->where('user_id', $userId)
            ->where('user_type', 'Real')
            ->first();
    
        if (!$winner) {
            return response()->json([
                'success' => false,
                'message' => 'Lottery win not found or not yours.'
            ]);
        }
    
        if ($winner->claim_request == 1) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a claim request.'
            ]);
        }

        $claimCode = $this->generateClaimCode();

        $winner->update([
            'claim_request'          => 1,
            'mobile'                 => $request->mobile,
            'claim_request_address'  => $request->address,
            'claim_code'             => $claimCode,
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Claim request submitted successfully.',
            'claim_code' => $claimCode,
        ]);
    }
    
    private function generateClaimCode()
    {
        do {
            $code = strtoupper(Str::random(4)) . '-' .
                    strtoupper(Str::random(4)) . '-' .
                    strtoupper(Str::random(4)) . '-' .
                    strtoupper(Str::random(4));
        } while (LottaryWinner::where('claim_code', $code)->exists());
    
        return $code;
    }
    
    public function SingleClaimLotteryPrize(Request $request)
    {
        $request->validate([
            'mobile'        => 'required|string|max:20',
            'address'       => 'required|string|max:500',
            'ticket_number' => 'required|string|max:50',
        ]);
    
        $userId = Auth::id();
        $ticketNumber = $request->ticket_number;

        $winner = LottaryWinner::where('user_id', $userId)
            ->where('ticket_number', $ticketNumber)
            ->where('user_type', 'Real')
            ->first();
    
        if (!$winner) {
            return response()->json([
                'success' => false,
                'message' => 'Lottery win not found or ticket does not belong to you.'
            ]);
        }
    
        if ($winner->claim_request == 1) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a claim request.'
            ]);
        }
    
        $claimCode = $this->generateClaimCode();
    
        $winner->update([
            'claim_request'          => 1,
            'mobile'                 => $request->mobile,
            'claim_request_address'  => $request->address,
            'claim_code'             => $claimCode,
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Claim request submitted successfully.',
            'claim_code' => $claimCode,
        ]);
    }



}
