<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottary;
use App\Models\LottaryPrize;
use App\Models\LottaryTicket;
use App\Models\FakePeople;
use App\Models\LottaryWinner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\LotteryNotification;

class LottaryDrewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lottary:drew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute lottery draw for due lottaries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lottaries = Lottary::where('drew_date', '<=', now())
            ->where('is_drew', 0)
            ->get();
    
        foreach ($lottaries as $lottary) {
    
            DB::transaction(function () use ($lottary) {
    
                $prizes = LottaryPrize::where('lottary_id', $lottary->id)->get();
    
                foreach ($prizes as $prize) {
    
                    $winnerCount = $prize->winner_number;
    
                    if ($prize->drew_type === 'Real') {
    
                        $tickets = LottaryTicket::where('lottary_id', $lottary->id)
                            ->inRandomOrder()
                            ->take($winnerCount)
                            ->get();
    
                        foreach ($tickets as $ticket) {

                            $winner = LottaryWinner::create([
                                'lottary_id'          => $lottary->id,
                                'lottary_prize_id'    => $prize->id,
                                'user_type'           => 'Real',
                                'user_id'             => $ticket->user_id,
                                'fake_people_id'      => null,
                                'lottary_tickets_id'  => $ticket->id,
                                'ticket_number'       => $ticket->ticket_number,
                            ]);

                            $alreadyNotified = DB::table('notifications')
                                ->where('notification_type_id', 37)
                                ->where('notifiable_id', $ticket->user_id)
                                ->whereJsonContains('data->winner_id', $winner->id)
                                ->exists();
    
                            if (!$alreadyNotified) {
                                DB::table('notifications')->insert([
                                    'id' => (string) Str::uuid(),
                                    'notification_type_id' => 37,
                                    'type' => 'App\Notifications\LotteryNotification',
                                    'notifiable_type' => 'App\Models\User',
                                    'notifiable_id' => $ticket->user_id,
                                    'data' => json_encode([
                                        'notification_type_id' => 37,
                                        'lottary_id'           => $lottary->id,
                                        'prize_id'             => $prize->id,
                                        'prize_name'           => $prize->name,
                                        'ticket_number'        => $ticket->ticket_number,
                                        'winner_id'            => $winner->id,
                                        'user_id'              => $ticket->user_id,
                                    ]),
                                    'read_at' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
    
                    } else {
                        $fakeUsers = FakePeople::inRandomOrder()
                            ->take($winnerCount)
                            ->get();
    
                        foreach ($fakeUsers as $fakeUser) {
    
                            do {
                                $fakeTicket = strtoupper(Str::random(4)) . '-' .
                                              strtoupper(Str::random(4)) . '-' .
                                              strtoupper(Str::random(4));
                            } while (
                                LottaryTicket::where('ticket_number', $fakeTicket)->exists()
                            );
    
                            LottaryWinner::create([
                                'lottary_id'          => $lottary->id,
                                'lottary_prize_id'    => $prize->id,
                                'user_type'           => 'Fake',
                                'user_id'             => null,
                                'fake_people_id'      => $fakeUser->id,
                                'lottary_tickets_id'  => null,
                                'ticket_number'       => $fakeTicket,
                            ]);
                        }
                    }
                }
    
                $lottary->is_drew = 1;
                $lottary->is_active = 0;
                $lottary->save();
            });
        }
    
        $this->info('Lottery draw completed successfully!');
        return 0;
    }

}