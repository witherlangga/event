<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JwtBlacklist;

class ClearJwtBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:clear-blacklist {--days= : Optional days to keep beyond expiration (default 0)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired entries from the JWT blacklist';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $query = JwtBlacklist::query();

        $now = now();
        if ($days > 0) {
            $threshold = $now->subDays($days);
            $deleted = $query->whereNotNull('expires_at')->where('expires_at', '<', $threshold)->delete();
        } else {
            $deleted = $query->whereNotNull('expires_at')->where('expires_at', '<', $now)->delete();
        }

        $this->info("Deleted {$deleted} expired blacklist entries.");

        return 0;
    }
}
