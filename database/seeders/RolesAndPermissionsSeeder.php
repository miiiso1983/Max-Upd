<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each module
        $permissions = [
            // Super Admin permissions
            'manage-tenants',
            'view-tenant-statistics',
            'suspend-tenants',
            'manage-licenses',

            // Tenant Admin permissions
            'manage-users',
            'manage-roles',
            'view-reports',
            'manage-settings',

            // Inventory permissions
            'view-inventory',
            'create-inventory',
            'edit-inventory',
            'delete-inventory',
            'manage-warehouses',
            'transfer-inventory',
            'view-stock-alerts',

            // Sales permissions
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',
            'process-returns',
            'manage-pos',
            'view-invoices',
            'create-invoices',

            // Client/CRM permissions
            'view-clients',
            'create-clients',
            'edit-clients',
            'delete-clients',
            'manage-loyalty',
            'view-client-orders',

            // Supplier permissions
            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',
            'manage-purchase-orders',

            // Accounting permissions
            'view-accounting',
            'create-accounting-entries',
            'edit-accounting-entries',
            'delete-accounting-entries',
            'manage-chart-of-accounts',
            'view-financial-reports',
            'manage-collections',

            // HR permissions
            'view-employees',
            'create-employees',
            'edit-employees',
            'delete-employees',
            'manage-attendance',
            'manage-payroll',
            'view-hr-reports',

            // Medical Rep permissions
            'view-medical-reps',
            'create-medical-reps',
            'edit-medical-reps',
            'delete-medical-reps',
            'track-visits',
            'manage-territories',

            // Analytics permissions
            'view-analytics',
            'view-predictions',
            'manage-ai-settings',

            // System permissions
            'import-data',
            'export-data',
            'manage-backups',
            'view-system-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $tenantAdminRole = Role::create(['name' => 'tenant-admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $salesRole = Role::create(['name' => 'sales-rep']);
        $inventoryRole = Role::create(['name' => 'inventory-manager']);
        $accountantRole = Role::create(['name' => 'accountant']);
        $hrRole = Role::create(['name' => 'hr-manager']);
        $medicalRepRole = Role::create(['name' => 'medical-rep']);
        $cashierRole = Role::create(['name' => 'cashier']);

        // Assign permissions to super admin (all permissions)
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign permissions to tenant admin
        $tenantAdminRole->givePermissionTo([
            'manage-users', 'manage-roles', 'view-reports', 'manage-settings',
            'view-inventory', 'create-inventory', 'edit-inventory', 'manage-warehouses',
            'view-sales', 'create-sales', 'edit-sales', 'view-invoices', 'create-invoices',
            'view-clients', 'create-clients', 'edit-clients', 'manage-loyalty',
            'view-suppliers', 'create-suppliers', 'edit-suppliers',
            'view-accounting', 'view-financial-reports', 'manage-collections',
            'view-employees', 'create-employees', 'edit-employees', 'manage-attendance',
            'view-analytics', 'import-data', 'export-data'
        ]);

        // Assign permissions to manager
        $managerRole->givePermissionTo([
            'view-reports', 'view-inventory', 'view-sales', 'view-clients', 'view-suppliers',
            'view-accounting', 'view-financial-reports', 'view-employees', 'view-analytics'
        ]);

        // Assign permissions to sales rep
        $salesRole->givePermissionTo([
            'view-sales', 'create-sales', 'edit-sales', 'view-invoices', 'create-invoices',
            'view-clients', 'create-clients', 'edit-clients', 'view-inventory', 'manage-pos'
        ]);

        // Assign permissions to inventory manager
        $inventoryRole->givePermissionTo([
            'view-inventory', 'create-inventory', 'edit-inventory', 'manage-warehouses',
            'transfer-inventory', 'view-stock-alerts', 'view-suppliers'
        ]);

        // Assign permissions to accountant
        $accountantRole->givePermissionTo([
            'view-accounting', 'create-accounting-entries', 'edit-accounting-entries',
            'manage-chart-of-accounts', 'view-financial-reports', 'manage-collections'
        ]);

        // Assign permissions to HR manager
        $hrRole->givePermissionTo([
            'view-employees', 'create-employees', 'edit-employees', 'manage-attendance',
            'manage-payroll', 'view-hr-reports'
        ]);

        // Assign permissions to medical rep
        $medicalRepRole->givePermissionTo([
            'view-medical-reps', 'track-visits', 'view-clients', 'create-clients', 'edit-clients'
        ]);

        // Assign permissions to cashier
        $cashierRole->givePermissionTo([
            'view-sales', 'create-sales', 'manage-pos', 'view-clients', 'view-inventory'
        ]);
    }
}
