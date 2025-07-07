<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Collection;

trait TwoFactorAuthentication
{
    /**
     * Enable two factor authentication for the user.
     */
    public function enableTwoFactorAuthentication(string $method = 'app'): string
    {
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => Crypt::encrypt($secretKey),
            'two_factor_method' => $method,
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode($this->generateRecoveryCodes())),
        ]);
        
        return $secretKey;
    }
    
    /**
     * Disable two factor authentication for the user.
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_method' => 'app',
            'two_factor_phone' => null,
        ]);
    }
    
    /**
     * Confirm two factor authentication setup.
     */
    public function confirmTwoFactorAuthentication(string $code): bool
    {
        if ($this->verifyTwoFactorCode($code)) {
            $this->update([
                'two_factor_confirmed_at' => now(),
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Verify a two factor authentication code.
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_enabled || !$this->two_factor_secret) {
            return false;
        }
        
        $google2fa = new Google2FA();
        $secretKey = Crypt::decrypt($this->two_factor_secret);
        
        return $google2fa->verifyKey($secretKey, $code);
    }
    
    /**
     * Verify a recovery code.
     */
    public function verifyRecoveryCode(string $code): bool
    {
        if (!$this->two_factor_enabled || !$this->two_factor_recovery_codes) {
            return false;
        }
        
        $recoveryCodes = json_decode(Crypt::decrypt($this->two_factor_recovery_codes), true);
        
        if (in_array($code, $recoveryCodes)) {
            // Remove the used recovery code
            $recoveryCodes = array_diff($recoveryCodes, [$code]);
            
            $this->update([
                'two_factor_recovery_codes' => Crypt::encrypt(json_encode(array_values($recoveryCodes))),
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the two factor authentication QR code URL.
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        if (!$this->two_factor_secret) {
            return '';
        }
        
        $google2fa = new Google2FA();
        $secretKey = Crypt::decrypt($this->two_factor_secret);
        
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $secretKey
        );
    }
    
    /**
     * Get the recovery codes.
     */
    public function getRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }
        
        return json_decode(Crypt::decrypt($this->two_factor_recovery_codes), true);
    }
    
    /**
     * Generate new recovery codes.
     */
    public function regenerateRecoveryCodes(): array
    {
        $codes = $this->generateRecoveryCodes();
        
        $this->update([
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode($codes)),
        ]);
        
        return $codes;
    }
    
    /**
     * Generate recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        return Collection::times(8, function () {
            return strtoupper(substr(str_replace(['-', '_'], '', base64_encode(random_bytes(6))), 0, 8));
        })->toArray();
    }
    
    /**
     * Check if two factor authentication is enabled and confirmed.
     */
    public function hasTwoFactorAuthenticationEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }
    
    /**
     * Record login attempt.
     */
    public function recordLoginAttempt(string $ip, bool $successful = true): void
    {
        $attempts = $this->login_attempts ? json_decode($this->login_attempts, true) : [];
        
        // Keep only last 10 attempts
        if (count($attempts) >= 10) {
            array_shift($attempts);
        }
        
        $attempts[] = [
            'ip' => $ip,
            'successful' => $successful,
            'timestamp' => now()->toISOString(),
        ];
        
        $updateData = [
            'login_attempts' => json_encode($attempts),
        ];
        
        if ($successful) {
            $updateData['last_login_at'] = now();
            $updateData['last_login_ip'] = $ip;
            $updateData['locked_until'] = null; // Clear any lock
        }
        
        $this->update($updateData);
    }
    
    /**
     * Check if account is locked due to failed attempts.
     */
    public function isAccountLocked(): bool
    {
        return $this->locked_until && $this->locked_until > now();
    }
    
    /**
     * Lock account for specified minutes.
     */
    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }
    
    /**
     * Get failed login attempts count in last hour.
     */
    public function getRecentFailedAttempts(): int
    {
        if (!$this->login_attempts) {
            return 0;
        }
        
        $attempts = json_decode($this->login_attempts, true);
        $oneHourAgo = now()->subHour();
        
        return collect($attempts)
            ->filter(function ($attempt) use ($oneHourAgo) {
                return !$attempt['successful'] && 
                       \Carbon\Carbon::parse($attempt['timestamp'])->gt($oneHourAgo);
            })
            ->count();
    }
    
    /**
     * Check if account should be locked based on failed attempts.
     */
    public function shouldLockAccount(): bool
    {
        return $this->getRecentFailedAttempts() >= 5;
    }
    
    /**
     * Send two factor code via SMS.
     */
    public function sendTwoFactorCodeViaSms(): bool
    {
        if ($this->two_factor_method !== 'sms' || !$this->two_factor_phone) {
            return false;
        }
        
        // Generate a temporary code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the code temporarily (you might want to use cache or database)
        cache()->put("2fa_sms_{$this->id}", $code, now()->addMinutes(5));
        
        // Send SMS (implement your SMS service here)
        // Example: SMS::send($this->two_factor_phone, "Your verification code is: {$code}");
        
        return true;
    }
    
    /**
     * Verify SMS two factor code.
     */
    public function verifySmsCode(string $code): bool
    {
        if ($this->two_factor_method !== 'sms') {
            return false;
        }
        
        $storedCode = cache()->get("2fa_sms_{$this->id}");
        
        if ($storedCode && $storedCode === $code) {
            cache()->forget("2fa_sms_{$this->id}");
            return true;
        }
        
        return false;
    }
    
    /**
     * Send two factor code via email.
     */
    public function sendTwoFactorCodeViaEmail(): bool
    {
        if ($this->two_factor_method !== 'email') {
            return false;
        }
        
        // Generate a temporary code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the code temporarily
        cache()->put("2fa_email_{$this->id}", $code, now()->addMinutes(5));
        
        // Send email (implement your email service here)
        // Example: Mail::to($this->email)->send(new TwoFactorCodeMail($code));
        
        return true;
    }
    
    /**
     * Verify email two factor code.
     */
    public function verifyEmailCode(string $code): bool
    {
        if ($this->two_factor_method !== 'email') {
            return false;
        }
        
        $storedCode = cache()->get("2fa_email_{$this->id}");
        
        if ($storedCode && $storedCode === $code) {
            cache()->forget("2fa_email_{$this->id}");
            return true;
        }
        
        return false;
    }
}
