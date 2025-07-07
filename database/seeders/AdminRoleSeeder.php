<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        // Create tenant-admin role if it doesn't exist
        $tenantAdminRole = Role::firstOrCreate([
            'name' => 'tenant-admin'
        ], [
            'name' => 'tenant-admin',
            'guard_name' => 'web'
        ]);

        // Create super_admin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin'
        ], [
            'name' => 'super_admin',
            'guard_name' => 'web'
        ]);

        // Define admin permissions
        $adminPermissions = [
            'manage-users',
            'manage-roles',
            'view-reports',
            'manage-settings',
            'view-inventory',
            'create-inventory',
            'edit-inventory',
            'manage-warehouses',
            'view-sales',
            'create-sales',
            'edit-sales',
            'view-invoices',
            'create-invoices',
            'view-clients',
            'create-clients',
            'edit-clients',
            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'view-accounting',
            'view-financial-reports',
            'manage-collections',
            'view-employees',
            'create-employees',
            'edit-employees',
            'manage-attendance',
            'view-analytics'
        ];

        // Create permissions if they don't exist and assign to admin roles
        foreach ($adminPermissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName
            ], [
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);

            // Give permission to both admin and tenant-admin roles
            if (!$adminRole->hasPermissionTo($permission)) {
                $adminRole->givePermissionTo($permission);
            }

            if (!$tenantAdminRole->hasPermissionTo($permission)) {
                $tenantAdminRole->givePermissionTo($permission);
            }
        }

        // Super admin gets all permissions
        $superAdminRole->givePermissionTo(Permission::all());

        $this->command->info('Admin roles and permissions created successfully!');
    }
}
