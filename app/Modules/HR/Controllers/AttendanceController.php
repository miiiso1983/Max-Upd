<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\HR\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['employee.department', 'employee.position']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->forEmployee($request->get('employee_id'));
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->get('department_id'));
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->get('date_from'), $request->get('date_to'));
        } elseif ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        } else {
            // Default to current month
            $query->dateRange(now()->startOfMonth(), now()->endOfMonth());
        }

        $attendance = $query->orderBy('date', 'desc')
                            ->orderBy('check_in_time', 'desc')
                            ->paginate(20);

        // Add calculated fields (these are already computed by the model accessors)
        $attendance->getCollection()->each(function ($record) {
            // Force calculation of computed properties
            $record->append(['formatted_working_hours', 'total_hours']);
        });

        // Get statistics for today
        $stats = [
            'present_today' => Attendance::whereDate('date', today())->where('status', 'present')->count(),
            'late_today' => Attendance::whereDate('date', today())->where('status', 'late')->count(),
            'absent_today' => Attendance::whereDate('date', today())->where('status', 'absent')->count(),
            'attendance_rate' => $this->calculateAttendanceRate(),
            'avg_work_hours' => $this->calculateAverageWorkHours(),
        ];

        $employees = Employee::active()->get(['id', 'employee_id', 'first_name', 'last_name', 'first_name_ar', 'last_name_ar', 'department_id']);

        $filters = [
            'employees' => $employees,
            'statuses' => Attendance::getStatuses(),
            'statuses_ar' => Attendance::getStatusesAr(),
        ];

        // Handle Excel export
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportToExcel($query, $request);
        }

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'attendances' => $attendance,
                'stats' => $stats,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('hr.attendance.index', compact('attendance', 'stats', 'employees', 'filters'));
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i|after:break_start_time',
            'status' => 'nullable|in:' . implode(',', array_keys(Attendance::getStatuses())),
            'notes' => 'nullable|string',
        ]);

        // Check if attendance already exists for this employee and date
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
                                       ->where('date', $validated['date'])
                                       ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Attendance record already exists for this employee and date'
            ], 422);
        }

        // Convert time strings to full datetime
        if ($validated['check_in_time']) {
            $validated['check_in_time'] = Carbon::parse($validated['date'] . ' ' . $validated['check_in_time']);
        }
        if ($validated['check_out_time']) {
            $validated['check_out_time'] = Carbon::parse($validated['date'] . ' ' . $validated['check_out_time']);
        }
        if ($validated['break_start_time']) {
            $validated['break_start_time'] = Carbon::parse($validated['date'] . ' ' . $validated['break_start_time']);
        }
        if ($validated['break_end_time']) {
            $validated['break_end_time'] = Carbon::parse($validated['date'] . ' ' . $validated['break_end_time']);
        }

        $validated['created_by'] = Auth::id() ?? 1;

        $attendance = Attendance::create($validated);

        return response()->json([
            'message' => 'Attendance record created successfully',
            'attendance' => $attendance->load('employee')
        ], 201);
    }

    /**
     * Display the specified attendance record
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['employee.department', 'employee.position', 'approver', 'creator']);

        return response()->json([
            'attendance' => $attendance
        ]);
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i|after:break_start_time',
            'status' => 'nullable|in:' . implode(',', array_keys(Attendance::getStatuses())),
            'notes' => 'nullable|string',
        ]);

        // Convert time strings to full datetime
        if (isset($validated['check_in_time'])) {
            $validated['check_in_time'] = $validated['check_in_time'] ? 
                Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['check_in_time']) : null;
        }
        if (isset($validated['check_out_time'])) {
            $validated['check_out_time'] = $validated['check_out_time'] ? 
                Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['check_out_time']) : null;
        }
        if (isset($validated['break_start_time'])) {
            $validated['break_start_time'] = $validated['break_start_time'] ? 
                Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['break_start_time']) : null;
        }
        if (isset($validated['break_end_time'])) {
            $validated['break_end_time'] = $validated['break_end_time'] ? 
                Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['break_end_time']) : null;
        }

        $attendance->update($validated);

        return response()->json([
            'message' => 'Attendance record updated successfully',
            'attendance' => $attendance->fresh()->load('employee')
        ]);
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف سجل الحضور بنجاح'
        ]);
    }

    /**
     * Check in employee
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_in_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $date = now()->toDateString();
        $checkInTime = $validated['check_in_time'] ?? now()->format('H:i');

        // Check if already checked in today
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
                                       ->where('date', $date)
                                       ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Employee already checked in today'
            ], 422);
        }

        $attendance = Attendance::create([
            'employee_id' => $validated['employee_id'],
            'date' => $date,
            'check_in_time' => Carbon::parse($date . ' ' . $checkInTime),
            'notes' => $validated['notes'],
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'message' => 'Check-in recorded successfully',
            'attendance' => $attendance->load('employee')
        ], 201);
    }

    /**
     * Check out employee
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $date = now()->toDateString();
        $checkOutTime = $validated['check_out_time'] ?? now()->format('H:i');

        // Find today's attendance record
        $attendance = Attendance::where('employee_id', $validated['employee_id'])
                                ->where('date', $date)
                                ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No check-in record found for today'
            ], 422);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'message' => 'Employee already checked out today'
            ], 422);
        }

        $attendance->update([
            'check_out_time' => Carbon::parse($date . ' ' . $checkOutTime),
            'notes' => $attendance->notes . ($validated['notes'] ? "\n" . $validated['notes'] : ''),
        ]);

        return response()->json([
            'message' => 'Check-out recorded successfully',
            'attendance' => $attendance->fresh()->load('employee')
        ]);
    }

    /**
     * Get attendance summary for employee
     */
    public function employeeSummary(Employee $employee, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $attendances = $employee->attendances()
                               ->dateRange($startDate, $endDate)
                               ->orderBy('date')
                               ->get();

        $summary = [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'half_days' => $attendances->where('status', 'half_day')->count(),
            'overtime_days' => $attendances->where('status', 'overtime')->count(),
            'total_hours' => $attendances->sum('total_hours'),
            'total_overtime_hours' => $attendances->sum('overtime_minutes') / 60,
            'total_late_minutes' => $attendances->sum('late_minutes'),
            'attendance_rate' => $attendances->count() > 0 ? 
                ($attendances->whereIn('status', ['present', 'late', 'overtime'])->count() / $attendances->count()) * 100 : 0,
        ];

        return response()->json([
            'employee' => $employee,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_records' => Attendance::dateRange($startDate, $endDate)->count(),
            'by_status' => Attendance::dateRange($startDate, $endDate)
                                    ->selectRaw('status, COUNT(*) as count')
                                    ->groupBy('status')
                                    ->get(),
            'average_working_hours' => Attendance::dateRange($startDate, $endDate)->avg('total_hours'),
            'total_overtime_hours' => Attendance::dateRange($startDate, $endDate)->sum('overtime_minutes') / 60,
            'total_late_minutes' => Attendance::dateRange($startDate, $endDate)->sum('late_minutes'),
            'by_department' => Attendance::with('employee.department')
                                        ->dateRange($startDate, $endDate)
                                        ->get()
                                        ->groupBy('employee.department.name')
                                        ->map(function ($attendances, $department) {
                                            return [
                                                'department' => $department,
                                                'total_records' => $attendances->count(),
                                                'present_count' => $attendances->where('status', 'present')->count(),
                                                'late_count' => $attendances->where('status', 'late')->count(),
                                                'absent_count' => $attendances->where('status', 'absent')->count(),
                                            ];
                                        })
                                        ->values(),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate attendance rate for current month
     */
    private function calculateAttendanceRate()
    {
        $totalWorkingDays = now()->startOfMonth()->diffInWeekdays(now());
        $totalEmployees = Employee::active()->count();
        $expectedAttendance = $totalWorkingDays * $totalEmployees;

        if ($expectedAttendance == 0) return 0;

        $actualAttendance = Attendance::whereMonth('date', now()->month)
                                    ->whereYear('date', now()->year)
                                    ->whereIn('status', ['present', 'late'])
                                    ->count();

        return ($actualAttendance / $expectedAttendance) * 100;
    }

    /**
     * Calculate average work hours for current month
     */
    private function calculateAverageWorkHours()
    {
        return Attendance::whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                        ->whereNotNull('total_hours')
                        ->avg('total_hours') ?? 0;
    }

    /**
     * Export attendance to Excel
     */
    private function exportToExcel($query, $request)
    {
        try {
            // Get all attendance records without pagination for export
            $attendances = $query->orderBy('date', 'desc')
                                ->orderBy('check_in_time', 'desc')
                                ->get();

            // Add calculated fields
            $attendances->each(function ($record) {
                $record->append(['formatted_working_hours', 'total_hours']);
            });

            // Create Excel export
            return Excel::download(new AttendanceExport($attendances, $request->all()), 'attendance_' . now()->format('Y-m-d_H-i-s') . '.xlsx');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Attendance export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات: ' . $e->getMessage());
        }
    }
}
