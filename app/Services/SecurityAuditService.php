<?php

namespace App\Services;

use App\Models\User;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SecurityAuditService
{
    /**
     * Log security event
     */
    public static function logSecurityEvent(string $event, array $data = [], ?User $user = null, ?Request $request = null): void
    {
        $logData = [
            'event' => $event,
            'user_id' => $user?->id,
            'ip_address' => $request?->ip() ?? request()?->ip(),
            'user_agent' => $request?->userAgent() ?? request()?->userAgent(),
            'data' => $data,
            'timestamp' => now(),
        ];
        
        // Log to database
        SecurityLog::create($logData);
        
        // Log to file for critical events
        if (self::isCriticalEvent($event)) {
            Log::channel('security')->critical('Security Event: ' . $event, $logData);
        } else {
            Log::channel('security')->info('Security Event: ' . $event, $logData);
        }
        
        // Check for suspicious patterns
        self::checkSuspiciousActivity($event, $logData);
    }
    
    /**
     * Check if event is critical
     */
    private static function isCriticalEvent(string $event): bool
    {
        $criticalEvents = [
            'login_failed_multiple',
            'account_locked',
            'unauthorized_access_attempt',
            'privilege_escalation_attempt',
            'data_breach_attempt',
            'sql_injection_attempt',
            'xss_attempt',
            'suspicious_file_upload',
            'admin_action_unauthorized',
        ];
        
        return in_array($event, $criticalEvents);
    }
    
    /**
     * Check for suspicious activity patterns
     */
    private static function checkSuspiciousActivity(string $event, array $logData): void
    {
        $ipAddress = $logData['ip_address'];
        $userId = $logData['user_id'];
        
        // Check for multiple failed login attempts
        if ($event === 'login_failed') {
            self::checkFailedLoginPattern($ipAddress, $userId);
        }
        
        // Check for rapid requests from same IP
        if (in_array($event, ['api_request', 'page_access'])) {
            self::checkRapidRequestPattern($ipAddress);
        }
        
        // Check for unusual access patterns
        if ($event === 'admin_access' && $userId !== null) {
            self::checkUnusualAdminAccess($userId, $logData);
        }
    }
    
    /**
     * Check failed login patterns
     */
    private static function checkFailedLoginPattern(?string $ipAddress, ?int $userId): void
    {
        if (!$ipAddress) return;
        
        $cacheKey = "failed_logins_ip_{$ipAddress}";
        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addHour());
        
        if ($attempts >= 5) {
            self::logSecurityEvent('login_failed_multiple', [
                'ip_address' => $ipAddress,
                'attempts' => $attempts,
                'user_id' => $userId,
            ]);
            
            // Block IP temporarily
            self::blockIpTemporarily($ipAddress);
        }
        
        // Check user-specific failed attempts
        if ($userId) {
            $userCacheKey = "failed_logins_user_{$userId}";
            $userAttempts = Cache::get($userCacheKey, 0) + 1;
            Cache::put($userCacheKey, $userAttempts, now()->addHour());
            
            if ($userAttempts >= 3) {
                $user = User::find($userId);
                if ($user && !$user->isAccountLocked()) {
                    $user->lockAccount();
                    self::logSecurityEvent('account_locked', ['user_id' => $userId]);
                }
            }
        }
    }
    
    /**
     * Check rapid request patterns
     */
    private static function checkRapidRequestPattern(string $ipAddress): void
    {
        $cacheKey = "requests_ip_{$ipAddress}";
        $requests = Cache::get($cacheKey, []);
        $now = time();
        
        // Remove requests older than 1 minute
        $requests = array_filter($requests, fn($timestamp) => $now - $timestamp < 60);
        
        // Add current request
        $requests[] = $now;
        Cache::put($cacheKey, $requests, now()->addMinutes(5));
        
        // Check if too many requests in short time
        if (count($requests) > 100) { // More than 100 requests per minute
            self::logSecurityEvent('rapid_requests_detected', [
                'ip_address' => $ipAddress,
                'request_count' => count($requests),
            ]);
            
            self::blockIpTemporarily($ipAddress, 30); // Block for 30 minutes
        }
    }
    
    /**
     * Check unusual admin access
     */
    private static function checkUnusualAdminAccess(int $userId, array $logData): void
    {
        $user = User::find($userId);
        if (!$user) return;
        
        // Check if admin access from unusual location/time
        $lastAdminAccess = Cache::get("last_admin_access_{$userId}");
        
        if ($lastAdminAccess) {
            $timeDiff = now()->diffInHours($lastAdminAccess['timestamp']);
            $ipDiff = $lastAdminAccess['ip'] !== $logData['ip_address'];
            
            // If access from different IP within 1 hour, flag as suspicious
            if ($ipDiff && $timeDiff < 1) {
                self::logSecurityEvent('unusual_admin_access', [
                    'user_id' => $userId,
                    'previous_ip' => $lastAdminAccess['ip'],
                    'current_ip' => $logData['ip_address'],
                    'time_diff_hours' => $timeDiff,
                ]);
            }
        }
        
        // Store current access info
        Cache::put("last_admin_access_{$userId}", [
            'ip' => $logData['ip_address'],
            'timestamp' => now(),
        ], now()->addDays(7));
    }
    
    /**
     * Block IP temporarily
     */
    private static function blockIpTemporarily(string $ipAddress, int $minutes = 60): void
    {
        Cache::put("blocked_ip_{$ipAddress}", true, now()->addMinutes($minutes));
        
        self::logSecurityEvent('ip_blocked_temporarily', [
            'ip_address' => $ipAddress,
            'duration_minutes' => $minutes,
        ]);
    }
    
    /**
     * Check if IP is blocked
     */
    public static function isIpBlocked(string $ipAddress): bool
    {
        return Cache::has("blocked_ip_{$ipAddress}");
    }
    
    /**
     * Log data access
     */
    public static function logDataAccess(string $model, int $recordId, string $action, ?User $user = null): void
    {
        self::logSecurityEvent('data_access', [
            'model' => $model,
            'record_id' => $recordId,
            'action' => $action,
        ], $user);
    }
    
    /**
     * Log privilege escalation attempt
     */
    public static function logPrivilegeEscalation(string $attemptedAction, ?User $user = null): void
    {
        self::logSecurityEvent('privilege_escalation_attempt', [
            'attempted_action' => $attemptedAction,
            'user_permissions' => $user?->getAllPermissions()->pluck('name')->toArray(),
        ], $user);
    }
    
    /**
     * Log file upload
     */
    public static function logFileUpload(string $filename, string $mimeType, int $size, ?User $user = null): void
    {
        $suspicious = self::isSuspiciousFile($filename, $mimeType);
        
        self::logSecurityEvent($suspicious ? 'suspicious_file_upload' : 'file_upload', [
            'filename' => $filename,
            'mime_type' => $mimeType,
            'size' => $size,
            'suspicious' => $suspicious,
        ], $user);
    }
    
    /**
     * Check if file is suspicious
     */
    private static function isSuspiciousFile(string $filename, string $mimeType): bool
    {
        $suspiciousExtensions = [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
            'php', 'asp', 'aspx', 'jsp', 'pl', 'py', 'rb', 'sh'
        ];
        
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($extension, $suspiciousExtensions)) {
            return true;
        }
        
        // Check for double extensions
        if (substr_count($filename, '.') > 1) {
            return true;
        }
        
        // Check MIME type
        $suspiciousMimeTypes = [
            'application/x-executable',
            'application/x-msdownload',
            'application/x-msdos-program',
        ];
        
        return in_array($mimeType, $suspiciousMimeTypes);
    }
    
    /**
     * Generate security report
     */
    public static function generateSecurityReport(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $events = SecurityLog::where('created_at', '>=', $startDate)
                           ->selectRaw('event, COUNT(*) as count')
                           ->groupBy('event')
                           ->orderByDesc('count')
                           ->get();
        
        $topIps = SecurityLog::where('created_at', '>=', $startDate)
                           ->whereNotNull('ip_address')
                           ->selectRaw('ip_address, COUNT(*) as count')
                           ->groupBy('ip_address')
                           ->orderByDesc('count')
                           ->limit(10)
                           ->get();
        
        $criticalEvents = SecurityLog::where('created_at', '>=', $startDate)
                                   ->whereIn('event', [
                                       'login_failed_multiple',
                                       'account_locked',
                                       'unauthorized_access_attempt',
                                       'privilege_escalation_attempt',
                                   ])
                                   ->orderByDesc('created_at')
                                   ->limit(50)
                                   ->get();
        
        return [
            'period_days' => $days,
            'total_events' => SecurityLog::where('created_at', '>=', $startDate)->count(),
            'events_by_type' => $events,
            'top_ips' => $topIps,
            'critical_events' => $criticalEvents,
            'blocked_ips' => self::getBlockedIps(),
        ];
    }
    
    /**
     * Get currently blocked IPs
     */
    private static function getBlockedIps(): array
    {
        // This would require a more sophisticated cache implementation
        // For now, return empty array
        return [];
    }
    
    /**
     * Clean old security logs
     */
    public static function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return SecurityLog::where('created_at', '<', $cutoffDate)->delete();
    }
    
    /**
     * Check for SQL injection patterns
     */
    public static function checkSqlInjection(string $input, ?User $user = null): bool
    {
        $patterns = [
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b)/i',
            '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
            '/\'\s*(OR|AND)\s*\'/i',
            '/--\s*$/m',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::logSecurityEvent('sql_injection_attempt', [
                    'input' => substr($input, 0, 200), // Log first 200 chars
                    'pattern_matched' => $pattern,
                ], $user);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     */
    public static function checkXss(string $input, ?User $user = null): bool
    {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::logSecurityEvent('xss_attempt', [
                    'input' => substr($input, 0, 200),
                    'pattern_matched' => $pattern,
                ], $user);
                
                return true;
            }
        }
        
        return false;
    }
}
