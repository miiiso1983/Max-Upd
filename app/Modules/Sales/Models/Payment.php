<?php

namespace App\Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const METHOD_CASH = 'cash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHECK = 'check';
    const METHOD_MOBILE_PAYMENT = 'mobile_payment';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'customer_id',
        'amount',
        'payment_method',
        'status',
        'payment_date',
        'reference_number',
        'notes',
        'bank_name',
        'check_number',
        'check_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'check_date' => 'date',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'payment_method' => self::METHOD_CASH,
    ];

    /**
     * Get the invoice that owns the payment
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods()
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHECK => 'Check',
            self::METHOD_MOBILE_PAYMENT => 'Mobile Payment',
        ];
    }

    /**
     * Get available payment methods in Arabic
     */
    public static function getPaymentMethodsAr()
    {
        return [
            self::METHOD_CASH => 'نقدي',
            self::METHOD_CREDIT_CARD => 'بطاقة ائتمان',
            self::METHOD_BANK_TRANSFER => 'تحويل مصرفي',
            self::METHOD_CHECK => 'شيك',
            self::METHOD_MOBILE_PAYMENT => 'دفع عبر الهاتف',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_PENDING => 'معلق',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_FAILED => 'فاشل',
            self::STATUS_CANCELLED => 'ملغي',
        ];
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->payment_date = now();
        $this->save();

        // Update invoice payment
        $this->invoice->markAsPaid($this->amount);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for payments by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Get the user who created the payment
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Generate payment number if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $year = date('Y');
                $month = date('m');
                $count = static::whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->count() + 1;
                $payment->payment_number = "PAY-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
