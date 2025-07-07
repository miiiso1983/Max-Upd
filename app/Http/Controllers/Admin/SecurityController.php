<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    /**
     * Display security management page
     */
    public function index()
    {
        return view('admin.security.index');
    }
    
    /**
     * Get security overview data
     */
    public function overview()
    {
        $today = now()->startOfDay();
        
        $overview = [
            'today_events' => SecurityLog::where('created_at', '>=', $today)->count(),
            'failed_logins' => SecurityLog::where('event', 'login_failed')
                                        ->where('created_at', '>=', $today)
                                        ->count(),
            'blocked_ips' => $this->getBlockedIpsCount(),
            'critical_events' => SecurityLog::where('severity', 'critical')
                                          ->where('created_at', '>=', $today)
                                          ->count(),
        ];
        
        return response()->json($overview);
    }
    
    /**
     * Get security alerts
     */
    public function alerts()
    {
        $alerts = SecurityLog::where('severity', 'critical')
                            ->orWhere('severity', 'warning')
                            ->where('created_at', '>=', now()->subHours(24))
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->map(function ($log) {
                                return [
                                    'id' => $log->id,
                                    'event' => $log->event,
                                    'description' => $log->description,
                                    'severity' => $log->severity,
                                    'created_at' => $log->created_at->diffForHumans(),
                                ];
                            });
        
        return response()->json(['alerts' => $alerts]);
    }
    
    /**
     * Get security logs
     */
    public function logs(Request $request)
    {
        $query = SecurityLog::with(['user'])
                           ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }
        
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('ip_address') && $request->ip_address) {
            $query->where('ip_address', $request->ip_address);
        }
        
        // Date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date);
        }
        
        $logs = $query->paginate(50);
        
        // Add computed fields
        $logs->getCollection()->transform(function ($log) {
            $log->description = $log->description;
            $log->severity = $log->severity;
            return $log;
        });
        
        return response()->json(['logs' => $logs]);
    }
    
    /**
     * Get specific security log details
     */
    public function show(SecurityLog $securityLog)
    {
        $securityLog->load(['user', 'tenant']);
        $securityLog->description = $securityLog->description;
        $securityLog->severity = $securityLog->severity;
        
        return response()->json($securityLog);
    }
    
    /**
     * Generate security report
     */
    public function report(Request $request)
    {
        $days = $request->get('days', 7);
        $report = SecurityAuditService::generateSecurityReport($days);
        
        return response()->json($report);
    }
    
    /**
     * Clean old security logs
     */
    public function cleanLogs(Request $request)
    {
        $daysToKeep = $request->get('days', 90);
        $deletedCount = SecurityAuditService::cleanOldLogs($daysToKeep);
        
        return response()->json([
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "تم حذف {$deletedCount} سجل قديم بنجاح"
        ]);
    }
    
    /**
     * Get security statistics for charts
     */
    public function statistics(Request $request)
    {
        $days = $request->get('days', 7);
        $startDate = now()->subDays($days);
        
        // Daily events count
        $dailyEvents = SecurityLog::where('created_at', '>=', $startDate)
                                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                ->groupBy('date')
                                ->orderBy('date')
                                ->get();
        
        // Events by type
        $eventsByType = SecurityLog::where('created_at', '>=', $startDate)
                                 ->selectRaw('event, COUNT(*) as count')
                                 ->groupBy('event')
                                 ->orderByDesc('count')
                                 ->limit(10)
                                 ->get();
        
        // Events by severity
        $eventsBySeverity = SecurityLog::where('created_at', '>=', $startDate)
                                     ->selectRaw('severity, COUNT(*) as count')
                                     ->groupBy('severity')
                                     ->get();
        
        // Top IPs
        $topIps = SecurityLog::where('created_at', '>=', $startDate)
                            ->whereNotNull('ip_address')
                            ->selectRaw('ip_address, COUNT(*) as count')
                            ->groupBy('ip_address')
                            ->orderByDesc('count')
                            ->limit(10)
                            ->get();
        
        return response()->json([
            'daily_events' => $dailyEvents,
            'events_by_type' => $eventsByType,
            'events_by_severity' => $eventsBySeverity,
            'top_ips' => $topIps,
        ]);
    }
    
    /**
     * Block IP address
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'duration' => 'required|integer|min:1|max:1440', // minutes
            'reason' => 'required|string|max:255',
        ]);
        
        $ipAddress = $request->ip_address;
        $duration = $request->duration;
        $reason = $request->reason;
        
        // Block the IP
        Cache::put("blocked_ip_{$ipAddress}", [
            'reason' => $reason,
            'blocked_by' => auth()->id(),
            'blocked_at' => now(),
        ], now()->addMinutes($duration));
        
        // Log the action
        SecurityAuditService::logSecurityEvent('ip_blocked_manually', [
            'ip_address' => $ipAddress,
            'duration_minutes' => $duration,
            'reason' => $reason,
        ], auth()->user());
        
        return response()->json([
            'success' => true,
            'message' => "تم حظر عنوان IP {$ipAddress} لمدة {$duration} دقيقة"
        ]);
    }
    
    /**
     * Unblock IP address
     */
    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);
        
        $ipAddress = $request->ip_address;
        
        // Unblock the IP
        Cache::forget("blocked_ip_{$ipAddress}");
        
        // Log the action
        SecurityAuditService::logSecurityEvent('ip_unblocked_manually', [
            'ip_address' => $ipAddress,
        ], auth()->user());
        
        return response()->json([
            'success' => true,
            'message' => "تم إلغاء حظر عنوان IP {$ipAddress}"
        ]);
    }
    
    /**
     * Get blocked IPs
     */
    public function blockedIps()
    {
        // This is a simplified implementation
        // In a real application, you might want to store blocked IPs in database
        $blockedIps = [];
        
        return response()->json(['blocked_ips' => $blockedIps]);
    }
    
    /**
     * Export security report
     */
    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $days = $request->get('days', 7);
        
        $report = SecurityAuditService::generateSecurityReport($days);
        
        if ($format === 'pdf') {
            return $this->exportReportPdf($report);
        } else {
            return $this->exportReportExcel($report);
        }
    }
    
    /**
     * Export report as PDF
     */
    private function exportReportPdf($report)
    {
        // This would use DomPDF to generate PDF
        return response()->json([
            'message' => 'PDF export functionality will be implemented with DomPDF'
        ]);
    }
    
    /**
     * Export report as Excel
     */
    private function exportReportExcel($report)
    {
        // This would use Laravel Excel to generate Excel file
        return response()->json([
            'message' => 'Excel export functionality will be implemented with Laravel Excel'
        ]);
    }
    
    /**
     * Get count of blocked IPs
     */
    private function getBlockedIpsCount(): int
    {
        // This is a simplified implementation
        // In a real application, you would query the cache or database
        return 0;
    }
    
    /**
     * Update security settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'max_login_attempts' => 'required|integer|min:1|max:10',
            'lockout_duration' => 'required|integer|min:5|max:1440',
            'force_2fa_for_admins' => 'boolean',
            'monitor_sql_injection' => 'boolean',
            'monitor_xss' => 'boolean',
            'auto_clean_logs' => 'boolean',
        ]);
        
        // Save settings (you might want to use a settings table or config)
        $settings = $request->only([
            'max_login_attempts',
            'lockout_duration',
            'force_2fa_for_admins',
            'monitor_sql_injection',
            'monitor_xss',
            'auto_clean_logs',
        ]);
        
        // Store in cache or database
        Cache::put('security_settings', $settings, now()->addDays(30));
        
        // Log the change
        SecurityAuditService::logSecurityEvent('security_settings_updated', [
            'settings' => $settings,
        ], auth()->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم حفظ إعدادات الأمان بنجاح'
        ]);
    }
    
    /**
     * Get security settings
     */
    public function getSettings()
    {
        $defaultSettings = [
            'max_login_attempts' => 5,
            'lockout_duration' => 30,
            'force_2fa_for_admins' => true,
            'monitor_sql_injection' => true,
            'monitor_xss' => true,
            'auto_clean_logs' => true,
        ];
        
        $settings = Cache::get('security_settings', $defaultSettings);
        
        return response()->json(['settings' => $settings]);
    }
}
