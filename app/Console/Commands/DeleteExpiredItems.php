<?php

namespace App\Console\Commands;

use App\Models\PantryItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pantry:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pantry items that expired 30 or more days ago.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = Carbon::today()->subDays(30);

        $count = PantryItem::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $cutoff)
            ->count();

        if ($count === 0) {
            $this->info('No expired items to delete.');
            return self::SUCCESS;
        }

        PantryItem::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $cutoff)
            ->delete();

        $this->info("Deleted {$count} expired pantry item(s) that passed the 30-day grace period.");

        return self::SUCCESS;
    }
}
