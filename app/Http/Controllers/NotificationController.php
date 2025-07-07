<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display notifications page
     */
    public function index()
    {
        return view('notifications.index');
    }

    /**
     * Get user notifications
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        $query = Notification::where('user_id', $user->id)
                           ->active()
                           ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('read_status')) {
            if ($request->read_status === 'unread') {
                $query->unread();
            } elseif ($request->read_status === 'read') {
                $query->read();
            }
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->paginate(20);

        // Add computed attributes
        $notifications->getCollection()->transform(function ($notification) {
            $notification->icon = $notification->icon;
            $notification->color = $notification->color;
            $notification->time_ago = $notification->time_ago;
            return $notification;
        });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        $count = NotificationService::getUnreadCount(Auth::id());
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for header dropdown
     */
    public function getRecent()
    {
        $notifications = NotificationService::getRecent(Auth::id(), 5);
        
        $notifications->transform(function ($notification) {
            $notification->icon = $notification->icon;
            $notification->color = $notification->color;
            $notification->time_ago = $notification->time_ago;
            return $notification;
        });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure user owns the notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد الإشعار كمقروء'
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Request $request, Notification $notification)
    {
        // Ensure user owns the notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد الإشعار كغير مقروء'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = NotificationService::markAllAsRead(Auth::id());

        return response()->json([
            'success' => true,
            'message' => "تم تحديد {$count} إشعار كمقروء",
            'count' => $count
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        // Ensure user owns the notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار'
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $count = Notification::where('user_id', Auth::id())
                           ->whereNotNull('read_at')
                           ->delete();

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$count} إشعار مقروء",
            'count' => $count
        ]);
    }

    /**
     * Get notification settings for user
     */
    public function getSettings()
    {
        $user = Auth::user();
        $settings = $user->notification_settings ?? [
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'low_stock_alerts' => true,
            'expiry_alerts' => true,
            'new_order_alerts' => true,
            'payment_alerts' => true,
            'system_alerts' => true,
        ];

        return response()->json(['settings' => $settings]);
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'low_stock_alerts' => 'boolean',
            'expiry_alerts' => 'boolean',
            'new_order_alerts' => 'boolean',
            'payment_alerts' => 'boolean',
            'system_alerts' => 'boolean',
        ]);

        $user = Auth::user();
        $user->update([
            'notification_settings' => $request->only([
                'email_notifications',
                'sms_notifications',
                'push_notifications',
                'low_stock_alerts',
                'expiry_alerts',
                'new_order_alerts',
                'payment_alerts',
                'system_alerts',
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ إعدادات الإشعارات'
        ]);
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'type' => 'required|in:info,success,warning,error,system',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'channels' => 'array',
            'channels.*' => 'in:database,email,sms,push,whatsapp',
        ]);

        $user = Auth::user();
        
        $notification = NotificationService::send(
            $user,
            $request->type,
            $request->title,
            $request->message,
            ['test' => true],
            [
                'channels' => $request->channels ?? ['database'],
                'priority' => Notification::PRIORITY_NORMAL,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الإشعار التجريبي',
            'notification' => $notification
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStatistics()
    {
        $user = Auth::user();
        
        $stats = [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->unread()->count(),
            'today' => Notification::where('user_id', $user->id)
                                 ->whereDate('created_at', today())
                                 ->count(),
            'this_week' => Notification::where('user_id', $user->id)
                                     ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                                     ->count(),
            'by_type' => Notification::where('user_id', $user->id)
                                   ->selectRaw('type, COUNT(*) as count')
                                   ->groupBy('type')
                                   ->pluck('count', 'type'),
            'by_priority' => Notification::where('user_id', $user->id)
                                       ->selectRaw('priority, COUNT(*) as count')
                                       ->groupBy('priority')
                                       ->pluck('count', 'priority'),
        ];

        return response()->json(['statistics' => $stats]);
    }

    /**
     * Export notifications
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->id)
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        if ($format === 'pdf') {
            return $this->exportPdf($notifications);
        } else {
            return $this->exportExcel($notifications);
        }
    }

    /**
     * Export notifications as PDF
     */
    private function exportPdf($notifications)
    {
        // This would use DomPDF to generate PDF
        return response()->json([
            'message' => 'PDF export functionality will be implemented with DomPDF'
        ]);
    }

    /**
     * Export notifications as Excel
     */
    private function exportExcel($notifications)
    {
        // This would use Laravel Excel to generate Excel file
        return response()->json([
            'message' => 'Excel export functionality will be implemented with Laravel Excel'
        ]);
    }

    /**
     * Clean expired notifications (admin only)
     */
    public function cleanExpired()
    {
        $count = NotificationService::cleanExpired();

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$count} إشعار منتهي الصلاحية",
            'count' => $count
        ]);
    }
}
