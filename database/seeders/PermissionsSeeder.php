<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions with groups
        $permissions = [
            // Users Management
            [
                'name' => 'view users',
                'display_name' => 'عرض المستخدمين',
                'description' => 'عرض قائمة المستخدمين وتفاصيلهم',
                'group' => 'users'
            ],
            [
                'name' => 'create users',
                'display_name' => 'إنشاء المستخدمين',
                'description' => 'إضافة مستخدمين جدد للنظام',
                'group' => 'users'
            ],
            [
                'name' => 'edit users',
                'display_name' => 'تعديل المستخدمين',
                'description' => 'تعديل بيانات المستخدمين الموجودين',
                'group' => 'users'
            ],
            [
                'name' => 'delete users',
                'display_name' => 'حذف المستخدمين',
                'description' => 'حذف المستخدمين من النظام',
                'group' => 'users'
            ],

            // Roles Management
            [
                'name' => 'view roles',
                'display_name' => 'عرض الأدوار',
                'description' => 'عرض قائمة الأدوار وتفاصيلها',
                'group' => 'roles'
            ],
            [
                'name' => 'create roles',
                'display_name' => 'إنشاء الأدوار',
                'description' => 'إضافة أدوار جديدة للنظام',
                'group' => 'roles'
            ],
            [
                'name' => 'edit roles',
                'display_name' => 'تعديل الأدوار',
                'description' => 'تعديل الأدوار الموجودة وصلاحياتها',
                'group' => 'roles'
            ],
            [
                'name' => 'delete roles',
                'display_name' => 'حذف الأدوار',
                'description' => 'حذف الأدوار من النظام',
                'group' => 'roles'
            ],

            // Permissions Management
            [
                'name' => 'view permissions',
                'display_name' => 'عرض الصلاحيات',
                'description' => 'عرض قائمة الصلاحيات',
                'group' => 'permissions'
            ],
            [
                'name' => 'create permissions',
                'display_name' => 'إنشاء الصلاحيات',
                'description' => 'إضافة صلاحيات جديدة للنظام',
                'group' => 'permissions'
            ],
            [
                'name' => 'edit permissions',
                'display_name' => 'تعديل الصلاحيات',
                'description' => 'تعديل الصلاحيات الموجودة',
                'group' => 'permissions'
            ],
            [
                'name' => 'delete permissions',
                'display_name' => 'حذف الصلاحيات',
                'description' => 'حذف الصلاحيات من النظام',
                'group' => 'permissions'
            ],

            // Sales Management
            [
                'name' => 'view sales',
                'display_name' => 'عرض المبيعات',
                'description' => 'عرض بيانات المبيعات والفواتير',
                'group' => 'sales'
            ],
            [
                'name' => 'create sales',
                'display_name' => 'إنشاء المبيعات',
                'description' => 'إنشاء فواتير ومبيعات جديدة',
                'group' => 'sales'
            ],
            [
                'name' => 'edit sales',
                'display_name' => 'تعديل المبيعات',
                'description' => 'تعديل الفواتير والمبيعات',
                'group' => 'sales'
            ],
            [
                'name' => 'delete sales',
                'display_name' => 'حذف المبيعات',
                'description' => 'حذف الفواتير والمبيعات',
                'group' => 'sales'
            ],

            // Inventory Management
            [
                'name' => 'view inventory',
                'display_name' => 'عرض المخزون',
                'description' => 'عرض بيانات المخزون والمنتجات',
                'group' => 'inventory'
            ],
            [
                'name' => 'create inventory',
                'display_name' => 'إدارة المخزون',
                'description' => 'إضافة وتعديل المنتجات والمخزون',
                'group' => 'inventory'
            ],
            [
                'name' => 'edit inventory',
                'display_name' => 'تعديل المخزون',
                'description' => 'تعديل بيانات المنتجات والمخزون',
                'group' => 'inventory'
            ],
            [
                'name' => 'delete inventory',
                'display_name' => 'حذف من المخزون',
                'description' => 'حذف المنتجات من المخزون',
                'group' => 'inventory'
            ],

            // Reports
            [
                'name' => 'view reports',
                'display_name' => 'عرض التقارير',
                'description' => 'عرض جميع التقارير والإحصائيات',
                'group' => 'reports'
            ],
            [
                'name' => 'export reports',
                'display_name' => 'تصدير التقارير',
                'description' => 'تصدير التقارير بصيغ مختلفة',
                'group' => 'reports'
            ],

            // Customers Management
            [
                'name' => 'view customers',
                'display_name' => 'عرض العملاء',
                'description' => 'عرض قائمة العملاء وبياناتهم',
                'group' => 'customers'
            ],
            [
                'name' => 'create customers',
                'display_name' => 'إضافة العملاء',
                'description' => 'إضافة عملاء جدد',
                'group' => 'customers'
            ],
            [
                'name' => 'edit customers',
                'display_name' => 'تعديل العملاء',
                'description' => 'تعديل بيانات العملاء',
                'group' => 'customers'
            ],
            [
                'name' => 'delete customers',
                'display_name' => 'حذف العملاء',
                'description' => 'حذف العملاء من النظام',
                'group' => 'customers'
            ],

            // Settings
            [
                'name' => 'view settings',
                'display_name' => 'عرض الإعدادات',
                'description' => 'عرض إعدادات النظام',
                'group' => 'settings'
            ],
            [
                'name' => 'edit settings',
                'display_name' => 'تعديل الإعدادات',
                'description' => 'تعديل إعدادات النظام',
                'group' => 'settings'
            ],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'مدير عام',
                'description' => 'مدير عام للنظام مع جميع الصلاحيات'
            ],
            [
                'name' => 'admin',
                'display_name' => 'مدير',
                'description' => 'مدير النظام مع معظم الصلاحيات'
            ],
            [
                'name' => 'manager',
                'display_name' => 'مشرف',
                'description' => 'مشرف على العمليات اليومية'
            ],
            [
                'name' => 'employee',
                'display_name' => 'موظف',
                'description' => 'موظف عادي مع صلاحيات محدودة'
            ],
            [
                'name' => 'user',
                'display_name' => 'مستخدم',
                'description' => 'مستخدم عادي مع صلاحيات أساسية'
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Assign permissions based on role
            switch ($role->name) {
                case 'super_admin':
                    // Super admin gets all permissions
                    $role->givePermissionTo(Permission::all());
                    break;

                case 'admin':
                    // Admin gets most permissions except some sensitive ones
                    $role->givePermissionTo([
                        'view users', 'create users', 'edit users',
                        'view roles', 'edit roles',
                        'view permissions',
                        'view sales', 'create sales', 'edit sales',
                        'view inventory', 'create inventory', 'edit inventory',
                        'view reports', 'export reports',
                        'view customers', 'create customers', 'edit customers',
                        'view settings'
                    ]);
                    break;

                case 'manager':
                    // Manager gets operational permissions
                    $role->givePermissionTo([
                        'view users',
                        'view sales', 'create sales', 'edit sales',
                        'view inventory', 'create inventory', 'edit inventory',
                        'view reports', 'export reports',
                        'view customers', 'create customers', 'edit customers'
                    ]);
                    break;

                case 'employee':
                    // Employee gets basic operational permissions
                    $role->givePermissionTo([
                        'view sales', 'create sales',
                        'view inventory',
                        'view reports',
                        'view customers', 'create customers'
                    ]);
                    break;

                case 'user':
                    // User gets minimal permissions
                    $role->givePermissionTo([
                        'view sales',
                        'view inventory',
                        'view customers'
                    ]);
                    break;
            }
        }

        $this->command->info('Permissions and roles created successfully!');
    }
}
