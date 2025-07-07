<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\HR\Exports\PayrollExport;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls
     */
    public function index(Request $request)
    {
        $query = Payroll::with(['employee.department', 'employee.position']);

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('pay_period_start')) {
            $query->where('pay_period_start', '>=', $request->pay_period_start);
        }

        if ($request->filled('pay_period_end')) {
            $query->where('pay_period_end', '<=', $request->pay_period_end);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->orderBy('pay_period_start', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        // Calculate statistics
        $stats = [
            'total_salaries' => Payroll::where('status', 'paid')->sum('net_salary'),
            'total_employees' => Employee::active()->count(),
            'average_salary' => Payroll::where('status', 'paid')->avg('net_salary') ?: 0,
            'pending_payrolls' => Payroll::where('status', 'pending')->count(),
        ];

        $departments = Department::active()->get(['id', 'name_ar']);
        $employees = Employee::active()->get(['id', 'first_name', 'last_name', 'first_name_ar', 'last_name_ar']);

        $filters = $request->only(['employee_id', 'department_id', 'pay_period_start', 'pay_period_end', 'status']);

        // Handle Excel export
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportToExcel($query, $filters);
        }

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'payrolls' => $payrolls,
                'stats' => $stats,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('hr.payroll.index', compact('payrolls', 'stats', 'departments', 'employees', 'filters'));
    }

    /**
     * Show the form for creating a new payroll
     */
    public function create(Request $request)
    {
        $employees = Employee::active()
                           ->with(['department', 'position'])
                           ->select('id', 'employee_id', 'first_name', 'last_name', 'first_name_ar', 'last_name_ar', 'basic_salary', 'department_id', 'position_id')
                           ->get();

        $departments = Department::active()->get(['id', 'name', 'name_ar']);

        $data = [
            'employees' => $employees,
            'departments' => $departments,
            'pay_period_start' => $request->get('pay_period_start', now()->startOfMonth()->format('Y-m-d')),
            'pay_period_end' => $request->get('pay_period_end', now()->endOfMonth()->format('Y-m-d')),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('hr.payroll.create', $data);
    }

    /**
     * Store a newly created payroll
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'social_security_deduction' => 'nullable|numeric|min:0',
            'health_insurance_deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Calculate amounts
            $basicSalary = $request->basic_salary;
            $allowances = $request->allowances ?: 0;
            $bonuses = $request->bonuses ?: 0;
            $overtimeHours = $request->overtime_hours ?: 0;
            $overtimeRate = $request->overtime_rate ?: ($basicSalary / 160);
            $overtimeAmount = $overtimeHours * $overtimeRate;

            $grossSalary = $basicSalary + $allowances + $bonuses + $overtimeAmount;

            $taxDeduction = $request->tax_deduction ?: 0;
            $socialSecurityDeduction = $request->social_security_deduction ?: 0;
            $healthInsuranceDeduction = $request->health_insurance_deduction ?: 0;
            $otherDeductions = $request->other_deductions ?: 0;

            $totalDeductions = $taxDeduction + $socialSecurityDeduction + $healthInsuranceDeduction + $otherDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            $payroll = Payroll::create([
                'employee_id' => $request->employee_id,
                'pay_period_start' => $request->pay_period_start,
                'pay_period_end' => $request->pay_period_end,
                'basic_salary' => $basicSalary,
                'overtime_hours' => $overtimeHours,
                'overtime_rate' => $overtimeRate,
                'overtime_amount' => $overtimeAmount,
                'allowances' => $allowances,
                'bonuses' => $bonuses,
                'gross_salary' => $grossSalary,
                'tax_deduction' => $taxDeduction,
                'social_security_deduction' => $socialSecurityDeduction,
                'health_insurance_deduction' => $healthInsuranceDeduction,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'currency' => 'IQD',
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => 1, // Default to admin user
            ]);

            DB::commit();

            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Payroll created successfully',
                    'payroll' => $payroll->load('employee')
                ], 201);
            }

            // Return redirect for web requests
            return redirect()->route('hr.payroll.show', $payroll)
                           ->with('success', 'تم إنشاء كشف الراتب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            Log::error('Payroll creation error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error creating payroll: ' . $e->getMessage()
                ], 500);
            }

            // Return redirect for web requests
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'حدث خطأ أثناء إنشاء كشف الراتب: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payroll
     */
    public function show(Payroll $payroll, Request $request)
    {
        $payroll->load([
            'employee.department',
            'employee.position',
            'creator',
            'updater'
        ]);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'payroll' => $payroll
            ]);
        }

        // Return view for web requests
        return view('hr.payroll.show', compact('payroll'));
    }

    /**
     * Show the form for editing the specified payroll
     */
    public function edit(Payroll $payroll, Request $request)
    {
        if ($payroll->status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot edit paid payroll'], 403);
            }
            return redirect()->back()->with('error', 'لا يمكن تعديل كشف راتب مدفوع');
        }

        $payroll->load(['employee.department', 'employee.position']);

        $employees = Employee::active()
                           ->with(['department', 'position'])
                           ->get(['id', 'employee_id', 'first_name', 'last_name', 'first_name_ar', 'last_name_ar', 'basic_salary', 'department_id', 'position_id']);

        $departments = Department::active()->get(['id', 'name', 'name_ar']);

        $data = [
            'payroll' => $payroll,
            'employees' => $employees,
            'departments' => $departments,
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('hr.payroll.edit', $data);
    }

    /**
     * Update the specified payroll
     */
    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot edit paid payroll'], 403);
            }
            return redirect()->back()->with('error', 'لا يمكن تعديل كشف راتب مدفوع');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'social_security_deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Recalculate payroll
            $basicSalary = $request->basic_salary;
            $allowances = $request->allowances ?: 0;
            $bonuses = $request->bonuses ?: 0;
            $overtimeHours = $request->overtime_hours ?: 0;
            $overtimeRate = $request->overtime_rate ?: ($basicSalary / 160);
            $overtimeAmount = $overtimeHours * $overtimeRate;

            $grossSalary = $basicSalary + $allowances + $bonuses + $overtimeAmount;

            $taxDeduction = $request->tax_deduction ?: 0;
            $socialSecurityDeduction = $request->social_security_deduction ?: 0;
            $healthInsuranceDeduction = $request->health_insurance_deduction ?: 0;
            $otherDeductions = $request->other_deductions ?: 0;

            $totalDeductions = $taxDeduction + $socialSecurityDeduction + $healthInsuranceDeduction + $otherDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            $payroll->update([
                'employee_id' => $request->employee_id,
                'pay_period_start' => $request->pay_period_start,
                'pay_period_end' => $request->pay_period_end,
                'basic_salary' => $basicSalary,
                'overtime_hours' => $overtimeHours,
                'overtime_rate' => $overtimeRate,
                'overtime_amount' => $overtimeAmount,
                'allowances' => $allowances,
                'bonuses' => $bonuses,
                'gross_salary' => $grossSalary,
                'tax_deduction' => $taxDeduction,
                'social_security_deduction' => $socialSecurityDeduction,
                'health_insurance_deduction' => $healthInsuranceDeduction,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'notes' => $request->notes,
                'updated_by' => 1, // Default to admin user
            ]);

            DB::commit();

            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Payroll updated successfully',
                    'payroll' => $payroll->load('employee')
                ]);
            }

            // Return redirect for web requests
            return redirect()->route('hr.payroll.show', $payroll)
                           ->with('success', 'تم تحديث كشف الراتب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error updating payroll: ' . $e->getMessage()
                ], 500);
            }

            // Return redirect for web requests
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'حدث خطأ أثناء تحديث كشف الراتب: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payroll
     */
    public function destroy(Payroll $payroll, Request $request)
    {
        if ($payroll->status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot delete paid payroll'], 403);
            }
            return redirect()->back()->with('error', 'لا يمكن حذف كشف راتب مدفوع');
        }

        $payroll->delete();

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Payroll deleted successfully']);
        }

        // Return redirect for web requests
        return redirect()->route('hr.payroll.index')
                        ->with('success', 'تم حذف كشف الراتب بنجاح');
    }

    /**
     * Preview employees for bulk payroll generation
     */
    public function previewBulkPayroll(Request $request)
    {
        $request->validate([
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'department_id' => 'nullable|exists:departments,id',
            'skip_existing' => 'boolean',
        ]);

        try {
            $query = Employee::active()->with(['department', 'position']);

            // Filter by department if specified
            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            $employees = $query->get();

            // Filter out employees who already have payroll for this period if skip_existing is true
            if ($request->skip_existing) {
                $employees = $employees->filter(function ($employee) use ($request) {
                    return !Payroll::where('employee_id', $employee->id)
                                  ->where('pay_period_start', $request->pay_period_start)
                                  ->where('pay_period_end', $request->pay_period_end)
                                  ->exists();
                });
            }

            // Format employee data for preview
            $employeeData = $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'name' => ($employee->first_name_ar ?: $employee->first_name) . ' ' . ($employee->last_name_ar ?: $employee->last_name),
                    'department' => optional($employee->department)->name_ar ?: optional($employee->department)->name ?: 'غير محدد',
                    'position' => optional($employee->position)->title_ar ?: optional($employee->position)->title ?: 'غير محدد',
                    'basic_salary' => (float) $employee->basic_salary,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'employees' => $employeeData,
                'count' => $employeeData->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الموظفين: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate bulk payroll for multiple employees
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'department_id' => 'nullable|exists:departments,id',
            'auto_calculate_tax' => 'boolean',
            'auto_calculate_social_security' => 'boolean',
            'auto_calculate_health_insurance' => 'boolean',
            'skip_existing' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $query = Employee::active()->with(['department', 'position']);

            // Filter by department if specified
            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            $employees = $query->get();

            // Filter out employees who already have payroll for this period if skip_existing is true
            if ($request->skip_existing) {
                $employees = $employees->filter(function ($employee) use ($request) {
                    return !Payroll::where('employee_id', $employee->id)
                                  ->where('pay_period_start', $request->pay_period_start)
                                  ->where('pay_period_end', $request->pay_period_end)
                                  ->exists();
                });
            }

            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($employees as $employee) {
                try {
                    // Calculate amounts
                    $basicSalary = $employee->basic_salary ?: 0;
                    $allowances = 0; // Default allowances
                    $bonuses = 0; // Default bonuses
                    $overtimeHours = 0; // Default overtime
                    $overtimeRate = $basicSalary / 160; // Default overtime rate
                    $overtimeAmount = $overtimeHours * $overtimeRate;

                    $grossSalary = $basicSalary + $allowances + $bonuses + $overtimeAmount;

                    // Calculate deductions
                    $taxDeduction = 0;
                    $socialSecurityDeduction = 0;
                    $healthInsuranceDeduction = 0;
                    $otherDeductions = 0;

                    if ($request->auto_calculate_tax) {
                        $taxDeduction = $this->calculateTaxDeduction($grossSalary);
                    }

                    if ($request->auto_calculate_social_security) {
                        $socialSecurityDeduction = $basicSalary * 0.05; // 5%
                    }

                    if ($request->auto_calculate_health_insurance) {
                        $healthInsuranceDeduction = $basicSalary * 0.02; // 2%
                    }

                    $totalDeductions = $taxDeduction + $socialSecurityDeduction + $healthInsuranceDeduction + $otherDeductions;
                    $netSalary = $grossSalary - $totalDeductions;

                    // Create payroll record
                    Payroll::create([
                        'employee_id' => $employee->id,
                        'pay_period_start' => $request->pay_period_start,
                        'pay_period_end' => $request->pay_period_end,
                        'basic_salary' => $basicSalary,
                        'overtime_hours' => $overtimeHours,
                        'overtime_rate' => $overtimeRate,
                        'overtime_amount' => $overtimeAmount,
                        'allowances' => $allowances,
                        'bonuses' => $bonuses,
                        'gross_salary' => $grossSalary,
                        'tax_deduction' => $taxDeduction,
                        'social_security_deduction' => $socialSecurityDeduction,
                        'health_insurance_deduction' => $healthInsuranceDeduction,
                        'other_deductions' => $otherDeductions,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'currency' => 'IQD',
                        'status' => 'draft',
                        'notes' => 'تم إنشاؤه تلقائياً - الإنشاء الجماعي',
                        'created_by' => 1, // Default to admin user
                    ]);

                    $createdCount++;

                } catch (\Exception $e) {
                    $errors[] = "خطأ في إنشاء راتب الموظف {$employee->employee_id}: " . $e->getMessage();
                    $skippedCount++;
                }
            }

            DB::commit();

            $message = "تم إنشاء {$createdCount} كشف راتب بنجاح";
            if ($skippedCount > 0) {
                $message .= " وتم تجاهل {$skippedCount} موظف";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created_count' => $createdCount,
                'skipped_count' => $skippedCount,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk payroll generation error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الرواتب: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate tax deduction based on Iraqi tax system
     */
    private function calculateTaxDeduction($grossSalary)
    {
        // Iraqi tax brackets (simplified)
        if ($grossSalary <= 250000) {
            return 0; // Tax-free threshold
        } elseif ($grossSalary <= 500000) {
            return ($grossSalary - 250000) * 0.03; // 3%
        } elseif ($grossSalary <= 1000000) {
            return 7500 + ($grossSalary - 500000) * 0.05; // 5%
        } else {
            return 32500 + ($grossSalary - 1000000) * 0.15; // 15%
        }
    }

    /**
     * Export payrolls to Excel
     */
    private function exportToExcel($query, $filters)
    {
        try {
            // Get all payrolls without pagination for export
            $payrolls = $query->with(['employee.department', 'employee.position'])
                             ->orderBy('pay_period_start', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->get();

            // Create Excel export
            return Excel::download(new PayrollExport($payrolls, $filters), 'payrolls_' . now()->format('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            Log::error('Payroll export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات: ' . $e->getMessage());
        }
    }
}
