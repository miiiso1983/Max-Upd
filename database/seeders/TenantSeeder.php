<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::where('is_super_admin', true)->first();

        if (!$superAdmin) {
            $this->command->error('Super admin not found. Please run SuperAdminSeeder first.');
            return;
        }

        // Create sample tenants
        $tenants = [
            [
                'name' => 'Al-Shifa Pharmacy',
                'domain' => 'alshifa',
                'database' => 'maxcon_tenant_alshifa',
                'company_name' => 'Al-Shifa Pharmacy',
                'company_type' => 'pharmacy',
                'contact_person' => 'Ahmed Al-Baghdadi',
                'email' => 'admin@alshifa-pharmacy.com',
                'phone' => '+964-770-111-2222',
                'address' => 'Al-Karrada Street, Building 15',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'license_key' => 'MAXCON-ALSHIFA-' . Str::upper(Str::random(8)),
                'license_expires_at' => now()->addYear(),
                'max_users' => 25,
                'is_active' => true,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Babylon Medical Distributor',
                'domain' => 'babylon',
                'database' => 'maxcon_tenant_babylon',
                'company_name' => 'Babylon Medical Distribution Co.',
                'company_type' => 'medical_distributor',
                'contact_person' => 'Sara Al-Hillawi',
                'email' => 'admin@babylon-medical.com',
                'phone' => '+964-780-333-4444',
                'address' => 'Industrial Zone, Warehouse Complex A',
                'city' => 'Hillah',
                'governorate' => 'Babylon',
                'license_key' => 'MAXCON-BABYLON-' . Str::upper(Str::random(8)),
                'license_expires_at' => now()->addMonths(6),
                'max_users' => 50,
                'is_active' => true,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Basra General Hospital',
                'domain' => 'basra-hospital',
                'database' => 'maxcon_tenant_basra_hospital',
                'company_name' => 'Basra General Hospital',
                'company_type' => 'hospital',
                'contact_person' => 'Dr. Omar Al-Basri',
                'email' => 'admin@basra-hospital.gov.iq',
                'phone' => '+964-770-555-6666',
                'address' => 'Medical City Complex, Block C',
                'city' => 'Basra',
                'governorate' => 'Basra',
                'license_key' => 'MAXCON-BASRA-' . Str::upper(Str::random(8)),
                'license_expires_at' => now()->addMonths(3),
                'max_users' => 100,
                'is_active' => true,
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create($tenantData);

            // Create database for tenant
            $this->createTenantDatabase($tenant->database);

            $this->command->info("Created tenant: {$tenant->name} (Domain: {$tenant->domain})");
        }
    }

    private function createTenantDatabase(string $databaseName): void
    {
        try {
            \DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->command->info("Database created: {$databaseName}");
        } catch (\Exception $e) {
            $this->command->error("Failed to create database {$databaseName}: " . $e->getMessage());
        }
    }
}
