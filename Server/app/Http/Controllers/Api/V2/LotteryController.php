<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Lottary;
use App\Models\LottaryTicket;
use App\Models\LottaryWinner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LotteryController extends Controller
{
    public function ping(Request $request): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        Log::info('Lottery ping route hit');

        return response()->json([
            'success' => true,
            'message' => 'Lottery ping ok',
        ]);
    }

    public function overview(Request $request): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        Log::info('Lottery overview endpoint hit');

        try {
            $lotteries = Lottary::with([
                'prizes' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                },
            ])
                ->where('is_drew', 0)
                ->orderByRaw('CASE WHEN is_active = 1 THEN 0 ELSE 1 END')
                ->orderBy('start_date', 'asc')
                ->get();

            Log::info('Lottery overview fetched records', [
                'count' => $lotteries->count(),
            ]);

            $currentLottery = $lotteries->firstWhere('is_active', true);
            $upcomingLotteries = $lotteries
                ->filter(fn ($lottery) => !$lottery->is_active)
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Lottery overview fetched successfully.',
                'data' => [
                    'current' => $currentLottery ? $this->formatLottery($currentLottery) : null,
                    'upcoming' => $upcomingLotteries
                        ->map(fn ($lottery) => $this->formatLottery($lottery))
                        ->values(),
                    'drawn_count' => Lottary::where('is_drew', 1)->count(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Lottery overview endpoint failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lottery overview failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function mySummary(Request $request): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        Log::info('Lottery my-summary endpoint hit', [
            'user_id' => auth()->id(),
        ]);

        try {
            $userId = auth()->id();

            $recentTickets = LottaryTicket::with('lottary:id,title,drew_date,is_drew')
                ->where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            Log::info('Lottery my-summary fetched records', [
                'user_id' => $userId,
                'recent_tickets_count' => $recentTickets->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lottery summary fetched successfully.',
                'data' => [
                    'total_tickets' => LottaryTicket::where('user_id', $userId)->count(),
                    'active_tickets' => LottaryTicket::where('user_id', $userId)
                        ->whereHas('lottary', function ($query) {
                            $query->where('is_drew', 0);
                        })->count(),
                    'total_wins' => LottaryWinner::where('user_id', $userId)
                        ->where('user_type', 'Real')
                        ->count(),
                    'pending_claims' => LottaryWinner::where('user_id', $userId)
                        ->where('user_type', 'Real')
                        ->where('claim_request', 0)
                        ->where('send_gift', 0)
                        ->count(),
                    'recent_tickets' => $recentTickets->map(function ($ticket) {
                        return [
                            'ticket_number' => $ticket->ticket_number,
                            'title' => $ticket->lottary->title ?? null,
                            'drew_date' => optional($ticket->lottary->drew_date)->toISOString(),
                            'status' => ($ticket->lottary && $ticket->lottary->is_drew) ? 'drawn' : 'active',
                        ];
                    })->values(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Lottery my-summary endpoint failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lottery summary failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function tickets(Request $request): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        try {
            $userId = auth()->id();
            $filter = $request->query('filter', 'all');

            $tickets = DB::table('lottary_tickets')
                ->join('lottaries', 'lottary_tickets.lottary_id', '=', 'lottaries.id')
                ->join('users', 'lottary_tickets.user_id', '=', 'users.id')
                ->leftJoin('lottary_winners', function ($join) {
                    $join->on('lottary_winners.lottary_id', '=', 'lottary_tickets.lottary_id')
                        ->on('lottary_winners.user_id', '=', 'lottary_tickets.user_id')
                        ->on('lottary_winners.ticket_number', '=', 'lottary_tickets.ticket_number');
                })
                ->where('lottary_tickets.user_id', $userId)
                ->when($filter === 'active', function ($query) {
                    $query->where('lottaries.is_drew', 0);
                })
                ->when($filter === 'completed', function ($query) {
                    $query->where('lottaries.is_drew', 1);
                })
                ->select(
                    'lottary_tickets.ticket_number',
                    'lottary_tickets.created_at as ticket_buy_date',
                    'lottaries.id as lottery_id',
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
                'message' => 'Lottery tickets fetched successfully.',
                'data' => [
                    'filter' => $filter,
                    'total' => $tickets->count(),
                    'tickets' => $tickets->map(fn ($ticket) => $this->formatTicketRow($ticket))->values(),
                ],
            ]);
        } catch (Throwable $e) {
            return $this->handleException('Lottery tickets fetch failed', $e, [
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function ticketDetails(Request $request, string $ticketNumber): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        try {
            $userId = auth()->id();

            $ticket = DB::table('lottary_tickets')
                ->join('lottaries', 'lottary_tickets.lottary_id', '=', 'lottaries.id')
                ->join('users', 'lottary_tickets.user_id', '=', 'users.id')
                ->leftJoin('lottary_winners', function ($join) {
                    $join->on('lottary_winners.lottary_id', '=', 'lottary_tickets.lottary_id')
                        ->on('lottary_winners.ticket_number', '=', 'lottary_tickets.ticket_number')
                        ->on('lottary_winners.user_id', '=', 'lottary_tickets.user_id');
                })
                ->where('lottary_tickets.ticket_number', $ticketNumber)
                ->where('lottary_tickets.user_id', $userId)
                ->select(
                    'lottary_tickets.ticket_number',
                    'lottary_tickets.created_at as ticket_buy_date',
                    'lottaries.id as lottery_id',
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
                    'lottary_winners.id as winner_id',
                    'lottary_winners.claim_request',
                    'lottary_winners.claim_code',
                    'lottary_winners.send_gift',
                    'lottary_winners.mobile as claim_mobile',
                    'lottary_winners.claim_request_address',
                    DB::raw("IF(lottary_winners.id IS NULL, 'lose', 'win') as win_status")
                )
                ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lottery ticket details fetched successfully.',
                'data' => $this->formatTicketDetails($ticket),
            ]);
        } catch (Throwable $e) {
            return $this->handleException('Lottery ticket details fetch failed', $e, [
                'user_id' => auth()->id(),
                'ticket_number' => $ticketNumber,
            ]);
        }
    }

    public function wins(Request $request): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        try {
            $userId = auth()->id();
            $cutoffDate = Carbon::now()->subDays(30);
            $filter = $request->query('filter', 'all');

            $wins = LottaryWinner::where('user_type', 'Real')
                ->where('user_id', $userId)
                ->with([
                    'prize:id,prize_value,winner_number,photo,name,description',
                    'lottary:id,title,drew_date',
                ])
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($win) use ($cutoffDate) {
                    $status = 'old';
                    if ($win->lottary && $win->lottary->drew_date) {
                        if (Carbon::parse($win->lottary->drew_date)->gte($cutoffDate)) {
                            $status = 'new';
                        }
                    }

                    return [
                        'winner_id' => $win->id,
                        'ticket_number' => $win->ticket_number,
                        'status' => $status,
                        'claim' => [
                            'claim_request' => (int) $win->claim_request,
                            'claim_code' => $win->claim_code,
                            'send_gift' => (int) $win->send_gift,
                            'mobile' => $win->mobile,
                            'address' => $win->claim_request_address,
                        ],
                        'lottary' => [
                            'id' => $win->lottary->id ?? null,
                            'title' => $win->lottary->title ?? null,
                            'drew_date' => optional($win->lottary->drew_date)->toISOString(),
                        ],
                        'prize' => [
                            'name' => $win->prize->name ?? null,
                            'description' => $win->prize->description ?? null,
                            'prize_value' => $win->prize->prize_value ?? null,
                            'winner_number' => $win->prize->winner_number ?? null,
                            'photo_url' => ($win->prize && $win->prize->photo)
                                ? url($win->prize->photo)
                                : null,
                        ],
                    ];
                })
                ->filter(function ($win) use ($filter) {
                    return match ($filter) {
                        'new' => $win['status'] === 'new',
                        'old' => $win['status'] === 'old',
                        'claimed' => $win['claim']['claim_request'] === 1,
                        'unclaimed' => $win['claim']['claim_request'] === 0,
                        default => true,
                    };
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Lottery wins fetched successfully.',
                'data' => [
                    'filter' => $filter,
                    'total_wins' => $wins->count(),
                    'wins' => $wins,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->handleException('Lottery wins fetch failed', $e, [
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function claimPrize(Request $request, int $winnerId): JsonResponse
    {
        if ($response = $this->validateSystemKey($request)) {
            return $response;
        }

        try {
            $validated = $request->validate([
                'mobile' => 'required|string|max:20',
                'address' => 'required|string|max:500',
            ]);

            $winner = LottaryWinner::where('id', $winnerId)
                ->where('user_id', auth()->id())
                ->where('user_type', 'Real')
                ->first();

            if (!$winner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lottery win not found or not yours.',
                ], 404);
            }

            if ((int) $winner->claim_request === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted a claim request.',
                ], 422);
            }

            $claimCode = $this->generateClaimCode();

            $winner->update([
                'claim_request' => 1,
                'mobile' => $validated['mobile'],
                'claim_request_address' => $validated['address'],
                'claim_code' => $claimCode,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim request submitted successfully.',
                'data' => [
                    'winner_id' => $winner->id,
                    'claim_code' => $claimCode,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->handleException('Lottery claim request failed', $e, [
                'user_id' => auth()->id(),
                'winner_id' => $winnerId,
            ]);
        }
    }

    private function formatLottery(Lottary $lottery): array
    {
        return [
            'id' => $lottery->id,
            'title' => $lottery->title,
            'description' => $lottery->description,
            'photo_url' => $lottery->photo ? url($lottery->photo) : null,
            'price' => $lottery->price,
            'prize_number' => $lottery->prize_number,
            'winner_number' => $lottery->winner_number,
            'start_date' => optional($lottery->start_date)->toISOString(),
            'drew_date' => optional($lottery->drew_date)->toISOString(),
            'is_drew' => (bool) $lottery->is_drew,
            'is_active' => (bool) $lottery->is_active,
            'type' => $lottery->is_active ? 'current' : 'upcoming',
            'prizes' => $lottery->prizes->map(function ($prize) {
                return [
                    'id' => $prize->id,
                    'name' => $prize->name,
                    'description' => $prize->description,
                    'prize_value' => $prize->prize_value,
                    'winner_number' => $prize->winner_number,
                    'photo_url' => $prize->photo ? url($prize->photo) : null,
                ];
            })->values(),
        ];
    }

    private function formatTicketRow(object $ticket): array
    {
        return [
            'ticket_number' => $ticket->ticket_number,
            'ticket_buy_date' => $this->toIsoString($ticket->ticket_buy_date),
            'lottery_id' => (int) $ticket->lottery_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'price' => $ticket->price,
            'drew_date' => $this->toIsoString($ticket->drew_date),
            'is_drew' => (bool) $ticket->is_drew,
            'name' => $ticket->name,
            'email' => $ticket->email,
            'phone' => $ticket->phone,
            'full_address' => $ticket->full_address,
            'win_status' => $ticket->win_status,
        ];
    }

    private function formatTicketDetails(object $ticket): array
    {
        return [
            'ticket_number' => $ticket->ticket_number,
            'ticket_buy_date' => $this->toIsoString($ticket->ticket_buy_date),
            'lottery_id' => (int) $ticket->lottery_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'price' => $ticket->price,
            'drew_date' => $this->toIsoString($ticket->drew_date),
            'is_drew' => (bool) $ticket->is_drew,
            'name' => $ticket->name,
            'email' => $ticket->email,
            'phone' => $ticket->phone,
            'full_address' => $ticket->full_address,
            'win_status' => $ticket->win_status,
            'winner' => $ticket->winner_id ? [
                'winner_id' => (int) $ticket->winner_id,
                'claim_request' => (int) $ticket->claim_request,
                'claim_code' => $ticket->claim_code,
                'send_gift' => (int) $ticket->send_gift,
                'mobile' => $ticket->claim_mobile,
                'address' => $ticket->claim_request_address,
            ] : null,
        ];
    }

    private function generateClaimCode(): string
    {
        do {
            $code = strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4));
        } while (LottaryWinner::where('claim_code', $code)->exists());

        return $code;
    }

    private function toIsoString(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toISOString();
        } catch (Throwable) {
            return null;
        }
    }

    private function handleException(string $message, Throwable $e, array $context = []): JsonResponse
    {
        Log::error($message, array_merge($context, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]));

        return response()->json([
            'success' => false,
            'message' => $message . '.',
            'error' => $e->getMessage(),
        ], 500);
    }

    private function validateSystemKey(Request $request): ?JsonResponse
    {
        if (
            !$request->header('System-Key') ||
            $request->header('System-Key') !== config('app.system_key')
        ) {
            return response()->json([
                'result' => false,
                'message' => 'Request not found!',
            ], 403);
        }

        return null;
    }
}
