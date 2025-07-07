<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptionService
{
    /**
     * Encrypt sensitive data with additional security
     */
    public static function encryptSensitive(string $data, ?string $key = null): string
    {
        // Add timestamp and random salt for additional security
        $timestamp = now()->timestamp;
        $salt = bin2hex(random_bytes(16));
        $payload = json_encode([
            'data' => $data,
            'timestamp' => $timestamp,
            'salt' => $salt,
            'checksum' => hash('sha256', $data . $timestamp . $salt)
        ]);
        
        if ($key) {
            return self::encryptWithKey($payload, $key);
        }
        
        return Crypt::encrypt($payload);
    }
    
    /**
     * Decrypt sensitive data with validation
     */
    public static function decryptSensitive(string $encryptedData, ?string $key = null): ?string
    {
        try {
            if ($key) {
                $payload = self::decryptWithKey($encryptedData, $key);
            } else {
                $payload = Crypt::decrypt($encryptedData);
            }
            
            $data = json_decode($payload, true);
            
            if (!$data || !isset($data['data'], $data['timestamp'], $data['salt'], $data['checksum'])) {
                return null;
            }
            
            // Verify checksum
            $expectedChecksum = hash('sha256', $data['data'] . $data['timestamp'] . $data['salt']);
            if (!hash_equals($expectedChecksum, $data['checksum'])) {
                return null;
            }
            
            // Check if data is not too old (optional)
            $maxAge = config('app.encryption_max_age', 86400 * 30); // 30 days default
            if (now()->timestamp - $data['timestamp'] > $maxAge) {
                return null;
            }
            
            return $data['data'];
            
        } catch (DecryptException $e) {
            return null;
        }
    }
    
    /**
     * Encrypt with custom key
     */
    private static function encryptWithKey(string $data, string $key): string
    {
        $method = 'AES-256-CBC';
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt with custom key
     */
    private static function decryptWithKey(string $encryptedData, string $key): string
    {
        $method = 'AES-256-CBC';
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, $method, $key, 0, $iv);
    }
    
    /**
     * Hash password with additional security
     */
    public static function hashPassword(string $password): string
    {
        // Add pepper (application-wide secret)
        $pepper = config('app.password_pepper', '');
        $passwordWithPepper = $password . $pepper;
        
        return Hash::make($passwordWithPepper, [
            'rounds' => 12, // Increase cost for better security
        ]);
    }
    
    /**
     * Verify password with pepper
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        $pepper = config('app.password_pepper', '');
        $passwordWithPepper = $password . $pepper;
        
        return Hash::check($passwordWithPepper, $hash);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Generate API key
     */
    public static function generateApiKey(): string
    {
        $prefix = 'maxcon_';
        $timestamp = base_convert(time(), 10, 36);
        $random = bin2hex(random_bytes(16));
        
        return $prefix . $timestamp . '_' . $random;
    }
    
    /**
     * Encrypt database field
     */
    public static function encryptField(string $value): string
    {
        if (empty($value)) {
            return $value;
        }
        
        return self::encryptSensitive($value);
    }
    
    /**
     * Decrypt database field
     */
    public static function decryptField(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }
        
        return self::decryptSensitive($value);
    }
    
    /**
     * Hash sensitive identifier (like SSN, ID numbers)
     */
    public static function hashIdentifier(string $identifier): string
    {
        $salt = config('app.identifier_salt', 'default_salt');
        return hash('sha256', $identifier . $salt);
    }
    
    /**
     * Generate one-time token for password reset, etc.
     */
    public static function generateOneTimeToken(): array
    {
        $token = self::generateSecureToken(32);
        $hashedToken = hash('sha256', $token);
        $expiresAt = now()->addHours(1);
        
        return [
            'token' => $token,
            'hashed_token' => $hashedToken,
            'expires_at' => $expiresAt,
        ];
    }
    
    /**
     * Verify one-time token
     */
    public static function verifyOneTimeToken(string $token, string $hashedToken, $expiresAt): bool
    {
        // Check if token has expired
        if (now()->gt($expiresAt)) {
            return false;
        }
        
        // Verify token hash
        return hash_equals($hashedToken, hash('sha256', $token));
    }
    
    /**
     * Encrypt file content
     */
    public static function encryptFile(string $filePath, ?string $key = null): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        $encryptedContent = self::encryptSensitive($content, $key);
        
        return file_put_contents($filePath . '.enc', $encryptedContent) !== false;
    }
    
    /**
     * Decrypt file content
     */
    public static function decryptFile(string $encryptedFilePath, ?string $key = null): ?string
    {
        if (!file_exists($encryptedFilePath)) {
            return null;
        }
        
        $encryptedContent = file_get_contents($encryptedFilePath);
        return self::decryptSensitive($encryptedContent, $key);
    }
    
    /**
     * Generate encryption key for tenant
     */
    public static function generateTenantKey(int $tenantId): string
    {
        $masterKey = config('app.master_encryption_key');
        $tenantSalt = config('app.tenant_salt', 'tenant_salt');
        
        return hash('sha256', $masterKey . $tenantId . $tenantSalt);
    }
    
    /**
     * Secure data wipe (overwrite with random data)
     */
    public static function secureWipe(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $fileSize = filesize($filePath);
        $handle = fopen($filePath, 'r+b');
        
        if (!$handle) {
            return false;
        }
        
        // Overwrite with random data multiple times
        for ($i = 0; $i < 3; $i++) {
            fseek($handle, 0);
            fwrite($handle, random_bytes($fileSize));
            fflush($handle);
        }
        
        fclose($handle);
        return unlink($filePath);
    }
    
    /**
     * Generate HMAC signature
     */
    public static function generateSignature(string $data, string $secret): string
    {
        return hash_hmac('sha256', $data, $secret);
    }
    
    /**
     * Verify HMAC signature
     */
    public static function verifySignature(string $data, string $signature, string $secret): bool
    {
        $expectedSignature = self::generateSignature($data, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Encrypt data for API transmission
     */
    public static function encryptForApi(array $data, string $apiKey): string
    {
        $jsonData = json_encode($data);
        $timestamp = time();
        $nonce = bin2hex(random_bytes(16));
        
        $payload = [
            'data' => $jsonData,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
        ];
        
        $signature = self::generateSignature(json_encode($payload), $apiKey);
        $payload['signature'] = $signature;
        
        return base64_encode(json_encode($payload));
    }
    
    /**
     * Decrypt data from API transmission
     */
    public static function decryptFromApi(string $encryptedData, string $apiKey): ?array
    {
        try {
            $payload = json_decode(base64_decode($encryptedData), true);
            
            if (!$payload || !isset($payload['data'], $payload['timestamp'], $payload['nonce'], $payload['signature'])) {
                return null;
            }
            
            // Verify signature
            $signaturePayload = [
                'data' => $payload['data'],
                'timestamp' => $payload['timestamp'],
                'nonce' => $payload['nonce'],
            ];
            
            if (!self::verifySignature(json_encode($signaturePayload), $payload['signature'], $apiKey)) {
                return null;
            }
            
            // Check timestamp (prevent replay attacks)
            if (abs(time() - $payload['timestamp']) > 300) { // 5 minutes tolerance
                return null;
            }
            
            return json_decode($payload['data'], true);
            
        } catch (\Exception $e) {
            return null;
        }
    }
}
