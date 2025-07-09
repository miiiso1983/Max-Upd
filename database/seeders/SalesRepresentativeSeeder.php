<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Modules\SalesReps\Models\SalesRepresentative;
use App\Modules\SalesReps\Models\Territory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SalesRepresentativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for sales representatives module
        $permissions = [
            'view_sales_reps',
            'create_sales_reps',
            'edit_sales_reps',
            'delete_sales_reps',
            'view_visits',
            'create_visits',
            'edit_visits',
            'delete_visits',
            'view_territories',
            'create_territories',
            'edit_territories',
            'delete_territories',
            'view_tasks',
            'create_tasks',
            'edit_tasks',
            'delete_tasks',
            'view_reports',
            'export_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create sales representative role
        $salesRepRole = Role::firstOrCreate(['name' => 'sales_representative']);
        $salesRepRole->givePermissionTo([
            'view_visits',
            'create_visits',
            'edit_visits',
            'view_tasks',
            'edit_tasks',
        ]);

        // Create sales manager role
        $salesManagerRole = Role::firstOrCreate(['name' => 'sales_manager']);
        $salesManagerRole->givePermissionTo($permissions);

        // Create sample territories
        $territories = [
            [
                'name' => 'Baghdad Central',
                'name_ar' => 'بغداد المركز',
                'code' => 'BGD-001',
                'description' => 'Central Baghdad territory covering main commercial areas',
                'governorate' => 'بغداد',
                'cities' => ['بغداد'],
                'areas' => ['الكرادة', 'الجادرية', 'المنصور', 'الكاظمية'],
                'center_latitude' => 33.3152,
                'center_longitude' => 44.3661,
                'radius_km' => 15,
                'type' => 'urban',
                'estimated_customers' => 150,
                'estimated_potential' => 500000.00,
                'difficulty_level' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Basra Industrial',
                'name_ar' => 'البصرة الصناعية',
                'code' => 'BSR-001',
                'description' => 'Basra industrial and port area',
                'governorate' => 'البصرة',
                'cities' => ['البصرة'],
                'areas' => ['المعقل', 'أبو الخصيب', 'الفاو'],
                'center_latitude' => 30.5085,
                'center_longitude' => 47.7804,
                'radius_km' => 25,
                'type' => 'mixed',
                'estimated_customers' => 80,
                'estimated_potential' => 300000.00,
                'difficulty_level' => 'hard',
                'is_active' => true,
            ],
            [
                'name' => 'Erbil North',
                'name_ar' => 'أربيل الشمالية',
                'code' => 'ERB-001',
                'description' => 'Northern Erbil territory',
                'governorate' => 'أربيل',
                'cities' => ['أربيل'],
                'areas' => ['عنكاوا', 'شقلاوة', 'صلاح الدين'],
                'center_latitude' => 36.1911,
                'center_longitude' => 44.0093,
                'radius_km' => 20,
                'type' => 'urban',
                'estimated_customers' => 100,
                'estimated_potential' => 400000.00,
                'difficulty_level' => 'easy',
                'is_active' => true,
            ],
        ];

        foreach ($territories as $territoryData) {
            Territory::firstOrCreate(
                ['code' => $territoryData['code']],
                $territoryData
            );
        }

        // Create sample sales representatives
        $salesReps = [
            [
                'name' => 'Ahmed Al-Mahmoud',
                'name_ar' => 'أحمد المحمود',
                'email' => 'ahmed.mahmoud@maxcon-erp.com',
                'phone' => '+964-770-123-4567',
                'mobile' => '+964-750-123-4567',
                'address' => 'Baghdad, Al-Karrada District',
                'city' => 'بغداد',
                'governorate' => 'بغداد',
                'hire_date' => '2023-01-15',
                'birth_date' => '1990-05-20',
                'gender' => 'male',
                'national_id' => '19900520001',
                'base_salary' => 800000.00,
                'commission_rate' => 2.5,
                'monthly_target' => 5000000.00,
                'quarterly_target' => 15000000.00,
                'annual_target' => 60000000.00,
                'employment_type' => 'full_time',
                'status' => 'active',
                'emergency_contact' => [
                    'name' => 'Fatima Al-Mahmoud',
                    'phone' => '+964-770-987-6543',
                    'relationship' => 'wife'
                ],
                'bank_details' => [
                    'bank_name' => 'Rafidain Bank',
                    'account_number' => '123456789',
                    'iban' => 'IQ98RAFI123456789'
                ],
                'can_create_orders' => true,
                'can_collect_payments' => true,
                'max_discount_percentage' => 5.0,
                'max_order_amount' => 1000000.00,
                'working_hours' => [
                    'start_time' => '08:00',
                    'end_time' => '16:00',
                    'days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']
                ],
                'gps_settings' => [
                    'tracking_enabled' => true,
                    'accuracy_required' => 10
                ],
            ],
            [
                'name' => 'Sara Al-Zahra',
                'name_ar' => 'سارة الزهراء',
                'email' => 'sara.zahra@maxcon-erp.com',
                'phone' => '+964-771-234-5678',
                'mobile' => '+964-751-234-5678',
                'address' => 'Basra, Al-Maqal District',
                'city' => 'البصرة',
                'governorate' => 'البصرة',
                'hire_date' => '2023-03-01',
                'birth_date' => '1992-08-15',
                'gender' => 'female',
                'national_id' => '19920815002',
                'base_salary' => 750000.00,
                'commission_rate' => 3.0,
                'monthly_target' => 4000000.00,
                'quarterly_target' => 12000000.00,
                'annual_target' => 48000000.00,
                'employment_type' => 'full_time',
                'status' => 'active',
                'emergency_contact' => [
                    'name' => 'Ali Al-Zahra',
                    'phone' => '+964-771-876-5432',
                    'relationship' => 'brother'
                ],
                'bank_details' => [
                    'bank_name' => 'Rasheed Bank',
                    'account_number' => '987654321',
                    'iban' => 'IQ98RASH987654321'
                ],
                'can_create_orders' => true,
                'can_collect_payments' => true,
                'max_discount_percentage' => 3.0,
                'max_order_amount' => 800000.00,
                'working_hours' => [
                    'start_time' => '08:30',
                    'end_time' => '16:30',
                    'days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']
                ],
                'gps_settings' => [
                    'tracking_enabled' => true,
                    'accuracy_required' => 15
                ],
            ],
            [
                'name' => 'Omar Al-Kurdish',
                'name_ar' => 'عمر الكردي',
                'email' => 'omar.kurdish@maxcon-erp.com',
                'phone' => '+964-772-345-6789',
                'mobile' => '+964-752-345-6789',
                'address' => 'Erbil, Ankawa District',
                'city' => 'أربيل',
                'governorate' => 'أربيل',
                'hire_date' => '2023-06-01',
                'birth_date' => '1988-12-10',
                'gender' => 'male',
                'national_id' => '19881210003',
                'base_salary' => 850000.00,
                'commission_rate' => 2.0,
                'monthly_target' => 6000000.00,
                'quarterly_target' => 18000000.00,
                'annual_target' => 72000000.00,
                'employment_type' => 'full_time',
                'status' => 'active',
                'emergency_contact' => [
                    'name' => 'Layla Al-Kurdish',
                    'phone' => '+964-772-765-4321',
                    'relationship' => 'wife'
                ],
                'bank_details' => [
                    'bank_name' => 'Kurdistan Bank',
                    'account_number' => '456789123',
                    'iban' => 'IQ98KURD456789123'
                ],
                'can_create_orders' => true,
                'can_collect_payments' => true,
                'max_discount_percentage' => 7.0,
                'max_order_amount' => 1500000.00,
                'working_hours' => [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']
                ],
                'gps_settings' => [
                    'tracking_enabled' => true,
                    'accuracy_required' => 5
                ],
            ],
        ];

        foreach ($salesReps as $index => $repData) {
            // Create user account
            $user = User::firstOrCreate(
                ['email' => $repData['email']],
                [
                    'name' => $repData['name'],
                    'email' => $repData['email'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign sales representative role
            $user->assignRole($salesRepRole);

            // Generate employee code
            $employeeCode = 'REP' . date('Y') . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            // Create sales representative
            $salesRep = SalesRepresentative::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($repData, [
                    'user_id' => $user->id,
                    'employee_code' => $employeeCode,
                    'created_by' => 1, // Assuming admin user ID is 1
                ])
            );

            // Assign territory based on governorate
            $territory = Territory::where('governorate', $repData['governorate'])->first();
            if ($territory) {
                $salesRep->territories()->syncWithoutDetaching([
                    $territory->id => [
                        'assigned_date' => now(),
                        'effective_from' => now(),
                        'assignment_type' => 'primary',
                        'is_active' => true,
                        'target_amount' => $repData['monthly_target'],
                        'target_visits_per_month' => 50,
                        'assigned_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }

        $this->command->info('Sales representatives seeder completed successfully!');
    }
}
