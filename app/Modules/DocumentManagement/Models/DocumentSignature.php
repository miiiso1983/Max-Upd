<?php

namespace App\Modules\DocumentManagement\Models;

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

    // Signature type constants (matching existing table)
    const TYPE_DIGITAL = 'digital';
    const TYPE_ELECTRONIC = 'electronic';
    const TYPE_HANDWRITTEN = 'handwritten';
    const TYPE_BIOMETRIC = 'biometric';

    // Signature method constants
    const METHOD_PKI = 'pki'; // Public Key Infrastructure
    const METHOD_HASH = 'hash';
    const METHOD_TIMESTAMP = 'timestamp';
    const METHOD_BIOMETRIC_SCAN = 'biometric_scan';
    const METHOD_DRAWN = 'drawn';
    const METHOD_TYPED = 'typed';
    const METHOD_UPLOADED = 'uploaded';
    const METHOD_OTP = 'otp'; // One Time Password

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SIGNED = 'signed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    // Verification status constants
    const VERIFICATION_VALID = 'valid';
    const VERIFICATION_INVALID = 'invalid';
    const VERIFICATION_EXPIRED = 'expired';
    const VERIFICATION_REVOKED = 'revoked';
    const VERIFICATION_PENDING = 'pending';

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

    public function revoker()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Scopes
     */
    public function scopeValid($query)
    {
        return $query->where('status', self::STATUS_SIGNED)
                    ->where('verification_status', self::VERIFICATION_VALID)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->whereNull('revoked_at');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('signature_type', $type);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('signature_method', $method);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_ELECTRONIC => 'Electronic Signature',
            self::TYPE_DIGITAL => 'Digital Signature',
            self::TYPE_BIOMETRIC => 'Biometric Signature',
            self::TYPE_HANDWRITTEN => 'Handwritten Signature',
            self::TYPE_CLICK_TO_SIGN => 'Click to Sign',
            self::TYPE_PIN => 'PIN Signature',
            self::TYPE_SMS => 'SMS Verification',
            self::TYPE_EMAIL => 'Email Verification',
        ];

        return $labels[$this->signature_type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_ELECTRONIC => 'توقيع إلكتروني',
            self::TYPE_DIGITAL => 'توقيع رقمي',
            self::TYPE_BIOMETRIC => 'توقيع بيومتري',
            self::TYPE_HANDWRITTEN => 'توقيع مكتوب بخط اليد',
            self::TYPE_CLICK_TO_SIGN => 'انقر للتوقيع',
            self::TYPE_PIN => 'توقيع برقم سري',
            self::TYPE_SMS => 'تحقق عبر الرسائل النصية',
            self::TYPE_EMAIL => 'تحقق عبر البريد الإلكتروني',
        ];

        return $labels[$this->signature_type] ?? 'غير معروف';
    }

    public function getMethodLabelAttribute()
    {
        $labels = [
            self::METHOD_PKI => 'Public Key Infrastructure',
            self::METHOD_HASH => 'Hash-based',
            self::METHOD_TIMESTAMP => 'Timestamp-based',
            self::METHOD_BIOMETRIC_SCAN => 'Biometric Scan',
            self::METHOD_DRAWN => 'Hand Drawn',
            self::METHOD_TYPED => 'Typed Signature',
            self::METHOD_UPLOADED => 'Uploaded Image',
            self::METHOD_OTP => 'One Time Password',
        ];

        return $labels[$this->signature_method] ?? 'Unknown';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SIGNED => 'Signed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_REVOKED => 'Revoked',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'في الانتظار',
            self::STATUS_SIGNED => 'موقع',
            self::STATUS_FAILED => 'فشل',
            self::STATUS_EXPIRED => 'منتهي الصلاحية',
            self::STATUS_REVOKED => 'ملغي',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getVerificationStatusLabelAttribute()
    {
        $labels = [
            self::VERIFICATION_VALID => 'Valid',
            self::VERIFICATION_INVALID => 'Invalid',
            self::VERIFICATION_EXPIRED => 'Expired',
            self::VERIFICATION_REVOKED => 'Revoked',
            self::VERIFICATION_PENDING => 'Pending',
        ];

        return $labels[$this->verification_status] ?? 'Unknown';
    }

    public function getIsValidAttribute()
    {
        return $this->status === self::STATUS_SIGNED &&
               $this->verification_status === self::VERIFICATION_VALID &&
               (!$this->expires_at || $this->expires_at->isFuture()) &&
               !$this->revoked_at;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsRevokedAttribute()
    {
        return !is_null($this->revoked_at);
    }

    /**
     * Methods
     */
    public function sign($signatureData, $method = null, $certificateData = null)
    {
        $this->update([
            'signature_data' => $signatureData,
            'signature_method' => $method ?: $this->signature_method,
            'certificate_data' => $certificateData,
            'signature_hash' => $this->generateSignatureHash($signatureData),
            'timestamp' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => self::STATUS_SIGNED,
            'verification_status' => self::VERIFICATION_VALID,
        ]);

        // Generate verification data
        $this->generateVerificationData();

        return $this;
    }

    public function revoke($reason = null, $revokedBy = null)
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'verification_status' => self::VERIFICATION_REVOKED,
            'revoked_at' => now(),
            'revoked_by' => $revokedBy ?: auth()->id(),
            'revocation_reason' => $reason,
        ]);

        return $this;
    }

    public function verify()
    {
        // Basic verification checks
        if ($this->status !== self::STATUS_SIGNED) {
            $this->update(['verification_status' => self::VERIFICATION_INVALID]);
            return false;
        }

        if ($this->is_expired) {
            $this->update(['verification_status' => self::VERIFICATION_EXPIRED]);
            return false;
        }

        if ($this->is_revoked) {
            $this->update(['verification_status' => self::VERIFICATION_REVOKED]);
            return false;
        }

        // Verify signature hash
        if (!$this->verifySignatureHash()) {
            $this->update(['verification_status' => self::VERIFICATION_INVALID]);
            return false;
        }

        // Additional verification based on signature type
        $isValid = $this->performTypeSpecificVerification();

        $this->update([
            'verification_status' => $isValid ? self::VERIFICATION_VALID : self::VERIFICATION_INVALID
        ]);

        return $isValid;
    }

    private function generateSignatureHash($signatureData)
    {
        $dataToHash = [
            'document_id' => $this->document_id,
            'user_id' => $this->user_id,
            'signature_data' => $signatureData,
            'timestamp' => now()->toISOString(),
        ];

        return hash('sha256', json_encode($dataToHash));
    }

    private function verifySignatureHash()
    {
        if (!$this->signature_hash || !$this->signature_data) {
            return false;
        }

        $dataToHash = [
            'document_id' => $this->document_id,
            'user_id' => $this->user_id,
            'signature_data' => $this->signature_data,
            'timestamp' => $this->timestamp->toISOString(),
        ];

        $expectedHash = hash('sha256', json_encode($dataToHash));
        return hash_equals($this->signature_hash, $expectedHash);
    }

    private function performTypeSpecificVerification()
    {
        switch ($this->signature_type) {
            case self::TYPE_DIGITAL:
                return $this->verifyDigitalSignature();
            case self::TYPE_BIOMETRIC:
                return $this->verifyBiometricSignature();
            case self::TYPE_PKI:
                return $this->verifyPKISignature();
            default:
                return true; // Basic verification passed
        }
    }

    private function verifyDigitalSignature()
    {
        // Implement digital signature verification logic
        // This would typically involve PKI certificate validation
        return true; // Placeholder
    }

    private function verifyBiometricSignature()
    {
        // Implement biometric signature verification logic
        // This would involve comparing biometric data
        return true; // Placeholder
    }

    private function verifyPKISignature()
    {
        // Implement PKI signature verification logic
        // This would involve certificate chain validation
        return true; // Placeholder
    }

    private function generateVerificationData()
    {
        $verificationData = [
            'signature_hash' => $this->signature_hash,
            'timestamp' => $this->timestamp->toISOString(),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'document_checksum' => $this->document->checksum,
            'verification_timestamp' => now()->toISOString(),
        ];

        $this->update(['verification_data' => $verificationData]);
    }

    public function generateCertificate()
    {
        $certificate = [
            'signature_id' => $this->id,
            'document_id' => $this->document_id,
            'document_title' => $this->document->title,
            'signer_name' => $this->user->name,
            'signer_email' => $this->user->email,
            'signature_type' => $this->signature_type,
            'signature_method' => $this->signature_method,
            'signed_at' => $this->timestamp->toISOString(),
            'verification_status' => $this->verification_status,
            'certificate_hash' => null,
        ];

        $certificate['certificate_hash'] = hash('sha256', json_encode($certificate));

        return $certificate;
    }

    public static function createSignatureRequest($documentId, $userId, $signatureType, $options = [])
    {
        return static::create([
            'document_id' => $documentId,
            'user_id' => $userId,
            'signature_type' => $signatureType,
            'signature_method' => $options['method'] ?? self::METHOD_HASH,
            'reason' => $options['reason'] ?? null,
            'expires_at' => $options['expires_at'] ?? now()->addDays(30),
            'status' => self::STATUS_PENDING,
            'verification_status' => self::VERIFICATION_PENDING,
            'metadata' => $options['metadata'] ?? [],
        ]);
    }

    public function sendSignatureNotification()
    {
        // Send notification to user about pending signature
        // This would typically send an email or SMS
        return true; // Placeholder
    }

    public function getSignatureUrl()
    {
        return route('documents.sign', [
            'document' => $this->document_id,
            'signature' => $this->id,
            'token' => $this->generateSignatureToken(),
        ]);
    }

    private function generateSignatureToken()
    {
        return hash('sha256', $this->id . $this->document_id . $this->user_id . config('app.key'));
    }

    public function validateSignatureToken($token)
    {
        return hash_equals($this->generateSignatureToken(), $token);
    }
}
