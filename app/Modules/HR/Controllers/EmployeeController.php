<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'manager']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name_ar', 'like', "%{$search}%")
                  ->orWhere('last_name_ar', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->inDepartment($request->get('department_id'));
        }

        // Filter by position
        if ($request->has('position_id')) {
            $query->where('position_id', $request->get('position_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by employment type
        if ($request->has('employment_type')) {
            $query->byEmploymentType($request->get('employment_type'));
        }

        $employees = $query->orderBy('first_name')
                          ->paginate(20);

        // Add calculated fields (these are already computed by the model accessors)
        $employees->getCollection()->each(function ($employee) {
            // Force calculation of computed properties
            $employee->append(['full_name', 'full_name_ar', 'age', 'years_of_service']);
        });

        $filters = [
            'departments' => Department::active()->get(['id', 'name', 'name_ar']),
            'positions' => Position::active()->get(['id', 'title', 'title_ar', 'department_id']),
            'statuses' => Employee::getStatuses(),
            'statuses_ar' => Employee::getStatusesAr(),
            'employment_types' => Employee::getEmploymentTypes(),
            'employment_types_ar' => Employee::getEmploymentTypesAr(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'employees' => $employees,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('hr.employees.index', compact('employees', 'filters'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create(Request $request)
    {
        $data = [
            'departments' => Department::active()->get(['id', 'name', 'name_ar']),
            'positions' => Position::active()->get(['id', 'title', 'title_ar', 'department_id']),
            'managers' => Employee::active()->managers()->get(['id', 'first_name', 'last_name', 'first_name_ar', 'last_name_ar']),
            'statuses' => Employee::getStatuses(),
            'statuses_ar' => Employee::getStatusesAr(),
            'employment_types' => Employee::getEmploymentTypes(),
            'employment_types_ar' => Employee::getEmploymentTypesAr(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('hr.employees.create', $data);
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'first_name_ar' => 'nullable|string|max:255',
            'last_name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:' . implode(',', array_keys(Employee::getGenders())),
            'marital_status' => 'nullable|in:' . implode(',', array_keys(Employee::getMaritalStatuses())),
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'employment_type' => 'nullable|in:' . implode(',', array_keys(Employee::getEmploymentTypes())),
            'status' => 'nullable|in:' . implode(',', array_keys(Employee::getStatuses())),
            'basic_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'tax_number' => 'nullable|string|max:50',
            'social_security_number' => 'nullable|string|max:50',
            'health_insurance_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $employee = Employee::create($validated);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Employee created successfully',
                'employee' => $employee->load(['department', 'position', 'manager'])
            ], 201);
        }

        // Return redirect for web requests
        return redirect()->route('hr.employees.index')
                        ->with('success', 'تم إنشاء الموظف بنجاح');
    }

    /**
     * Display the specified employee
     */
    public function show(Request $request, Employee $employee)
    {
        $employee->load([
            'department',
            'position',
            'manager',
            'subordinates',
            'attendances' => function ($query) {
                $query->latest()->take(30);
            },
            'leaveRequests' => function ($query) {
                $query->latest()->take(10);
            },
            'payrolls' => function ($query) {
                $query->latest()->take(12);
            },
            'evaluations' => function ($query) {
                $query->latest()->take(5);
            },
            'documents'
        ]);

        // Calculated fields are available via accessors

        // Get recent attendance summary
        $attendanceSummary = [
            'total_days' => $employee->attendances()->count(),
            'present_days' => $employee->attendances()->where('status', 'present')->count(),
            'late_days' => $employee->attendances()->where('status', 'late')->count(),
            'absent_days' => $employee->attendances()->where('status', 'absent')->count(),
        ];

        // Get leave balance (simplified - would need more complex logic in real system)
        $leaveBalance = [
            'annual_leave_taken' => $employee->leaveRequests()
                                           ->where('type', 'annual')
                                           ->where('status', 'approved')
                                           ->whereYear('start_date', now()->year)
                                           ->sum('days_requested'),
            'sick_leave_taken' => $employee->leaveRequests()
                                          ->where('type', 'sick')
                                          ->where('status', 'approved')
                                          ->whereYear('start_date', now()->year)
                                          ->sum('days_requested'),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'employee' => $employee,
                'attendance_summary' => $attendanceSummary,
                'leave_balance' => $leaveBalance,
            ]);
        }

        // Return view for web requests
        return view('hr.employees.show', compact('employee', 'attendanceSummary', 'leaveBalance'));
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Request $request, Employee $employee)
    {
        $data = [
            'employee' => $employee,
            'departments' => Department::active()->get(['id', 'name', 'name_ar']),
            'positions' => Position::active()->get(['id', 'title', 'title_ar', 'department_id']),
            'managers' => Employee::active()->managers()->where('id', '!=', $employee->id)->get(['id', 'first_name', 'last_name', 'first_name_ar', 'last_name_ar']),
            'statuses' => Employee::getStatuses(),
            'statuses_ar' => Employee::getStatusesAr(),
            'employment_types' => Employee::getEmploymentTypes(),
            'employment_types_ar' => Employee::getEmploymentTypesAr(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('hr.employees.edit', $data);
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'first_name_ar' => 'nullable|string|max:255',
            'last_name_ar' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:' . implode(',', array_keys(Employee::getGenders())),
            'marital_status' => 'nullable|in:' . implode(',', array_keys(Employee::getMaritalStatuses())),
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after:hire_date',
            'employment_type' => 'nullable|in:' . implode(',', array_keys(Employee::getEmploymentTypes())),
            'status' => 'nullable|in:' . implode(',', array_keys(Employee::getStatuses())),
            'basic_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'tax_number' => 'nullable|string|max:50',
            'social_security_number' => 'nullable|string|max:50',
            'health_insurance_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $employee->update($validated);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Employee updated successfully',
                'employee' => $employee->fresh()->load(['department', 'position', 'manager'])
            ]);
        }

        // Return redirect for web requests
        return redirect()->route('hr.employees.show', $employee)
                        ->with('success', 'تم تحديث بيانات الموظف بنجاح');
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Request $request, Employee $employee)
    {
        // Check if employee has dependent records
        if ($employee->subordinates()->exists()) {
            $message = 'Cannot delete employee who manages other employees';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->back()->with('error', 'لا يمكن حذف موظف يدير موظفين آخرين');
        }

        if ($employee->attendances()->exists() || $employee->payrolls()->exists()) {
            $message = 'Cannot delete employee with attendance or payroll records. Consider marking as terminated instead.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->back()->with('error', 'لا يمكن حذف موظف له سجلات حضور أو رواتب. يُنصح بتعيين حالته كمنتهي الخدمة');
        }

        $employee->delete();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Employee deleted successfully']);
        }

        // Return redirect for web requests
        return redirect()->route('hr.employees.index')
                        ->with('success', 'تم حذف الموظف بنجاح');
    }

    /**
     * Get employee statistics
     */
    public function statistics(Employee $employee, Request $request)
    {
        $year = $request->get('year', now()->year);

        $stats = [
            'attendance' => [
                'total_days' => $employee->attendances()->whereYear('date', $year)->count(),
                'present_days' => $employee->attendances()->whereYear('date', $year)->where('status', 'present')->count(),
                'late_days' => $employee->attendances()->whereYear('date', $year)->where('status', 'late')->count(),
                'absent_days' => $employee->attendances()->whereYear('date', $year)->where('status', 'absent')->count(),
                'overtime_hours' => $employee->attendances()->whereYear('date', $year)->sum('overtime_minutes') / 60,
            ],
            'leave' => [
                'annual_leave_taken' => $employee->leaveRequests()
                                               ->where('type', 'annual')
                                               ->where('status', 'approved')
                                               ->whereYear('start_date', $year)
                                               ->sum('days_requested'),
                'sick_leave_taken' => $employee->leaveRequests()
                                              ->where('type', 'sick')
                                              ->where('status', 'approved')
                                              ->whereYear('start_date', $year)
                                              ->sum('days_requested'),
                'total_leave_taken' => $employee->leaveRequests()
                                               ->where('status', 'approved')
                                               ->whereYear('start_date', $year)
                                               ->sum('days_requested'),
            ],
            'payroll' => [
                'total_gross_salary' => $employee->payrolls()->whereYear('pay_period_start', $year)->sum('gross_salary'),
                'total_net_salary' => $employee->payrolls()->whereYear('pay_period_start', $year)->sum('net_salary'),
                'total_deductions' => $employee->payrolls()->whereYear('pay_period_start', $year)->sum('total_deductions'),
                'average_monthly_salary' => $employee->payrolls()->whereYear('pay_period_start', $year)->avg('net_salary'),
            ],
            'performance' => [
                'latest_evaluation' => $employee->evaluations()->latest()->first(),
                'average_rating' => $employee->evaluations()->whereYear('evaluation_period_start', $year)->avg('overall_rating'),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Download Excel template for employee import
     */
    public function downloadTemplate()
    {
        $headers = [
            'employee_id' => 'رقم الموظف',
            'first_name' => 'الاسم الأول (مطلوب)',
            'last_name' => 'الاسم الأخير (مطلوب)',
            'first_name_ar' => 'الاسم الأول بالعربي',
            'last_name_ar' => 'الاسم الأخير بالعربي',
            'email' => 'البريد الإلكتروني (مطلوب)',
            'phone' => 'رقم الهاتف',
            'mobile' => 'رقم الجوال',
            'national_id' => 'رقم الهوية',
            'passport_number' => 'رقم جواز السفر',
            'date_of_birth' => 'تاريخ الميلاد (YYYY-MM-DD)',
            'gender' => 'الجنس (male/female)',
            'marital_status' => 'الحالة الاجتماعية (single/married/divorced/widowed)',
            'nationality' => 'الجنسية',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'governorate' => 'المحافظة',
            'emergency_contact_name' => 'اسم جهة الاتصال الطارئ',
            'emergency_contact_phone' => 'هاتف جهة الاتصال الطارئ',
            'emergency_contact_relationship' => 'صلة القرابة',
            'department_name' => 'اسم القسم',
            'position_title' => 'المسمى الوظيفي',
            'hire_date' => 'تاريخ التوظيف (مطلوب) (YYYY-MM-DD)',
            'employment_type' => 'نوع التوظيف (full_time/part_time/contract/intern)',
            'status' => 'الحالة (active/inactive/terminated/suspended)',
            'basic_salary' => 'الراتب الأساسي',
            'hourly_rate' => 'الأجر بالساعة',
            'currency' => 'العملة (IQD/USD/EUR)',
            'bank_name' => 'اسم البنك',
            'bank_account' => 'رقم الحساب البنكي',
            'iban' => 'رقم IBAN',
            'tax_number' => 'الرقم الضريبي',
            'social_security_number' => 'رقم الضمان الاجتماعي',
            'health_insurance_number' => 'رقم التأمين الصحي',
            'notes' => 'ملاحظات',
        ];

        // Sample data
        $sampleData = [
            [
                'EMP001',
                'أحمد',
                'محمد',
                'Ahmed',
                'Mohammed',
                'ahmed.mohammed@company.com',
                '07701234567',
                '07701234567',
                '19900101123',
                'A12345678',
                '1990-01-01',
                'male',
                'married',
                'Iraqi',
                'بغداد - الكرادة',
                'بغداد',
                'بغداد',
                'فاطمة محمد',
                '07709876543',
                'زوجة',
                'تقنية المعلومات',
                'مطور برمجيات',
                '2024-01-15',
                'full_time',
                'active',
                '1500000',
                '',
                'IQD',
                'بنك بغداد',
                '123456789',
                'IQ12BBAG1234567890123456',
                'TAX123456',
                'SS123456',
                'HI123456',
                'موظف ممتاز'
            ]
        ];

        // Create Excel file
        $filename = 'employee_import_template_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($headers, $sampleData) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Arabic display
            fwrite($handle, "\xEF\xBB\xBF");

            // Write headers
            fputcsv($handle, array_values($headers));

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import employees from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'update_existing' => 'boolean',
            'send_welcome_email' => 'boolean',
            'validate_only' => 'boolean',
        ]);

        try {
            $file = $request->file('excel_file');
            $updateExisting = $request->boolean('update_existing');
            $sendWelcomeEmail = $request->boolean('send_welcome_email');
            $validateOnly = $request->boolean('validate_only');

            // Read Excel file
            $data = $this->readExcelFile($file);

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الملف فارغ أو لا يحتوي على بيانات صحيحة'
                ], 400);
            }

            // Validate data
            $validationResult = $this->validateImportData($data);

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'توجد أخطاء في البيانات',
                    'errors' => $validationResult['errors']
                ], 400);
            }

            if ($validateOnly) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم التحقق من البيانات بنجاح. جميع البيانات صحيحة.',
                    'total_rows' => count($data)
                ]);
            }

            // Process import
            $result = $this->processImport($data, $updateExisting, $sendWelcomeEmail);

            return response()->json([
                'success' => true,
                'message' => 'تم استيراد البيانات بنجاح',
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Read Excel file and return data array
     */
    private function readExcelFile($file)
    {
        $data = [];
        $headers = [];

        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $rowIndex = 0;

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($rowIndex === 0) {
                    // First row contains headers
                    $headers = $row;
                } else {
                    // Data rows
                    if (count($row) >= count($headers)) {
                        $rowData = array_combine($headers, array_slice($row, 0, count($headers)));

                        // Skip empty rows
                        if (!empty(array_filter($rowData))) {
                            $data[] = $rowData;
                        }
                    }
                }
                $rowIndex++;
            }

            fclose($handle);
        }

        return $data;
    }

    /**
     * Validate import data
     */
    private function validateImportData($data)
    {
        $errors = [];
        $valid = true;

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header row

            // Required fields validation
            if (empty($row['first_name'])) {
                $errors[] = "الصف {$rowNumber}: الاسم الأول مطلوب";
                $valid = false;
            }

            if (empty($row['last_name'])) {
                $errors[] = "الصف {$rowNumber}: الاسم الأخير مطلوب";
                $valid = false;
            }

            if (empty($row['email'])) {
                $errors[] = "الصف {$rowNumber}: البريد الإلكتروني مطلوب";
                $valid = false;
            } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "الصف {$rowNumber}: البريد الإلكتروني غير صحيح";
                $valid = false;
            }

            if (empty($row['hire_date'])) {
                $errors[] = "الصف {$rowNumber}: تاريخ التوظيف مطلوب";
                $valid = false;
            } elseif (!strtotime($row['hire_date'])) {
                $errors[] = "الصف {$rowNumber}: تاريخ التوظيف غير صحيح";
                $valid = false;
            }

            // Optional field validations
            if (!empty($row['date_of_birth']) && !strtotime($row['date_of_birth'])) {
                $errors[] = "الصف {$rowNumber}: تاريخ الميلاد غير صحيح";
                $valid = false;
            }

            if (!empty($row['gender']) && !in_array($row['gender'], ['male', 'female'])) {
                $errors[] = "الصف {$rowNumber}: الجنس يجب أن يكون male أو female";
                $valid = false;
            }

            if (!empty($row['employment_type']) && !in_array($row['employment_type'], ['full_time', 'part_time', 'contract', 'intern'])) {
                $errors[] = "الصف {$rowNumber}: نوع التوظيف غير صحيح";
                $valid = false;
            }

            if (!empty($row['status']) && !in_array($row['status'], ['active', 'inactive', 'terminated', 'suspended'])) {
                $errors[] = "الصف {$rowNumber}: حالة الموظف غير صحيحة";
                $valid = false;
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * Process the import and create/update employees
     */
    private function processImport($data, $updateExisting, $sendWelcomeEmail)
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $row) {
            try {
                // Check if employee exists
                $existingEmployee = Employee::where('email', $row['email'])->first();

                if ($existingEmployee && !$updateExisting) {
                    $skipped++;
                    continue;
                }

                // Prepare employee data
                $employeeData = $this->prepareEmployeeData($row);

                if ($existingEmployee && $updateExisting) {
                    // Update existing employee
                    $existingEmployee->update($employeeData);
                    $updated++;
                } else {
                    // Create new employee
                    Employee::create($employeeData);
                    $created++;

                    // TODO: Send welcome email if requested
                    if ($sendWelcomeEmail) {
                        // Implement welcome email sending
                    }
                }

            } catch (\Exception $e) {
                $skipped++;
                // Log error for debugging
                \Log::error('Employee import error: ' . $e->getMessage(), ['row' => $row]);
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped
        ];
    }

    /**
     * Prepare employee data from Excel row
     */
    private function prepareEmployeeData($row)
    {
        // Find department and position
        $department = null;
        $position = null;

        if (!empty($row['department_name'])) {
            $department = Department::where('name', $row['department_name'])
                                  ->orWhere('name_ar', $row['department_name'])
                                  ->first();
        }

        if (!empty($row['position_title'])) {
            $position = Position::where('title', $row['position_title'])
                               ->orWhere('title_ar', $row['position_title'])
                               ->first();
        }

        return [
            'employee_id' => $row['employee_id'] ?: $this->generateEmployeeId(),
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'first_name_ar' => $row['first_name_ar'] ?: null,
            'last_name_ar' => $row['last_name_ar'] ?: null,
            'email' => $row['email'],
            'phone' => $row['phone'] ?: null,
            'mobile' => $row['mobile'] ?: null,
            'national_id' => $row['national_id'] ?: null,
            'passport_number' => $row['passport_number'] ?: null,
            'date_of_birth' => !empty($row['date_of_birth']) ? date('Y-m-d', strtotime($row['date_of_birth'])) : null,
            'gender' => $row['gender'] ?: null,
            'marital_status' => $row['marital_status'] ?: null,
            'nationality' => $row['nationality'] ?: 'Iraqi',
            'address' => $row['address'] ?: null,
            'city' => $row['city'] ?: null,
            'governorate' => $row['governorate'] ?: null,
            'emergency_contact_name' => $row['emergency_contact_name'] ?: null,
            'emergency_contact_phone' => $row['emergency_contact_phone'] ?: null,
            'emergency_contact_relationship' => $row['emergency_contact_relationship'] ?: null,
            'department_id' => $department ? $department->id : null,
            'position_id' => $position ? $position->id : null,
            'hire_date' => date('Y-m-d', strtotime($row['hire_date'])),
            'employment_type' => $row['employment_type'] ?: 'full_time',
            'status' => $row['status'] ?: 'active',
            'basic_salary' => !empty($row['basic_salary']) ? floatval($row['basic_salary']) : null,
            'hourly_rate' => !empty($row['hourly_rate']) ? floatval($row['hourly_rate']) : null,
            'currency' => $row['currency'] ?: 'IQD',
            'bank_name' => $row['bank_name'] ?: null,
            'bank_account' => $row['bank_account'] ?: null,
            'iban' => $row['iban'] ?: null,
            'tax_number' => $row['tax_number'] ?: null,
            'social_security_number' => $row['social_security_number'] ?: null,
            'health_insurance_number' => $row['health_insurance_number'] ?: null,
            'notes' => $row['notes'] ?: null,
            'created_by' => auth()->id(),
        ];
    }

    /**
     * Generate unique employee ID
     */
    private function generateEmployeeId()
    {
        $prefix = 'EMP';
        $lastEmployee = Employee::where('employee_id', 'like', $prefix . '%')
                               ->orderBy('employee_id', 'desc')
                               ->first();

        if ($lastEmployee) {
            $lastNumber = intval(substr($lastEmployee->employee_id, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
