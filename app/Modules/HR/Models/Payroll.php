<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'employee_id',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'allowances',
        'bonuses',
        'gross_salary',
        'tax_deduction',
        'social_security_deduction',
        'health_insurance_deduction',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'currency',
        'status',
        'paid_date',
        'payment_method',
        'payment_reference',
        'notes',
        'calculated_by',
        'approved_by',
        'paid_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'paid_date' => 'date',
        'basic_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'social_security_deduction' => 'decimal:2',
        'health_insurance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'currency' => 'IQD',
        'overtime_hours' => 0,
        'overtime_rate' => 0,
        'overtime_amount' => 0,
        'allowances' => 0,
        'bonuses' => 0,
        'tax_deduction' => 0,
        'social_security_deduction' => 0,
        'health_insurance_deduction' => 0,
        'other_deductions' => 0,
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who calculated the payroll
     */
    public function calculator()
    {
        return $this->belongsTo(\App\Models\User::class, 'calculated_by');
    }

    /**
     * Get the user who approved the payroll
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the user who paid the payroll
     */
    public function payer()
    {
        return $this->belongsTo(\App\Models\User::class, 'paid_by');
    }

    /**
     * Get the user who created the payroll
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who updated the payroll
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Calculate gross salary
     */
    public function calculateGrossSalary()
    {
        return $this->basic_salary + $this->overtime_amount + $this->allowances + $this->bonuses;
    }

    /**
     * Calculate total deductions
     */
    public function calculateTotalDeductions()
    {
        return $this->tax_deduction + $this->social_security_deduction + 
               $this->health_insurance_deduction + $this->other_deductions;
    }

    /**
     * Calculate net salary
     */
    public function calculateNetSalary()
    {
        return $this->calculateGrossSalary() - $this->calculateTotalDeductions();
    }

    /**
     * Calculate tax deduction (Iraqi tax system - simplified)
     */
    public function calculateTaxDeduction()
    {
        $grossSalary = $this->calculateGrossSalary();
        
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
     * Calculate social security deduction
     */
    public function calculateSocialSecurityDeduction()
    {
        // Iraqi social security: 5% of basic salary (employee contribution)
        return $this->basic_salary * 0.05;
    }

    /**
     * Calculate health insurance deduction
     */
    public function calculateHealthInsuranceDeduction()
    {
        // Health insurance: 2% of basic salary
        return $this->basic_salary * 0.02;
    }

    /**
     * Auto-calculate all amounts
     */
    public function autoCalculate()
    {
        // Calculate overtime amount
        $this->overtime_amount = $this->overtime_hours * $this->overtime_rate;
        
        // Calculate gross salary
        $this->gross_salary = $this->calculateGrossSalary();
        
        // Calculate deductions
        $this->tax_deduction = $this->calculateTaxDeduction();
        $this->social_security_deduction = $this->calculateSocialSecurityDeduction();
        $this->health_insurance_deduction = $this->calculateHealthInsuranceDeduction();
        $this->total_deductions = $this->calculateTotalDeductions();
        
        // Calculate net salary
        $this->net_salary = $this->calculateNetSalary();
        
        // Update status
        $this->status = self::STATUS_CALCULATED;
    }

    /**
     * Approve payroll
     */
    public function approve($approvedBy)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($paidBy, $paymentMethod, $paymentReference = null)
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_by' => $paidBy,
            'paid_date' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Check if payroll can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CALCULATED]);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_CALCULATED => 'Calculated',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAID => 'Paid',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_CALCULATED => 'محسوب',
            self::STATUS_APPROVED => 'موافق عليه',
            self::STATUS_PAID => 'مدفوع',
        ];
    }

    /**
     * Get payment methods
     */
    public static function getPaymentMethods()
    {
        return [
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'check' => 'Check',
            'mobile_payment' => 'Mobile Payment',
        ];
    }

    /**
     * Scope for payroll by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for payroll by pay period
     */
    public function scopeByPayPeriod($query, $startDate, $endDate)
    {
        return $query->where('pay_period_start', $startDate)
                    ->where('pay_period_end', $endDate);
    }

    /**
     * Scope for pending payrolls
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_CALCULATED]);
    }

    /**
     * Boot method to auto-calculate amounts
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payroll) {
            // Auto-calculate amounts if basic salary is set
            if ($payroll->basic_salary && $payroll->status === self::STATUS_DRAFT) {
                $payroll->autoCalculate();
            }
        });
    }
}
