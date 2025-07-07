<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:assign {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign admin role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Find the user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        // Check if user already has admin role
        if ($user->hasRole('admin')) {
            $this->info("User {$user->name} already has admin role!");
            return 0;
        }

        // Assign admin role
        $user->assignRole('admin');

        $this->info("Admin role assigned successfully!");
        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Current roles: " . $user->getRoleNames()->join(', '));

        return 0;
    }
}
