<?php

namespace App\Modules\SalesReps\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesRepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'city' => $this->city,
            'governorate' => $this->governorate,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'national_id' => $this->national_id,
            'base_salary' => $this->base_salary,
            'commission_rate' => $this->commission_rate,
            'monthly_target' => $this->monthly_target,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'supervisor' => $this->whenLoaded('supervisor', function () {
                return [
                    'id' => $this->supervisor->id,
                    'name' => $this->supervisor->name,
                    'employee_code' => $this->supervisor->employee_code,
                ];
            }),
            
            'territories' => $this->whenLoaded('territories', function () {
                return $this->territories->map(function ($territory) {
                    return [
                        'id' => $territory->id,
                        'name' => $territory->name,
                        'name_ar' => $territory->name_ar,
                        'code' => $territory->code,
                        'assignment_type' => $territory->pivot->assignment_type ?? null,
                        'is_active' => $territory->pivot->is_active ?? null,
                    ];
                });
            }),
            
            'customers_count' => $this->whenLoaded('customers', function () {
                return $this->customers->count();
            }),
            
            'visits_count' => $this->whenLoaded('visits', function () {
                return $this->visits->count();
            }),
            
            // Performance metrics
            'performance' => $this->when($request->has('include_performance'), function () {
                return [
                    'total_visits' => $this->visits()->count(),
                    'completed_visits' => $this->visits()->where('status', 'completed')->count(),
                    'total_sales' => $this->visits()->sum('sales_amount') ?? 0,
                    'total_collections' => $this->visits()->sum('collection_amount') ?? 0,
                    'monthly_achievement' => $this->calculateMonthlyAchievement(),
                ];
            }),
        ];
    }
    
    /**
     * Calculate monthly achievement percentage
     */
    private function calculateMonthlyAchievement(): float
    {
        if (!$this->monthly_target || $this->monthly_target == 0) {
            return 0;
        }
        
        $currentMonthSales = $this->visits()
            ->whereMonth('visit_date', now()->month)
            ->whereYear('visit_date', now()->year)
            ->sum('sales_amount') ?? 0;
            
        return round(($currentMonthSales / $this->monthly_target) * 100, 2);
    }
}
