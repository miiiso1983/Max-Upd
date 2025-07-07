<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to user
     */
    public static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): Notification {
        $notification = Notification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'priority' => $options['priority'] ?? Notification::PRIORITY_NORMAL,
            'action_url' => $options['action_url'] ?? null,
            'action_text' => $options['action_text'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'channels' => $options['channels'] ?? [Notification::CHANNEL_DATABASE],
            'notifiable_type' => $options['notifiable_type'] ?? null,
            'notifiable_id' => $options['notifiable_id'] ?? null,
        ]);

        // Send through specified channels
        self::sendThroughChannels($notification, $options['channels'] ?? [Notification::CHANNEL_DATABASE]);

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public static function sendToUsers(
        array $users,
        string $type,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): array {
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = self::send($user, $type, $title, $message, $data, $options);
        }

        return $notifications;
    }

    /**
     * Send notification to all users in tenant
     */
    public static function sendToTenant(
        Tenant $tenant,
        string $type,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): array {
        $users = $tenant->users()->where('is_active', true)->get();
        return self::sendToUsers($users->toArray(), $type, $title, $message, $data, $options);
    }

    /**
     * Send notification to users with specific role
     */
    public static function sendToRole(
        string $role,
        string $type,
        string $title,
        string $message,
        array $data = [],
        array $options = [],
        ?Tenant $tenant = null
    ): array {
        $query = User::role($role)->where('is_active', true);
        
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }
        
        $users = $query->get();
        return self::sendToUsers($users->toArray(), $type, $title, $message, $data, $options);
    }

    /**
     * Send through specified channels
     */
    private static function sendThroughChannels(Notification $notification, array $channels): void
    {
        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case Notification::CHANNEL_EMAIL:
                        self::sendEmail($notification);
                        break;
                    case Notification::CHANNEL_SMS:
                        self::sendSms($notification);
                        break;
                    case Notification::CHANNEL_PUSH:
                        self::sendPush($notification);
                        break;
                    case Notification::CHANNEL_WHATSAPP:
                        self::sendWhatsApp($notification);
                        break;
                    case Notification::CHANNEL_DATABASE:
                        // Already stored in database
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification through {$channel}", [
                    'notification_id' => $notification->id,
                    'channel' => $channel,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send email notification
     */
    private static function sendEmail(Notification $notification): void
    {
        if (!$notification->user->email) {
            return;
        }

        // This would use a proper email template
        Mail::raw($notification->message, function ($message) use ($notification) {
            $message->to($notification->user->email)
                   ->subject($notification->title);
        });
    }

    /**
     * Send SMS notification
     */
    private static function sendSms(Notification $notification): void
    {
        if (!$notification->user->phone) {
            return;
        }

        // This would integrate with SMS service provider
        Log::info("SMS notification sent", [
            'phone' => $notification->user->phone,
            'message' => $notification->message,
        ]);
    }

    /**
     * Send push notification
     */
    private static function sendPush(Notification $notification): void
    {
        // This would integrate with push notification service
        Log::info("Push notification sent", [
            'user_id' => $notification->user_id,
            'title' => $notification->title,
            'message' => $notification->message,
        ]);
    }

    /**
     * Send WhatsApp notification
     */
    private static function sendWhatsApp(Notification $notification): void
    {
        if (!$notification->user->phone) {
            return;
        }

        // This would integrate with WhatsApp Business API
        Log::info("WhatsApp notification sent", [
            'phone' => $notification->user->phone,
            'message' => $notification->message,
        ]);
    }

    /**
     * Create system notification
     */
    public static function system(
        User $user,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): Notification {
        return self::send(
            $user,
            Notification::TYPE_SYSTEM,
            $title,
            $message,
            $data,
            array_merge($options, ['priority' => Notification::PRIORITY_HIGH])
        );
    }

    /**
     * Create success notification
     */
    public static function success(
        User $user,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): Notification {
        return self::send($user, Notification::TYPE_SUCCESS, $title, $message, $data, $options);
    }

    /**
     * Create warning notification
     */
    public static function warning(
        User $user,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): Notification {
        return self::send(
            $user,
            Notification::TYPE_WARNING,
            $title,
            $message,
            $data,
            array_merge($options, ['priority' => Notification::PRIORITY_HIGH])
        );
    }

    /**
     * Create error notification
     */
    public static function error(
        User $user,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): Notification {
        return self::send(
            $user,
            Notification::TYPE_ERROR,
            $title,
            $message,
            $data,
            array_merge($options, ['priority' => Notification::PRIORITY_URGENT])
        );
    }

    /**
     * Create info notification
     */
    public static function info(
        User $user,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ): Notification {
        return self::send($user, Notification::TYPE_INFO, $title, $message, $data, $options);
    }

    /**
     * Notify about low stock
     */
    public static function lowStock(User $user, $product, int $currentStock): Notification
    {
        return self::warning(
            $user,
            'تنبيه مخزون منخفض',
            "المنتج {$product->name} وصل إلى مستوى مخزون منخفض ({$currentStock} وحدة)",
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $currentStock,
                'minimum_stock' => $product->minimum_stock,
            ],
            [
                'action_url' => "/inventory/products/{$product->id}",
                'action_text' => 'عرض المنتج',
                'notifiable_type' => get_class($product),
                'notifiable_id' => $product->id,
            ]
        );
    }

    /**
     * Notify about expiring products
     */
    public static function expiringProduct(User $user, $product, $expiryDate): Notification
    {
        return self::warning(
            $user,
            'تنبيه انتهاء صلاحية',
            "المنتج {$product->name} سينتهي في {$expiryDate->format('Y-m-d')}",
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'expiry_date' => $expiryDate->format('Y-m-d'),
            ],
            [
                'action_url' => "/inventory/products/{$product->id}",
                'action_text' => 'عرض المنتج',
                'notifiable_type' => get_class($product),
                'notifiable_id' => $product->id,
            ]
        );
    }

    /**
     * Notify about new order
     */
    public static function newOrder(User $user, $order): Notification
    {
        return self::info(
            $user,
            'طلب جديد',
            "تم إنشاء طلب جديد رقم {$order->order_number}",
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name,
                'total_amount' => $order->total_amount,
            ],
            [
                'action_url' => "/sales/orders/{$order->id}",
                'action_text' => 'عرض الطلب',
                'notifiable_type' => get_class($order),
                'notifiable_id' => $order->id,
            ]
        );
    }

    /**
     * Notify about payment received
     */
    public static function paymentReceived(User $user, $payment): Notification
    {
        return self::success(
            $user,
            'تم استلام دفعة',
            "تم استلام دفعة بمبلغ {$payment->amount} د.ع من {$payment->customer->name}",
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'customer_name' => $payment->customer->name,
                'payment_method' => $payment->payment_method,
            ],
            [
                'action_url' => "/sales/payments/{$payment->id}",
                'action_text' => 'عرض الدفعة',
                'notifiable_type' => get_class($payment),
                'notifiable_id' => $payment->id,
            ]
        );
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
                                  ->where('user_id', $userId)
                                  ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for user
     */
    public static function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
                         ->whereNull('read_at')
                         ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications count for user
     */
    public static function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
                         ->unread()
                         ->active()
                         ->count();
    }

    /**
     * Get recent notifications for user
     */
    public static function getRecent(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::where('user_id', $userId)
                         ->active()
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Clean expired notifications
     */
    public static function cleanExpired(): int
    {
        return Notification::where('expires_at', '<', now())->delete();
    }
}
