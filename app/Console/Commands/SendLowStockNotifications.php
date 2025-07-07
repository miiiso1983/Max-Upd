<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendLowStockNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:low-stock {--tenant= : Specific tenant ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send low stock notifications to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        $this->info('Checking for low stock products...');

        // Get products with low stock
        $query = \App\Modules\Inventory\Models\Product::where('current_stock', '<=', \Illuminate\Database\Eloquent\Builder::raw('minimum_stock'))
                                                    ->where('minimum_stock', '>', 0);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lowStockProducts = $query->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('No low stock products found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$lowStockProducts->count()} low stock products.");

        $notificationsSent = 0;

        foreach ($lowStockProducts as $product) {
            // Get users who should receive notifications for this tenant
            $users = User::where('tenant_id', $product->tenant_id)
                        ->whereHas('roles', function ($query) {
                            $query->whereIn('name', ['admin', 'inventory_manager']);
                        })
                        ->where('is_active', true)
                        ->get();

            foreach ($users as $user) {
                // Check if user wants low stock alerts
                $settings = $user->notification_settings ?? [];
                if (($settings['low_stock_alerts'] ?? true)) {
                    NotificationService::lowStock($user, $product, $product->current_stock);
                    $notificationsSent++;
                }
            }
        }

        $this->info("Sent {$notificationsSent} low stock notifications.");

        return Command::SUCCESS;
    }
}
