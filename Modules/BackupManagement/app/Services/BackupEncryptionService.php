<?php

namespace Modules\BackupManagement\app\Services;

use Modules\BackupManagement\app\Models\TenantBackup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Exception;

class BackupEncryptionService
{
    protected $backupDisk;

    public function __construct()
    {
        $this->backupDisk = Storage::disk('backups');
    }

    /**
     * Encrypt a backup file
     */
    public function encryptFile(string $filePath, TenantBackup $backup)
    {
        if (!$this->backupDisk->exists($filePath)) {
            throw new Exception('File to encrypt does not exist: ' . $filePath);
        }

        // Generate encryption key
        $encryptionKey = $this->generateEncryptionKey();
        
        // Read file content
        $fileContent = $this->backupDisk->get($filePath);
        
        // Encrypt content
        $encryptedContent = $this->encryptContent($fileContent, $encryptionKey);
        
        // Create encrypted file path
        $encryptedFilePath = $this->getEncryptedFilePath($filePath);
        
        // Save encrypted content
        $this->backupDisk->put($encryptedFilePath, $encryptedContent);
        
        // Store encryption key hash for verification
        $backup->update([
            'encryption_key_hash' => hash('sha256', $encryptionKey),
        ]);
        
        // Store encryption key securely (in production, use a key management service)
        $this->storeEncryptionKey($backup->id, $encryptionKey);
        
        // Delete original unencrypted file
        $this->backupDisk->delete($filePath);
        
        return $encryptedFilePath;
    }

    /**
     * Decrypt a backup file
     */
    public function decryptFile(string $encryptedFilePath, TenantBackup $backup)
    {
        if (!$this->backupDisk->exists($encryptedFilePath)) {
            throw new Exception('Encrypted file does not exist: ' . $encryptedFilePath);
        }

        // Retrieve encryption key
        $encryptionKey = $this->retrieveEncryptionKey($backup->id);
        
        if (!$encryptionKey) {
            throw new Exception('Encryption key not found for backup ID: ' . $backup->id);
        }

        // Verify encryption key
        if (hash('sha256', $encryptionKey) !== $backup->encryption_key_hash) {
            throw new Exception('Encryption key verification failed');
        }

        // Read encrypted content
        $encryptedContent = $this->backupDisk->get($encryptedFilePath);
        
        // Decrypt content
        $decryptedContent = $this->decryptContent($encryptedContent, $encryptionKey);
        
        // Create decrypted file path
        $decryptedFilePath = $this->getDecryptedFilePath($encryptedFilePath);
        
        // Save decrypted content
        $this->backupDisk->put($decryptedFilePath, $decryptedContent);
        
        return $decryptedFilePath;
    }

    /**
     * Generate a secure encryption key
     */
    protected function generateEncryptionKey()
    {
        return Str::random(32); // 256-bit key
    }

    /**
     * Encrypt content using AES-256-CBC
     */
    protected function encryptContent(string $content, string $key)
    {
        $iv = random_bytes(16); // 128-bit IV for AES
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }

        // Prepend IV to encrypted data
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt content using AES-256-CBC
     */
    protected function decryptContent(string $encryptedContent, string $key)
    {
        $data = base64_decode($encryptedContent);
        
        if ($data === false) {
            throw new Exception('Invalid encrypted data format');
        }

        // Extract IV and encrypted content
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Get encrypted file path
     */
    protected function getEncryptedFilePath(string $originalPath)
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.encrypted';
    }

    /**
     * Get decrypted file path
     */
    protected function getDecryptedFilePath(string $encryptedPath)
    {
        return str_replace('.encrypted', '.decrypted', $encryptedPath);
    }

    /**
     * Store encryption key securely
     * In production, this should use a proper key management service
     */
    protected function storeEncryptionKey(int $backupId, string $key)
    {
        $keyPath = "encryption_keys/backup_{$backupId}.key";
        
        // Encrypt the key with Laravel's application key
        $encryptedKey = Crypt::encrypt($key);
        
        $this->backupDisk->put($keyPath, $encryptedKey);
    }

    /**
     * Retrieve encryption key
     */
    protected function retrieveEncryptionKey(int $backupId)
    {
        $keyPath = "encryption_keys/backup_{$backupId}.key";
        
        if (!$this->backupDisk->exists($keyPath)) {
            return null;
        }

        $encryptedKey = $this->backupDisk->get($keyPath);
        
        try {
            return Crypt::decrypt($encryptedKey);
        } catch (Exception $e) {
            throw new Exception('Failed to decrypt encryption key: ' . $e->getMessage());
        }
    }

    /**
     * Verify file encryption
     */
    public function verifyEncryption(TenantBackup $backup)
    {
        if (!$backup->encrypted) {
            return true; // Not encrypted, so verification passes
        }

        // Check if encryption key exists
        $encryptionKey = $this->retrieveEncryptionKey($backup->id);
        
        if (!$encryptionKey) {
            return false;
        }

        // Verify key hash
        return hash('sha256', $encryptionKey) === $backup->encryption_key_hash;
    }

    /**
     * Clean up encryption keys for deleted backups
     */
    public function cleanupEncryptionKey(int $backupId)
    {
        $keyPath = "encryption_keys/backup_{$backupId}.key";
        
        if ($this->backupDisk->exists($keyPath)) {
            $this->backupDisk->delete($keyPath);
        }
    }

    /**
     * Rotate encryption keys (for security best practices)
     */
    public function rotateEncryptionKey(TenantBackup $backup)
    {
        if (!$backup->encrypted || !$backup->fileExists()) {
            throw new Exception('Cannot rotate key for non-encrypted or missing backup');
        }

        // Decrypt with old key
        $decryptedFilePath = $this->decryptFile($backup->file_path, $backup);
        
        // Re-encrypt with new key
        $newEncryptedFilePath = $this->encryptFile($decryptedFilePath, $backup);
        
        // Update backup record
        $backup->update([
            'file_path' => $newEncryptedFilePath,
        ]);

        return $newEncryptedFilePath;
    }

    /**
     * Get encryption statistics
     */
    public function getEncryptionStatistics()
    {
        $totalBackups = TenantBackup::count();
        $encryptedBackups = TenantBackup::where('encrypted', true)->count();
        $encryptionKeys = collect($this->backupDisk->files('encryption_keys'))->count();

        return [
            'total_backups' => $totalBackups,
            'encrypted_backups' => $encryptedBackups,
            'encryption_percentage' => $totalBackups > 0 ? round(($encryptedBackups / $totalBackups) * 100, 2) : 0,
            'stored_keys' => $encryptionKeys,
            'orphaned_keys' => max(0, $encryptionKeys - $encryptedBackups),
        ];
    }

    /**
     * Cleanup orphaned encryption keys
     */
    public function cleanupOrphanedKeys()
    {
        $keyFiles = $this->backupDisk->files('encryption_keys');
        $cleanedCount = 0;

        foreach ($keyFiles as $keyFile) {
            // Extract backup ID from filename
            if (preg_match('/backup_(\d+)\.key$/', $keyFile, $matches)) {
                $backupId = (int) $matches[1];
                
                // Check if backup still exists
                if (!TenantBackup::where('id', $backupId)->where('encrypted', true)->exists()) {
                    $this->backupDisk->delete($keyFile);
                    $cleanedCount++;
                }
            }
        }

        return $cleanedCount;
    }
}
