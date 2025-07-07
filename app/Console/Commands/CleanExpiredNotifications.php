<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanExpiredNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean-expired {--days=30 : Number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired and old notifications from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("Cleaning notifications older than {$days} days...");

        // Clean expired notifications
        $expiredCount = NotificationService::cleanExpired();
        $this->info("Cleaned {$expiredCount} expired notifications.");

        // Clean old notifications
        $oldCount = \App\Models\Notification::where('created_at', '<', now()->subDays($days))->delete();
        $this->info("Cleaned {$oldCount} old notifications.");

        $totalCleaned = $expiredCount + $oldCount;
        $this->info("Total notifications cleaned: {$totalCleaned}");

        return Command::SUCCESS;
    }
}
