<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create departments
        $this->createDepartments();

        // Create positions
        $this->createPositions();

        // Create employees
        $this->createEmployees();

        // Create sample attendance records
        $this->createAttendanceRecords();
    }

    /**
     * Create departments
     */
    private function createDepartments()
    {
        $departments = [
            [
                'name' => 'Management',
                'name_ar' => 'الإدارة',
                'code' => 'MGT-001',
                'description' => 'Executive management and administration',
                'description_ar' => 'الإدارة التنفيذية والإدارية',
                'budget' => 500000,
                'location' => 'Head Office',
                'created_by' => 1,
            ],
            [
                'name' => 'Sales & Marketing',
                'name_ar' => 'المبيعات والتسويق',
                'code' => 'SAL-001',
                'description' => 'Sales operations and marketing activities',
                'description_ar' => 'عمليات المبيعات والأنشطة التسويقية',
                'budget' => 300000,
                'location' => 'Ground Floor',
                'created_by' => 1,
            ],
            [
                'name' => 'Pharmacy Operations',
                'name_ar' => 'عمليات الصيدلة',
                'code' => 'PHR-001',
                'description' => 'Pharmaceutical operations and dispensing',
                'description_ar' => 'العمليات الصيدلانية والصرف',
                'budget' => 400000,
                'location' => 'Pharmacy Floor',
                'created_by' => 1,
            ],
            [
                'name' => 'Warehouse & Logistics',
                'name_ar' => 'المستودعات واللوجستيات',
                'code' => 'WHS-001',
                'description' => 'Inventory management and logistics',
                'description_ar' => 'إدارة المخزون واللوجستيات',
                'budget' => 250000,
                'location' => 'Warehouse',
                'created_by' => 1,
            ],
            [
                'name' => 'Finance & Accounting',
                'name_ar' => 'المالية والمحاسبة',
                'code' => 'FIN-001',
                'description' => 'Financial management and accounting',
                'description_ar' => 'الإدارة المالية والمحاسبة',
                'budget' => 200000,
                'location' => 'Second Floor',
                'created_by' => 1,
            ],
            [
                'name' => 'Human Resources',
                'name_ar' => 'الموارد البشرية',
                'code' => 'HR-001',
                'description' => 'Human resources management',
                'description_ar' => 'إدارة الموارد البشرية',
                'budget' => 150000,
                'location' => 'Second Floor',
                'created_by' => 1,
            ],
        ];

        foreach ($departments as $departmentData) {
            \App\Modules\HR\Models\Department::create($departmentData);
        }
    }

    /**
     * Create positions
     */
    private function createPositions()
    {
        $positions = [
            // Management
            [
                'title' => 'General Manager',
                'title_ar' => 'المدير العام',
                'code' => 'GM-001',
                'department_id' => 1,
                'level' => 'executive',
                'description' => 'Overall company management and strategic direction',
                'min_salary' => 2000000,
                'max_salary' => 3000000,
                'created_by' => 1,
            ],
            [
                'title' => 'Assistant Manager',
                'title_ar' => 'مساعد المدير',
                'code' => 'AM-001',
                'department_id' => 1,
                'level' => 'manager',
                'description' => 'Assist in management operations',
                'min_salary' => 1200000,
                'max_salary' => 1800000,
                'created_by' => 1,
            ],
            // Sales & Marketing
            [
                'title' => 'Sales Manager',
                'title_ar' => 'مدير المبيعات',
                'code' => 'SM-001',
                'department_id' => 2,
                'level' => 'manager',
                'description' => 'Manage sales team and operations',
                'min_salary' => 1000000,
                'max_salary' => 1500000,
                'created_by' => 1,
            ],
            [
                'title' => 'Sales Representative',
                'title_ar' => 'مندوب مبيعات',
                'code' => 'SR-001',
                'department_id' => 2,
                'level' => 'senior',
                'description' => 'Handle customer sales and relationships',
                'min_salary' => 600000,
                'max_salary' => 900000,
                'created_by' => 1,
            ],
            // Pharmacy Operations
            [
                'title' => 'Chief Pharmacist',
                'title_ar' => 'كبير الصيادلة',
                'code' => 'CP-001',
                'department_id' => 3,
                'level' => 'manager',
                'description' => 'Lead pharmacy operations and compliance',
                'min_salary' => 1200000,
                'max_salary' => 1800000,
                'created_by' => 1,
            ],
            [
                'title' => 'Pharmacist',
                'title_ar' => 'صيدلي',
                'code' => 'PH-001',
                'department_id' => 3,
                'level' => 'senior',
                'description' => 'Dispense medications and provide consultation',
                'min_salary' => 800000,
                'max_salary' => 1200000,
                'created_by' => 1,
            ],
            [
                'title' => 'Pharmacy Assistant',
                'title_ar' => 'مساعد صيدلي',
                'code' => 'PA-001',
                'department_id' => 3,
                'level' => 'junior',
                'description' => 'Assist pharmacists in daily operations',
                'min_salary' => 400000,
                'max_salary' => 600000,
                'created_by' => 1,
            ],
            // Warehouse & Logistics
            [
                'title' => 'Warehouse Manager',
                'title_ar' => 'مدير المستودع',
                'code' => 'WM-001',
                'department_id' => 4,
                'level' => 'manager',
                'description' => 'Manage warehouse operations and inventory',
                'min_salary' => 800000,
                'max_salary' => 1200000,
                'created_by' => 1,
            ],
            [
                'title' => 'Warehouse Clerk',
                'title_ar' => 'موظف مستودع',
                'code' => 'WC-001',
                'department_id' => 4,
                'level' => 'junior',
                'description' => 'Handle inventory and stock movements',
                'min_salary' => 350000,
                'max_salary' => 500000,
                'created_by' => 1,
            ],
            // Finance & Accounting
            [
                'title' => 'Accountant',
                'title_ar' => 'محاسب',
                'code' => 'AC-001',
                'department_id' => 5,
                'level' => 'senior',
                'description' => 'Handle financial records and reporting',
                'min_salary' => 700000,
                'max_salary' => 1000000,
                'created_by' => 1,
            ],
            // Human Resources
            [
                'title' => 'HR Specialist',
                'title_ar' => 'أخصائي موارد بشرية',
                'code' => 'HR-001',
                'department_id' => 6,
                'level' => 'senior',
                'description' => 'Manage HR operations and employee relations',
                'min_salary' => 600000,
                'max_salary' => 900000,
                'created_by' => 1,
            ],
        ];

        foreach ($positions as $positionData) {
            \App\Modules\HR\Models\Position::create($positionData);
        }
    }

    /**
     * Create employees
     */
    private function createEmployees()
    {
        $employees = [
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Mansouri',
                'first_name_ar' => 'أحمد',
                'last_name_ar' => 'المنصوري',
                'email' => 'ahmed.mansouri@maxcon.com',
                'phone' => '+964-1-777-1001',
                'mobile' => '+964-770-777-1001',
                'national_id' => '19850101001',
                'date_of_birth' => '1985-01-01',
                'gender' => 'male',
                'marital_status' => 'married',
                'address' => 'Al-Mansour District, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'emergency_contact_name' => 'Fatima Al-Mansouri',
                'emergency_contact_phone' => '+964-770-777-1002',
                'emergency_contact_relationship' => 'Wife',
                'department_id' => 1,
                'position_id' => 1,
                'hire_date' => '2020-01-15',
                'employment_type' => 'full_time',
                'basic_salary' => 2500000,
                'bank_name' => 'Rafidain Bank',
                'bank_account' => '1234567890',
                'created_by' => 1,
            ],
            [
                'first_name' => 'Layla',
                'last_name' => 'Al-Zahra',
                'first_name_ar' => 'ليلى',
                'last_name_ar' => 'الزهراء',
                'email' => 'layla.zahra@maxcon.com',
                'phone' => '+964-1-777-1003',
                'mobile' => '+964-770-777-1003',
                'national_id' => '19900315002',
                'date_of_birth' => '1990-03-15',
                'gender' => 'female',
                'marital_status' => 'single',
                'address' => 'Karrada District, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'emergency_contact_name' => 'Omar Al-Zahra',
                'emergency_contact_phone' => '+964-770-777-1004',
                'emergency_contact_relationship' => 'Brother',
                'department_id' => 3,
                'position_id' => 5,
                'manager_id' => 1,
                'hire_date' => '2021-03-01',
                'employment_type' => 'full_time',
                'basic_salary' => 1500000,
                'bank_name' => 'Rasheed Bank',
                'bank_account' => '2345678901',
                'created_by' => 1,
            ],
            [
                'first_name' => 'Mohammed',
                'last_name' => 'Al-Baghdadi',
                'first_name_ar' => 'محمد',
                'last_name_ar' => 'البغدادي',
                'email' => 'mohammed.baghdadi@maxcon.com',
                'phone' => '+964-1-777-1005',
                'mobile' => '+964-770-777-1005',
                'national_id' => '19880720003',
                'date_of_birth' => '1988-07-20',
                'gender' => 'male',
                'marital_status' => 'married',
                'address' => 'Sadr City, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'emergency_contact_name' => 'Zainab Al-Baghdadi',
                'emergency_contact_phone' => '+964-770-777-1006',
                'emergency_contact_relationship' => 'Wife',
                'department_id' => 3,
                'position_id' => 6,
                'manager_id' => 2,
                'hire_date' => '2021-06-15',
                'employment_type' => 'full_time',
                'basic_salary' => 1000000,
                'bank_name' => 'Commercial Bank of Iraq',
                'bank_account' => '3456789012',
                'created_by' => 1,
            ],
            [
                'first_name' => 'Sara',
                'last_name' => 'Al-Kuwaiti',
                'first_name_ar' => 'سارة',
                'last_name_ar' => 'الكويتي',
                'email' => 'sara.kuwaiti@maxcon.com',
                'phone' => '+964-1-777-1007',
                'mobile' => '+964-770-777-1007',
                'national_id' => '19920510004',
                'date_of_birth' => '1992-05-10',
                'gender' => 'female',
                'marital_status' => 'single',
                'address' => 'Jadiriya District, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'emergency_contact_name' => 'Khalid Al-Kuwaiti',
                'emergency_contact_phone' => '+964-770-777-1008',
                'emergency_contact_relationship' => 'Father',
                'department_id' => 2,
                'position_id' => 3,
                'manager_id' => 1,
                'hire_date' => '2022-01-10',
                'employment_type' => 'full_time',
                'basic_salary' => 1300000,
                'bank_name' => 'Iraqi Islamic Bank',
                'bank_account' => '4567890123',
                'created_by' => 1,
            ],
            [
                'first_name' => 'Hassan',
                'last_name' => 'Al-Basri',
                'first_name_ar' => 'حسن',
                'last_name_ar' => 'البصري',
                'email' => 'hassan.basri@maxcon.com',
                'phone' => '+964-1-777-1009',
                'mobile' => '+964-770-777-1009',
                'national_id' => '19870825005',
                'date_of_birth' => '1987-08-25',
                'gender' => 'male',
                'marital_status' => 'married',
                'address' => 'Dora District, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'emergency_contact_name' => 'Maryam Al-Basri',
                'emergency_contact_phone' => '+964-770-777-1010',
                'emergency_contact_relationship' => 'Wife',
                'department_id' => 4,
                'position_id' => 8,
                'manager_id' => 1,
                'hire_date' => '2020-09-01',
                'employment_type' => 'full_time',
                'basic_salary' => 1000000,
                'bank_name' => 'Zain Iraq Islamic Bank',
                'bank_account' => '5678901234',
                'created_by' => 1,
            ],
        ];

        foreach ($employees as $employeeData) {
            \App\Modules\HR\Models\Employee::create($employeeData);
        }

        // Update department managers
        \App\Modules\HR\Models\Department::where('id', 1)->update(['manager_id' => 1]);
        \App\Modules\HR\Models\Department::where('id', 2)->update(['manager_id' => 4]);
        \App\Modules\HR\Models\Department::where('id', 3)->update(['manager_id' => 2]);
        \App\Modules\HR\Models\Department::where('id', 4)->update(['manager_id' => 5]);
    }

    /**
     * Create sample attendance records
     */
    private function createAttendanceRecords()
    {
        $employees = \App\Modules\HR\Models\Employee::all();
        $startDate = now()->startOfMonth();
        $endDate = now();

        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // Skip weekends (Friday and Saturday in Iraq)
                if (!$currentDate->isFriday() && !$currentDate->isSaturday()) {
                    // 90% chance of attendance
                    if (rand(1, 100) <= 90) {
                        $checkInTime = $currentDate->copy()->setTime(8, rand(0, 30)); // 8:00-8:30 AM
                        $checkOutTime = $currentDate->copy()->setTime(16, rand(30, 59)); // 4:30-4:59 PM

                        // Add some variation for late arrivals
                        if (rand(1, 100) <= 15) { // 15% chance of being late
                            $checkInTime->addMinutes(rand(15, 60));
                        }

                        // Add some overtime
                        if (rand(1, 100) <= 20) { // 20% chance of overtime
                            $checkOutTime->addHours(rand(1, 3));
                        }

                        \App\Modules\HR\Models\Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $currentDate->toDateString(),
                            'check_in_time' => $checkInTime,
                            'check_out_time' => $checkOutTime,
                            'created_by' => 1,
                        ]);
                    }
                }

                $currentDate->addDay();
            }
        }
    }
}
