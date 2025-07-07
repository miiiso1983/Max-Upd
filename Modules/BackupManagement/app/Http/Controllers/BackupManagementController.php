<?php

namespace Modules\BackupManagement\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Modules\BackupManagement\app\Models\TenantBackup;
use Modules\BackupManagement\app\Models\BackupSchedule;
use Modules\BackupManagement\app\Models\BackupRestoreLog;
use Modules\BackupManagement\app\Services\BackupService;
use Modules\BackupManagement\app\Services\RestoreService;
use Modules\BackupManagement\app\Services\BackupSchedulerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class BackupManagementController extends Controller
{
    protected $backupService;
    protected $restoreService;
    protected $schedulerService;

    public function __construct()
    {
        $this->backupService = new BackupService();
        $this->restoreService = new RestoreService();
        $this->schedulerService = new BackupSchedulerService();
    }

    /**
     * Get all backups for a tenant
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'status' => 'nullable|in:pending,in_progress,completed,failed,cancelled',
            'backup_type' => 'nullable|in:full,incremental,differential',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = TenantBackup::with(['creator', 'tenant'])
            ->where('tenant_id', $request->tenant_id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('backup_type')) {
            $query->where('backup_type', $request->backup_type);
        }

        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');

        $backups = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'backups' => $backups,
        ]);
    }

    /**
     * Create a new backup
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'backup_type' => 'required|in:full,incremental',
            'name' => 'nullable|string|max:255',
            'encrypt' => 'nullable|boolean',
            'compress' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $tenant = Tenant::findOrFail($request->tenant_id);

            $options = [
                'name' => $request->name,
                'trigger_type' => TenantBackup::TRIGGER_MANUAL,
                'encrypt' => $request->get('encrypt', true),
                'compress' => $request->get('compress', true),
                'expires_at' => $request->expires_at ? now()->parse($request->expires_at) : now()->addDays(30),
                'created_by' => auth()->id() ?? 1, // Default for testing
            ];

            if ($request->backup_type === 'full') {
                $backup = $this->backupService->createFullBackup($tenant, $options);
            } else {
                $backup = $this->backupService->createIncrementalBackup($tenant, $options);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'message_ar' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                'backup' => $backup->load(['creator', 'tenant']),
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء النسخة الاحتياطية: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get backup details
     */
    public function show($id)
    {
        try {
            $backup = TenantBackup::with(['creator', 'tenant', 'restoreLogs'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'backup' => $backup,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup not found',
                'message_ar' => 'النسخة الاحتياطية غير موجودة',
            ], 404);
        }
    }

    /**
     * Download backup file
     */
    public function download($id)
    {
        try {
            $backup = TenantBackup::findOrFail($id);

            if (!$backup->fileExists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found',
                    'message_ar' => 'ملف النسخة الاحتياطية غير موجود',
                ], 404);
            }

            if ($backup->status !== TenantBackup::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup is not completed',
                    'message_ar' => 'النسخة الاحتياطية غير مكتملة',
                ], 422);
            }

            // Log download access
            BackupRestoreLog::logOperation(
                $backup->tenant_id,
                BackupRestoreLog::OPERATION_VERIFICATION,
                'Backup file downloaded',
                $backup->id
            );

            return Storage::disk('backups')->download($backup->file_path, $backup->file_name);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download backup: ' . $e->getMessage(),
                'message_ar' => 'فشل في تحميل النسخة الاحتياطية: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'create_safety_backup' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $backup = TenantBackup::findOrFail($id);

            // Validate restore prerequisites
            $issues = $this->restoreService->validateRestorePrerequisites($backup);
            if (!empty($issues)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restore prerequisites not met',
                    'message_ar' => 'متطلبات الاستعادة غير مستوفاة',
                    'issues' => $issues,
                ], 422);
            }

            $options = [
                'create_safety_backup' => $request->get('create_safety_backup', true),
                'notes' => $request->notes,
                'performed_by' => auth()->id() ?? 1, // Default for testing
            ];

            $this->restoreService->restoreFromBackup($backup, $options);

            return response()->json([
                'success' => true,
                'message' => 'Restore completed successfully',
                'message_ar' => 'تمت الاستعادة بنجاح',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
                'message_ar' => 'فشلت الاستعادة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get restore preview
     */
    public function restorePreview($id)
    {
        try {
            $backup = TenantBackup::findOrFail($id);
            $preview = $this->restoreService->getRestorePreview($backup);

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get restore preview: ' . $e->getMessage(),
                'message_ar' => 'فشل في الحصول على معاينة الاستعادة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete backup
     */
    public function destroy($id)
    {
        try {
            $backup = TenantBackup::findOrFail($id);

            // Don't allow deletion of backups that are in progress
            if ($backup->status === TenantBackup::STATUS_IN_PROGRESS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete backup in progress',
                    'message_ar' => 'لا يمكن حذف النسخة الاحتياطية قيد التقدم',
                ], 422);
            }

            // Delete backup file
            $backup->deleteFile();

            // Delete backup record
            $backup->delete();

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
                'message_ar' => 'تم حذف النسخة الاحتياطية بنجاح',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage(),
                'message_ar' => 'فشل في حذف النسخة الاحتياطية: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get backup statistics
     */
    public function statistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $tenantId = $request->tenant_id;

            $backupStats = TenantBackup::getBackupStatistics($tenantId);
            $operationStats = BackupRestoreLog::getOperationStatistics($tenantId);
            $schedulerStats = $this->schedulerService->getSchedulerStatistics();

            return response()->json([
                'success' => true,
                'statistics' => [
                    'backups' => $backupStats,
                    'operations' => $operationStats,
                    'scheduler' => $schedulerStats,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage(),
                'message_ar' => 'فشل في الحصول على الإحصائيات: ' . $e->getMessage(),
            ], 500);
        }
    }
}
