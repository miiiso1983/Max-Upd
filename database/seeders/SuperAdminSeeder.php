<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $superAdmin = User::create([
            'name' => 'MaxCon Super Admin',
            'email' => 'admin@maxcon-erp.com',
            'password' => Hash::make('MaxCon@2025'),
            'phone' => '+964-770-123-4567',
            'is_active' => true,
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Assign super admin role
        $superAdmin->assignRole('super-admin');

        $this->command->info('Super admin created successfully!');
        $this->command->info('Email: admin@maxcon-erp.com');
        $this->command->info('Password: MaxCon@2025');
    }
}
