<?php

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentCategory;
use App\Modules\Documents\Models\DocumentFolder;
use App\Modules\Documents\Models\DocumentActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentAnalyticsService
{
    /**
     * Generate Document Management Dashboard
     */
    public function generateDocumentDashboard($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'document_metrics' => $this->getDocumentMetrics($startDate, $endDate),
            'storage_metrics' => $this->getStorageMetrics(),
            'activity_metrics' => $this->getActivityMetrics($startDate, $endDate),
            'category_metrics' => $this->getCategoryMetrics(),
            'folder_metrics' => $this->getFolderMetrics(),
            'security_metrics' => $this->getSecurityMetrics(),
            'compliance_metrics' => $this->getComplianceMetrics(),
        ];
    }

    /**
     * Get Document Metrics
     */
    private function getDocumentMetrics($startDate, $endDate)
    {
        $totalDocuments = Document::count();
        $newDocuments = Document::whereBetween('created_at', [$startDate, $endDate])->count();
        $approvedDocuments = Document::byStatus(Document::STATUS_APPROVED)->count();
        $pendingDocuments = Document::byStatus(Document::STATUS_PENDING_REVIEW)->count();
        $expiredDocuments = Document::expired()->count();
        $expiringSoon = Document::expiringSoon()->count();
        $templates = Document::templates()->count();

        // Document status distribution
        $statusDistribution = Document::selectRaw('status, COUNT(*) as count')
                                    ->groupBy('status')
                                    ->pluck('count', 'status');

        // Document visibility distribution
        $visibilityDistribution = Document::selectRaw('visibility, COUNT(*) as count')
                                         ->groupBy('visibility')
                                         ->pluck('count', 'visibility');

        // Document type distribution
        $typeDistribution = Document::selectRaw('file_extension, COUNT(*) as count')
                                  ->groupBy('file_extension')
                                  ->orderByDesc('count')
                                  ->limit(10)
                                  ->pluck('count', 'file_extension');

        // Template type distribution
        $templateDistribution = Document::templates()
                                       ->selectRaw('template_type, COUNT(*) as count')
                                       ->whereNotNull('template_type')
                                       ->groupBy('template_type')
                                       ->pluck('count', 'template_type');

        return [
            'total_documents' => $totalDocuments,
            'new_documents' => $newDocuments,
            'approved_documents' => $approvedDocuments,
            'pending_documents' => $pendingDocuments,
            'expired_documents' => $expiredDocuments,
            'expiring_soon' => $expiringSoon,
            'templates' => $templates,
            'status_distribution' => $statusDistribution,
            'visibility_distribution' => $visibilityDistribution,
            'type_distribution' => $typeDistribution,
            'template_distribution' => $templateDistribution,
        ];
    }

    /**
     * Get Storage Metrics
     */
    private function getStorageMetrics()
    {
        $totalSize = Document::sum('file_size');
        $averageSize = Document::avg('file_size') ?? 0;
        $largestDocument = Document::orderByDesc('file_size')->first();
        
        // Storage by file type
        $storageByType = Document::selectRaw('file_extension, SUM(file_size) as total_size, COUNT(*) as count')
                               ->groupBy('file_extension')
                               ->orderByDesc('total_size')
                               ->get()
                               ->map(function ($item) {
                                   $item->average_size = $item->count > 0 ? $item->total_size / $item->count : 0;
                                   return $item;
                               });

        // Storage by category
        $storageByCategory = Document::join('document_categories', 'documents.category_id', '=', 'document_categories.id')
                                   ->selectRaw('document_categories.name, document_categories.name_ar, SUM(documents.file_size) as total_size, COUNT(documents.id) as count')
                                   ->groupBy('document_categories.id', 'document_categories.name', 'document_categories.name_ar')
                                   ->orderByDesc('total_size')
                                   ->get();

        return [
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'average_size' => $averageSize,
            'average_size_human' => $this->formatBytes($averageSize),
            'largest_document' => $largestDocument ? [
                'title' => $largestDocument->title,
                'size' => $largestDocument->file_size,
                'size_human' => $this->formatBytes($largestDocument->file_size),
            ] : null,
            'storage_by_type' => $storageByType,
            'storage_by_category' => $storageByCategory,
        ];
    }

    /**
     * Get Activity Metrics
     */
    private function getActivityMetrics($startDate, $endDate)
    {
        $totalActivities = DocumentActivity::whereBetween('activity_date', [$startDate, $endDate])->count();
        $totalDownloads = Document::sum('download_count');
        
        // Activity by type
        $activityByType = DocumentActivity::selectRaw('type, COUNT(*) as count')
                                         ->whereBetween('activity_date', [$startDate, $endDate])
                                         ->groupBy('type')
                                         ->orderByDesc('count')
                                         ->pluck('count', 'type');

        // Most active documents
        $mostActiveDocuments = DocumentActivity::join('documents', 'document_activities.document_id', '=', 'documents.id')
                                              ->selectRaw('documents.id, documents.title, documents.title_ar, COUNT(document_activities.id) as activity_count')
                                              ->whereBetween('document_activities.activity_date', [$startDate, $endDate])
                                              ->groupBy('documents.id', 'documents.title', 'documents.title_ar')
                                              ->orderByDesc('activity_count')
                                              ->limit(10)
                                              ->get();

        // Most downloaded documents
        $mostDownloaded = Document::where('download_count', '>', 0)
                                ->orderByDesc('download_count')
                                ->limit(10)
                                ->get(['id', 'title', 'title_ar', 'download_count']);

        // Daily activity trend
        $dailyActivity = DocumentActivity::selectRaw('DATE(activity_date) as date, COUNT(*) as count')
                                        ->whereBetween('activity_date', [$startDate, $endDate])
                                        ->groupBy('date')
                                        ->orderBy('date')
                                        ->get();

        return [
            'total_activities' => $totalActivities,
            'total_downloads' => $totalDownloads,
            'activity_by_type' => $activityByType,
            'most_active_documents' => $mostActiveDocuments,
            'most_downloaded' => $mostDownloaded,
            'daily_activity' => $dailyActivity,
        ];
    }

    /**
     * Get Category Metrics
     */
    private function getCategoryMetrics()
    {
        $totalCategories = DocumentCategory::count();
        $activeCategories = DocumentCategory::active()->count();
        
        // Categories with document counts
        $categoryStats = DocumentCategory::withCount('documents')
                                        ->orderByDesc('documents_count')
                                        ->get()
                                        ->map(function ($category) {
                                            return [
                                                'id' => $category->id,
                                                'name' => $category->name,
                                                'name_ar' => $category->name_ar,
                                                'document_count' => $category->documents_count,
                                                'color' => $category->color,
                                                'icon' => $category->icon,
                                            ];
                                        });

        return [
            'total_categories' => $totalCategories,
            'active_categories' => $activeCategories,
            'category_stats' => $categoryStats,
        ];
    }

    /**
     * Get Folder Metrics
     */
    private function getFolderMetrics()
    {
        $totalFolders = DocumentFolder::count();
        $sharedFolders = DocumentFolder::where('is_shared', true)->count();
        
        // Folders with document counts
        $folderStats = DocumentFolder::withCount('documents')
                                    ->orderByDesc('documents_count')
                                    ->get()
                                    ->map(function ($folder) {
                                        return [
                                            'id' => $folder->id,
                                            'name' => $folder->name,
                                            'name_ar' => $folder->name_ar,
                                            'document_count' => $folder->documents_count,
                                            'visibility' => $folder->visibility,
                                            'is_shared' => $folder->is_shared,
                                            'color' => $folder->color,
                                            'icon' => $folder->icon,
                                        ];
                                    });

        // Folder visibility distribution
        $visibilityDistribution = DocumentFolder::selectRaw('visibility, COUNT(*) as count')
                                               ->groupBy('visibility')
                                               ->pluck('count', 'visibility');

        return [
            'total_folders' => $totalFolders,
            'shared_folders' => $sharedFolders,
            'folder_stats' => $folderStats,
            'visibility_distribution' => $visibilityDistribution,
        ];
    }

    /**
     * Get Security Metrics
     */
    private function getSecurityMetrics()
    {
        $publicDocuments = Document::byVisibility(Document::VISIBILITY_PUBLIC)->count();
        $restrictedDocuments = Document::byVisibility(Document::VISIBILITY_RESTRICTED)->count();
        $privateDocuments = Document::byVisibility(Document::VISIBILITY_PRIVATE)->count();
        $internalDocuments = Document::byVisibility(Document::VISIBILITY_INTERNAL)->count();

        // Documents with permissions
        $documentsWithPermissions = Document::has('permissions')->count();
        
        // Documents with signatures
        $documentsWithSignatures = Document::has('signatures')->count();

        return [
            'public_documents' => $publicDocuments,
            'restricted_documents' => $restrictedDocuments,
            'private_documents' => $privateDocuments,
            'internal_documents' => $internalDocuments,
            'documents_with_permissions' => $documentsWithPermissions,
            'documents_with_signatures' => $documentsWithSignatures,
        ];
    }

    /**
     * Get Compliance Metrics
     */
    private function getComplianceMetrics()
    {
        $documentsWithExpiry = Document::whereNotNull('expires_at')->count();
        $expiredDocuments = Document::expired()->count();
        $expiringSoon = Document::expiringSoon()->count();
        
        // Documents by approval status
        $approvalStats = [
            'approved' => Document::byStatus(Document::STATUS_APPROVED)->count(),
            'pending_review' => Document::byStatus(Document::STATUS_PENDING_REVIEW)->count(),
            'rejected' => Document::byStatus(Document::STATUS_REJECTED)->count(),
            'draft' => Document::byStatus(Document::STATUS_DRAFT)->count(),
        ];

        // Upcoming expirations
        $upcomingExpirations = Document::expiringSoon()
                                     ->orderBy('expires_at')
                                     ->limit(10)
                                     ->get(['id', 'title', 'title_ar', 'expires_at']);

        return [
            'documents_with_expiry' => $documentsWithExpiry,
            'expired_documents' => $expiredDocuments,
            'expiring_soon' => $expiringSoon,
            'approval_stats' => $approvalStats,
            'upcoming_expirations' => $upcomingExpirations,
        ];
    }

    /**
     * Generate Document Usage Report
     */
    public function generateUsageReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        // User activity analysis
        $userActivity = DocumentActivity::join('users', 'document_activities.created_by', '=', 'users.id')
                                       ->selectRaw('users.id, users.name, COUNT(document_activities.id) as activity_count')
                                       ->whereBetween('document_activities.activity_date', [$startDate, $endDate])
                                       ->groupBy('users.id', 'users.name')
                                       ->orderByDesc('activity_count')
                                       ->limit(20)
                                       ->get();

        // Document creation trends
        $creationTrends = Document::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->groupBy('date')
                                ->orderBy('date')
                                ->get();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'user_activity' => $userActivity,
            'creation_trends' => $creationTrends,
        ];
    }

    /**
     * Helper method to format bytes
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
