<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'signature_type',
        'signature_data',
        'signature_image',
        'ip_address',
        'user_agent',
        'signed_at',
        'certificate_data',
        'verification_code',
        'is_verified',
        'notes',
        'notes_ar',
    ];

    protected $casts = [
        'signature_data' => 'array',
        'certificate_data' => 'array',
        'signed_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Signature type constants
    const TYPE_DIGITAL = 'digital';
    const TYPE_ELECTRONIC = 'electronic';
    const TYPE_HANDWRITTEN = 'handwritten';
    const TYPE_BIOMETRIC = 'biometric';

    /**
     * Relationships
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('signature_type', $type);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_DIGITAL => 'Digital Signature',
            self::TYPE_ELECTRONIC => 'Electronic Signature',
            self::TYPE_HANDWRITTEN => 'Handwritten Signature',
            self::TYPE_BIOMETRIC => 'Biometric Signature',
        ];

        return $labels[$this->signature_type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_DIGITAL => 'توقيع رقمي',
            self::TYPE_ELECTRONIC => 'توقيع إلكتروني',
            self::TYPE_HANDWRITTEN => 'توقيع مكتوب بخط اليد',
            self::TYPE_BIOMETRIC => 'توقيع بيومتري',
        ];

        return $labels[$this->signature_type] ?? 'غير معروف';
    }

    /**
     * Methods
     */
    public function verify()
    {
        $this->update([
            'is_verified' => true,
            'verification_code' => $this->generateVerificationCode(),
        ]);

        return $this;
    }

    public function generateVerificationCode()
    {
        return strtoupper(substr(md5($this->id . $this->signed_at . $this->user_id), 0, 8));
    }

    public function isValid()
    {
        return $this->is_verified && $this->signed_at;
    }
}
