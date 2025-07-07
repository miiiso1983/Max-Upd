<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index(Request $request)
    {
        $query = Department::with(['parent', 'manager', 'employees']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        // Filter by parent department
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        }

        // Show only root departments if requested
        if ($request->get('root_only') === 'true') {
            $query->root();
        }

        $departments = $query->withCount(['employees', 'positions'])->orderBy('name')->paginate(20);

        // Add calculated fields
        $departments->getCollection()->each(function ($department) {
            // Force calculation of computed properties
            $department->append(['active_employees_count', 'hierarchy_path', 'hierarchy_path_ar']);
        });

        $filters = [
            'statuses' => Department::getStatuses(),
            'statuses_ar' => Department::getStatusesAr(),
            'parent_departments' => Department::active()->root()->get(['id', 'name', 'name_ar']),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'departments' => $departments,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('hr.departments.index', compact('departments', 'filters'));
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255|unique:departments,code',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'parent_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'nullable|in:' . implode(',', array_keys(Department::getStatuses())),
            'budget' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        $department = Department::create($validated);

        return response()->json([
            'message' => 'Department created successfully',
            'department' => $department->load(['parent', 'manager'])
        ], 201);
    }

    /**
     * Display the specified department
     */
    public function show(Department $department)
    {
        $department->load([
            'parent',
            'children.manager',
            'manager',
            'employees.position',
            'positions'
        ]);

        // Add calculated fields
        $department->active_employees_count = $department->active_employees_count;
        $department->total_employees_count = $department->getTotalEmployeesCount();
        $department->hierarchy_path = $department->hierarchy_path;
        $department->hierarchy_path_ar = $department->hierarchy_path_ar;

        // Get department statistics
        $stats = [
            'total_employees' => $department->employees()->count(),
            'active_employees' => $department->employees()->active()->count(),
            'by_employment_type' => $department->employees()
                                              ->selectRaw('employment_type, COUNT(*) as count')
                                              ->groupBy('employment_type')
                                              ->get(),
            'by_position' => $department->employees()
                                       ->with('position')
                                       ->get()
                                       ->groupBy('position.title')
                                       ->map(function ($employees, $position) {
                                           return [
                                               'position' => $position,
                                               'count' => $employees->count()
                                           ];
                                       })
                                       ->values(),
            'average_salary' => $department->employees()->avg('basic_salary'),
            'total_budget' => $department->budget,
        ];

        return response()->json([
            'department' => $department,
            'statistics' => $stats,
        ]);
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => ['nullable', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($department) {
                    // Prevent circular reference
                    if ($value == $department->id) {
                        $fail('Department cannot be its own parent.');
                    }
                    
                    // Check if the new parent is a descendant
                    if ($value && $department->descendants()->pluck('id')->contains($value)) {
                        $fail('Cannot set a descendant department as parent.');
                    }
                },
            ],
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'nullable|in:' . implode(',', array_keys(Department::getStatuses())),
            'budget' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['updated_by'] = auth()->id();

        $department->update($validated);

        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department->fresh()->load(['parent', 'manager'])
        ]);
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department)
    {
        // Check if department has employees
        if ($department->employees()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department with employees. Please reassign employees first.'
            ], 422);
        }

        // Check if department has child departments
        if ($department->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department with sub-departments. Please reassign or delete sub-departments first.'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }

    /**
     * Get department hierarchy
     */
    public function hierarchy()
    {
        $departments = Department::with(['children.children.children', 'manager', 'employees'])
                                ->root()
                                ->active()
                                ->get();

        $hierarchy = $departments->map(function ($department) {
            return $this->buildHierarchy($department);
        });

        return response()->json([
            'hierarchy' => $hierarchy
        ]);
    }

    /**
     * Build department hierarchy recursively
     */
    private function buildHierarchy($department)
    {
        return [
            'id' => $department->id,
            'name' => $department->name,
            'name_ar' => $department->name_ar,
            'code' => $department->code,
            'manager' => $department->manager ? [
                'id' => $department->manager->id,
                'name' => $department->manager->full_name,
                'name_ar' => $department->manager->full_name_ar,
            ] : null,
            'employees_count' => $department->employees()->active()->count(),
            'children' => $department->children->map(function ($child) {
                return $this->buildHierarchy($child);
            }),
        ];
    }

    /**
     * Get department statistics
     */
    public function statistics(Department $department, Request $request)
    {
        $year = $request->get('year', now()->year);

        $stats = [
            'employees' => [
                'total' => $department->employees()->count(),
                'active' => $department->employees()->active()->count(),
                'by_employment_type' => $department->employees()
                                                  ->selectRaw('employment_type, COUNT(*) as count')
                                                  ->groupBy('employment_type')
                                                  ->get(),
                'new_hires' => $department->employees()
                                         ->whereYear('hire_date', $year)
                                         ->count(),
                'terminations' => $department->employees()
                                            ->where('status', 'terminated')
                                            ->whereYear('termination_date', $year)
                                            ->count(),
            ],
            'payroll' => [
                'total_salary_budget' => $department->employees()
                                                   ->active()
                                                   ->sum('basic_salary'),
                'average_salary' => $department->employees()
                                              ->active()
                                              ->avg('basic_salary'),
                'total_paid' => $department->employees()
                                          ->with(['payrolls' => function ($query) use ($year) {
                                              $query->whereYear('pay_period_start', $year)
                                                    ->where('status', 'paid');
                                          }])
                                          ->get()
                                          ->sum(function ($employee) {
                                              return $employee->payrolls->sum('net_salary');
                                          }),
            ],
            'attendance' => [
                'average_attendance_rate' => $this->calculateAverageAttendanceRate($department, $year),
                'total_overtime_hours' => $this->calculateTotalOvertimeHours($department, $year),
            ],
            'budget' => [
                'allocated_budget' => $department->budget,
                'budget_utilization' => $department->budget ? 
                    ($department->employees()->active()->sum('basic_salary') / $department->budget) * 100 : 0,
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Calculate average attendance rate for department
     */
    private function calculateAverageAttendanceRate($department, $year)
    {
        $employees = $department->employees()->active()->get();
        
        if ($employees->isEmpty()) {
            return 0;
        }

        $totalRate = 0;
        foreach ($employees as $employee) {
            $totalDays = $employee->attendances()->whereYear('date', $year)->count();
            $presentDays = $employee->attendances()
                                   ->whereYear('date', $year)
                                   ->whereIn('status', ['present', 'late', 'overtime'])
                                   ->count();
            
            $rate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
            $totalRate += $rate;
        }

        return $totalRate / $employees->count();
    }

    /**
     * Calculate total overtime hours for department
     */
    private function calculateTotalOvertimeHours($department, $year)
    {
        return $department->employees()
                         ->with(['attendances' => function ($query) use ($year) {
                             $query->whereYear('date', $year);
                         }])
                         ->get()
                         ->sum(function ($employee) {
                             return $employee->attendances->sum('overtime_minutes');
                         }) / 60; // Convert to hours
    }

    /**
     * Toggle department status
     */
    public function toggleStatus(Request $request, Department $department)
    {
        $department->update([
            'is_active' => !$department->is_active
        ]);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Department status updated successfully',
                'department' => $department
            ]);
        }

        // Return redirect for web requests
        return redirect()->back()
                        ->with('success', 'تم تغيير حالة القسم بنجاح');
    }

    /**
     * Export departments to Excel
     */
    public function export(Request $request)
    {
        // This would implement Excel export using Maatwebsite/Excel
        return response()->json([
            'message' => 'Excel export will be implemented with Maatwebsite/Excel',
            'download_url' => '/api/tenant/hr/departments/export'
        ]);
    }

    /**
     * Import departments from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        // This would implement Excel import using Maatwebsite/Excel
        return response()->json([
            'message' => 'Excel import will be implemented with Maatwebsite/Excel'
        ]);
    }
}
