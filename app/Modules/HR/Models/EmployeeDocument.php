<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    const TYPE_CONTRACT = 'contract';
    const TYPE_ID_COPY = 'id_copy';
    const TYPE_PASSPORT_COPY = 'passport_copy';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_RESUME = 'resume';
    const TYPE_MEDICAL_REPORT = 'medical_report';
    const TYPE_BANK_DETAILS = 'bank_details';
    const TYPE_EMERGENCY_CONTACT = 'emergency_contact';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'title_ar',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'expiry_date',
        'is_confidential',
        'uploaded_by',
        'created_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_confidential' => 'boolean',
        'file_size' => 'integer',
    ];

    protected $attributes = [
        'is_confidential' => false,
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who uploaded the document
     */
    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Get the user who created the document record
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if document is expired
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute()
    {
        return route('hr.documents.download', $this->id);
    }

    /**
     * Get available document types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_CONTRACT => 'Employment Contract',
            self::TYPE_ID_COPY => 'ID Copy',
            self::TYPE_PASSPORT_COPY => 'Passport Copy',
            self::TYPE_CERTIFICATE => 'Certificate',
            self::TYPE_RESUME => 'Resume/CV',
            self::TYPE_MEDICAL_REPORT => 'Medical Report',
            self::TYPE_BANK_DETAILS => 'Bank Details',
            self::TYPE_EMERGENCY_CONTACT => 'Emergency Contact',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get available document types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_CONTRACT => 'عقد العمل',
            self::TYPE_ID_COPY => 'نسخة الهوية',
            self::TYPE_PASSPORT_COPY => 'نسخة الجواز',
            self::TYPE_CERTIFICATE => 'شهادة',
            self::TYPE_RESUME => 'السيرة الذاتية',
            self::TYPE_MEDICAL_REPORT => 'تقرير طبي',
            self::TYPE_BANK_DETAILS => 'تفاصيل البنك',
            self::TYPE_EMERGENCY_CONTACT => 'جهة اتصال طوارئ',
            self::TYPE_OTHER => 'أخرى',
        ];
    }

    /**
     * Scope for documents by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for confidential documents
     */
    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    /**
     * Scope for expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope for documents expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }
}
