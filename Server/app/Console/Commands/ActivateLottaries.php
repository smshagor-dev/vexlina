<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottary;
use Carbon\Carbon;

class ActivateLottaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lottaries:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate lottaries whose start_date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $updated = Lottary::where('start_date', '<=', $now)
            ->where('is_active', 0)
            ->where('is_drew', 0) 
            ->update(['is_active' => 1]);
    
        $this->info("Activated $updated lottaries.");
        return 0;
    }
}
