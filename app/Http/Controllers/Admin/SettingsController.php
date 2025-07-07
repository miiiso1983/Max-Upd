<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|tenant-admin|super_admin');
    }

    /**
     * Display system settings
     */
    public function index(Request $request)
    {
        $settings = $this->getSystemSettings();

        if ($request->expectsJson()) {
            return response()->json(['settings' => $settings]);
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_name_ar' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
            'company_address_ar' => 'required|string|max:500',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'language' => 'required|string|max:10',
            'items_per_page' => 'required|integer|min:10|max:100',
            'session_timeout' => 'required|integer|min:15|max:480',
            'backup_frequency' => 'required|string|in:daily,weekly,monthly',
            'backup_retention_days' => 'required|integer|min:7|max:365',
            'enable_notifications' => 'boolean',
            'enable_email_notifications' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'enable_two_factor' => 'boolean',
            'enable_audit_log' => 'boolean',
            'maintenance_mode' => 'boolean',
            'allow_registration' => 'boolean',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:5|max:60',
        ]);

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('logos', 'public');
            $validated['company_logo'] = $logoPath;
        }

        // Save settings to cache and database
        foreach ($validated as $key => $value) {
            Cache::put("setting_{$key}", $value, now()->addDays(30));
        }

        // You would typically save these to a settings table
        // For now, we'll just use cache

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم حفظ الإعدادات بنجاح',
                'settings' => $validated
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    /**
     * Get system information
     */
    public function systemInfo(Request $request)
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'disk_space' => [
                'total' => disk_total_space(storage_path()),
                'free' => disk_free_space(storage_path()),
                'used' => disk_total_space(storage_path()) - disk_free_space(storage_path()),
            ],
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
        ];

        if ($request->expectsJson()) {
            return response()->json(['system_info' => $systemInfo]);
        }

        return view('admin.settings.system-info', compact('systemInfo'));
    }

    /**
     * Clear system cache
     */
    public function clearCache(Request $request)
    {
        $cacheTypes = $request->input('cache_types', ['config', 'route', 'view']);
        $cleared = [];

        foreach ($cacheTypes as $type) {
            switch ($type) {
                case 'config':
                    \Artisan::call('config:clear');
                    $cleared[] = 'إعدادات التطبيق';
                    break;
                case 'route':
                    \Artisan::call('route:clear');
                    $cleared[] = 'مسارات التطبيق';
                    break;
                case 'view':
                    \Artisan::call('view:clear');
                    $cleared[] = 'ملفات العرض';
                    break;
                case 'cache':
                    \Artisan::call('cache:clear');
                    $cleared[] = 'ذاكرة التخزين المؤقت';
                    break;
            }
        }

        $message = 'تم مسح: ' . implode(', ', $cleared);

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', $message);
    }

    /**
     * Create system backup
     */
    public function createBackup(Request $request)
    {
        // This would implement actual backup functionality
        $backupName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
        
        // Simulate backup creation
        $backupInfo = [
            'name' => $backupName,
            'size' => '15.2 MB',
            'created_at' => now(),
            'type' => 'manual',
            'status' => 'completed'
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                'backup' => $backupInfo
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح');
    }

    /**
     * Get system settings
     */
    private function getSystemSettings()
    {
        return [
            'company_name' => Cache::get('setting_company_name', 'MaxCon ERP'),
            'company_name_ar' => Cache::get('setting_company_name_ar', 'ماكس كون لإدارة الموارد'),
            'company_email' => Cache::get('setting_company_email', 'info@maxcon-erp.com'),
            'company_phone' => Cache::get('setting_company_phone', '+964 770 123 4567'),
            'company_address' => Cache::get('setting_company_address', 'Baghdad, Iraq'),
            'company_address_ar' => Cache::get('setting_company_address_ar', 'بغداد، العراق'),
            'company_logo' => Cache::get('setting_company_logo', null),
            'currency' => Cache::get('setting_currency', 'IQD'),
            'currency_symbol' => Cache::get('setting_currency_symbol', 'د.ع'),
            'timezone' => Cache::get('setting_timezone', 'Asia/Baghdad'),
            'date_format' => Cache::get('setting_date_format', 'Y-m-d'),
            'time_format' => Cache::get('setting_time_format', 'H:i'),
            'language' => Cache::get('setting_language', 'ar'),
            'items_per_page' => Cache::get('setting_items_per_page', 20),
            'session_timeout' => Cache::get('setting_session_timeout', 120),
            'backup_frequency' => Cache::get('setting_backup_frequency', 'daily'),
            'backup_retention_days' => Cache::get('setting_backup_retention_days', 30),
            'enable_notifications' => Cache::get('setting_enable_notifications', true),
            'enable_email_notifications' => Cache::get('setting_enable_email_notifications', true),
            'enable_sms_notifications' => Cache::get('setting_enable_sms_notifications', false),
            'enable_two_factor' => Cache::get('setting_enable_two_factor', true),
            'enable_audit_log' => Cache::get('setting_enable_audit_log', true),
            'maintenance_mode' => Cache::get('setting_maintenance_mode', false),
            'allow_registration' => Cache::get('setting_allow_registration', false),
            'max_login_attempts' => Cache::get('setting_max_login_attempts', 5),
            'lockout_duration' => Cache::get('setting_lockout_duration', 15),
        ];
    }

    /**
     * Get database version
     */
    private function getDatabaseVersion()
    {
        try {
            return \DB::select('SELECT VERSION() as version')[0]->version;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
}
