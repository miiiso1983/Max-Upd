<?php

namespace App\Modules\SalesReps\Services;

use App\Modules\SalesReps\Models\SalesRepresentative;
use App\Modules\SalesReps\Models\RepLocationTracking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SalesRepService
{
    /**
     * Create a new sales representative
     */
    public function create(array $data): SalesRepresentative
    {
        return DB::transaction(function () use ($data) {
            // Create user account first
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password123'),
                'email_verified_at' => now(),
            ]);

            // Assign sales representative role
            $role = Role::where('name', 'sales_representative')->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Create sales representative record
            $salesRep = SalesRepresentative::create([
                'user_id' => $user->id,
                'employee_code' => $this->generateEmployeeCode(),
                'name' => $data['name'],
                'name_ar' => $data['name_ar'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'mobile' => $data['mobile'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'governorate' => $data['governorate'] ?? null,
                'hire_date' => $data['hire_date'] ?? now(),
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
                'national_id' => $data['national_id'] ?? null,
                'base_salary' => $data['base_salary'] ?? 0,
                'commission_rate' => $data['commission_rate'] ?? 0,
                'monthly_target' => $data['monthly_target'] ?? 0,
                'quarterly_target' => $data['quarterly_target'] ?? 0,
                'annual_target' => $data['annual_target'] ?? 0,
                'employment_type' => $data['employment_type'] ?? 'full_time',
                'status' => $data['status'] ?? 'active',
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'bank_details' => $data['bank_details'] ?? null,
                'notes' => $data['notes'] ?? null,
                'can_create_orders' => $data['can_create_orders'] ?? true,
                'can_collect_payments' => $data['can_collect_payments'] ?? true,
                'can_view_all_customers' => $data['can_view_all_customers'] ?? false,
                'max_discount_percentage' => $data['max_discount_percentage'] ?? 0,
                'max_order_amount' => $data['max_order_amount'] ?? 0,
                'working_hours' => $data['working_hours'] ?? null,
                'gps_settings' => $data['gps_settings'] ?? ['tracking_enabled' => true, 'accuracy_required' => 10],
                'created_by' => auth()->id(),
            ]);

            // Assign territories if provided
            if (!empty($data['territory_ids'])) {
                $this->assignTerritories($salesRep, $data['territory_ids']);
            }

            // Assign customers if provided
            if (!empty($data['customer_ids'])) {
                $this->assignCustomers($salesRep, $data['customer_ids']);
            }

            return $salesRep;
        });
    }

    /**
     * Update sales representative
     */
    public function update(SalesRepresentative $salesRep, array $data): SalesRepresentative
    {
        return DB::transaction(function () use ($salesRep, $data) {
            // Update user account if email changed
            if (isset($data['email']) && $data['email'] !== $salesRep->email) {
                $salesRep->user->update(['email' => $data['email']]);
            }

            // Update sales representative record
            $salesRep->update(array_merge($data, [
                'updated_by' => auth()->id(),
            ]));

            // Update territory assignments if provided
            if (isset($data['territory_ids'])) {
                $this->assignTerritories($salesRep, $data['territory_ids']);
            }

            // Update customer assignments if provided
            if (isset($data['customer_ids'])) {
                $this->assignCustomers($salesRep, $data['customer_ids']);
            }

            return $salesRep->fresh();
        });
    }

    /**
     * Delete sales representative (soft delete)
     */
    public function delete(SalesRepresentative $salesRep): bool
    {
        return DB::transaction(function () use ($salesRep) {
            // Deactivate user account
            $salesRep->user->update(['is_active' => false]);

            // Soft delete sales representative
            return $salesRep->delete();
        });
    }

    /**
     * Assign territories to sales representative
     */
    public function assignTerritories(SalesRepresentative $salesRep, array $territoryIds): void
    {
        $assignments = [];
        foreach ($territoryIds as $territoryId) {
            $assignments[$territoryId] = [
                'assigned_date' => now(),
                'effective_from' => now(),
                'assignment_type' => 'primary',
                'is_active' => true,
                'assigned_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $salesRep->territories()->sync($assignments);
    }

    /**
     * Assign customers to sales representative
     */
    public function assignCustomers(SalesRepresentative $salesRep, array $customerIds): void
    {
        $assignments = [];
        foreach ($customerIds as $customerId) {
            $assignments[$customerId] = [
                'assigned_date' => now(),
                'effective_from' => now(),
                'assignment_type' => 'primary',
                'is_active' => true,
                'visit_frequency_days' => 30,
                'priority' => 'medium',
                'assigned_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $salesRep->customers()->sync($assignments);
    }

    /**
     * Update sales representative location
     */
    public function updateLocation(
        SalesRepresentative $salesRep,
        float $latitude,
        float $longitude,
        ?int $accuracy = null,
        ?string $activityType = null
    ): void {
        // Update sales rep's last known location
        $salesRep->updateLocation($latitude, $longitude);

        // Create location tracking record
        RepLocationTracking::create([
            'sales_rep_id' => $salesRep->id,
            'tracked_at' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy_meters' => $accuracy,
            'activity_type' => $activityType,
            'is_working_hours' => $this->isWorkingHours($salesRep),
            'device_id' => request()->header('Device-ID'),
            'app_version' => request()->header('App-Version'),
        ]);
    }

    /**
     * Get performance data for sales representative
     */
    public function getPerformanceData(
        SalesRepresentative $salesRep,
        string $period = 'monthly',
        $startDate = null,
        $endDate = null
    ): array {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        // Get visits data
        $visits = $salesRep->visits()
                          ->whereBetween('visit_date', [$startDate, $endDate])
                          ->get();

        // Get orders data
        $orders = $salesRep->salesOrders()
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->get();

        // Get payments data
        $payments = $salesRep->paymentsCollected()
                            ->whereBetween('payment_date', [$startDate, $endDate])
                            ->get();

        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'visits' => [
                'total' => $visits->count(),
                'completed' => $visits->where('status', 'completed')->count(),
                'in_progress' => $visits->where('status', 'in_progress')->count(),
                'cancelled' => $visits->where('status', 'cancelled')->count(),
                'completion_rate' => $visits->count() > 0 ? 
                    round(($visits->where('status', 'completed')->count() / $visits->count()) * 100, 2) : 0,
            ],
            'orders' => [
                'total' => $orders->count(),
                'total_value' => $orders->sum('total_amount'),
                'average_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
            ],
            'payments' => [
                'total' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'average_amount' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            ],
            'targets' => [
                'monthly_target' => $salesRep->monthly_target,
                'achievement_rate' => $salesRep->monthly_target > 0 ? 
                    round(($orders->sum('total_amount') / $salesRep->monthly_target) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Bulk operations on sales representatives
     */
    public function bulkAction(string $action, array $salesRepIds, $supervisorId = null): array
    {
        $affected = 0;

        switch ($action) {
            case 'activate':
                $affected = SalesRepresentative::whereIn('id', $salesRepIds)
                                              ->update(['status' => 'active']);
                break;

            case 'deactivate':
                $affected = SalesRepresentative::whereIn('id', $salesRepIds)
                                              ->update(['status' => 'inactive']);
                break;

            case 'delete':
                $affected = SalesRepresentative::whereIn('id', $salesRepIds)->delete();
                break;

            case 'assign_supervisor':
                $affected = SalesRepresentative::whereIn('id', $salesRepIds)
                                              ->update(['supervisor_id' => $supervisorId]);
                break;
        }

        return [
            'action' => $action,
            'affected' => $affected,
            'sales_rep_ids' => $salesRepIds,
        ];
    }

    /**
     * Generate unique employee code
     */
    private function generateEmployeeCode(): string
    {
        $prefix = 'REP';
        $year = date('Y');
        
        do {
            $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $year . $number;
        } while (SalesRepresentative::where('employee_code', $code)->exists());

        return $code;
    }

    /**
     * Check if current time is within working hours
     */
    private function isWorkingHours(SalesRepresentative $salesRep): bool
    {
        $workingHours = $salesRep->working_hours;
        
        if (!$workingHours || !isset($workingHours['start_time']) || !isset($workingHours['end_time'])) {
            return true; // Default to working hours if not set
        }

        $currentTime = now()->format('H:i');
        $startTime = $workingHours['start_time'];
        $endTime = $workingHours['end_time'];

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }
}
