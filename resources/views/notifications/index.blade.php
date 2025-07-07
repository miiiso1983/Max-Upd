@extends('layouts.app')

@section('title', 'الإشعارات - MaxCon ERP')
@section('page-title', 'الإشعارات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">الإشعارات</h1>
            <p class="text-gray-600">إدارة إشعاراتك وتنبيهاتك</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="markAllAsRead()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-check-double ml-2"></i>
                تحديد الكل كمقروء
            </button>
            <button onclick="deleteAllRead()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-trash ml-2"></i>
                حذف المقروءة
            </button>
            <button onclick="showSettings()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-cog ml-2"></i>
                الإعدادات
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي الإشعارات</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-notifications">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bell text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">غير مقروءة</p>
                    <p class="text-2xl font-bold text-orange-600" id="unread-notifications">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">اليوم</p>
                    <p class="text-2xl font-bold text-green-600" id="today-notifications">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">هذا الأسبوع</p>
                    <p class="text-2xl font-bold text-purple-600" id="week-notifications">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-week text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
                <select id="type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الأنواع</option>
                    <option value="info">معلومات</option>
                    <option value="success">نجح</option>
                    <option value="warning">تحذير</option>
                    <option value="error">خطأ</option>
                    <option value="system">نظام</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <select id="read-status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="unread">غير مقروءة</option>
                    <option value="read">مقروءة</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الأولوية</label>
                <select id="priority-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الأولويات</option>
                    <option value="low">منخفض</option>
                    <option value="normal">عادي</option>
                    <option value="high">عالي</option>
                    <option value="urgent">عاجل</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-filter ml-1"></i>
                    تطبيق الفلاتر
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة الإشعارات</h3>
        </div>
        
        <div id="notifications-container" class="divide-y divide-gray-200">
            <!-- Notifications will be loaded here -->
            <div class="p-12 text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-lg text-gray-500">جاري تحميل الإشعارات...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200" id="pagination-container">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

<!-- Notification Settings Modal -->
<div id="settingsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">إعدادات الإشعارات</h3>
                <button onclick="closeSettings()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="settings-form" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">قنوات الإشعارات</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">إشعارات البريد الإلكتروني</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">الرسائل النصية</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="sms_notifications" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">الإشعارات الفورية</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="push_notifications" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">أنواع التنبيهات</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">تنبيهات المخزون المنخفض</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="low_stock_alerts" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">تنبيهات انتهاء الصلاحية</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="expiry_alerts" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">تنبيهات الطلبات الجديدة</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="new_order_alerts" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">تنبيهات المدفوعات</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="payment_alerts" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">تنبيهات النظام</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="system_alerts" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 space-x-reverse">
                    <button type="button" onclick="closeSettings()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        حفظ الإعدادات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadNotifications();
    
    // Set up filter event listeners
    document.getElementById('type-filter').addEventListener('change', applyFilters);
    document.getElementById('read-status-filter').addEventListener('change', applyFilters);
    document.getElementById('priority-filter').addEventListener('change', applyFilters);
    
    // Set up settings form
    document.getElementById('settings-form').addEventListener('submit', saveSettings);
});

function loadStatistics() {
    fetch('/api/notifications/statistics')
        .then(response => response.json())
        .then(data => {
            const stats = data.statistics;
            document.getElementById('total-notifications').textContent = stats.total || 0;
            document.getElementById('unread-notifications').textContent = stats.unread || 0;
            document.getElementById('today-notifications').textContent = stats.today || 0;
            document.getElementById('week-notifications').textContent = stats.this_week || 0;
        })
        .catch(error => console.error('Error loading statistics:', error));
}

function loadNotifications() {
    const params = new URLSearchParams();
    
    const typeFilter = document.getElementById('type-filter').value;
    const readStatusFilter = document.getElementById('read-status-filter').value;
    const priorityFilter = document.getElementById('priority-filter').value;
    
    if (typeFilter) params.append('type', typeFilter);
    if (readStatusFilter) params.append('read_status', readStatusFilter);
    if (priorityFilter) params.append('priority', priorityFilter);
    
    fetch(`/api/notifications?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            updateNotificationsList(data.notifications);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notifications-container').innerHTML = `
                <div class="p-12 text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-lg text-red-500">حدث خطأ في تحميل الإشعارات</p>
                </div>
            `;
        });
}

function updateNotificationsList(notifications) {
    const container = document.getElementById('notifications-container');
    
    if (notifications.data && notifications.data.length > 0) {
        container.innerHTML = notifications.data.map(notification => `
            <div class="p-6 hover:bg-gray-50 ${notification.read_at ? 'opacity-75' : ''}" data-notification-id="${notification.id}">
                <div class="flex items-start space-x-4 space-x-reverse">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-${notification.color}-100 rounded-full flex items-center justify-center">
                            <i class="${notification.icon} text-${notification.color}-600"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900 truncate">${notification.title}</h4>
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <span class="text-xs text-gray-500">${notification.time_ago}</span>
                                ${getPriorityBadge(notification.priority)}
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex space-x-2 space-x-reverse">
                                ${notification.action_url ? `
                                    <a href="${notification.action_url}" class="text-xs text-blue-600 hover:text-blue-800">
                                        ${notification.action_text || 'عرض التفاصيل'}
                                    </a>
                                ` : ''}
                            </div>
                            <div class="flex space-x-1 space-x-reverse">
                                ${!notification.read_at ? `
                                    <button onclick="markAsRead(${notification.id})" class="text-xs text-green-600 hover:text-green-800">
                                        <i class="fas fa-check ml-1"></i>
                                        تحديد كمقروء
                                    </button>
                                ` : `
                                    <button onclick="markAsUnread(${notification.id})" class="text-xs text-orange-600 hover:text-orange-800">
                                        <i class="fas fa-undo ml-1"></i>
                                        تحديد كغير مقروء
                                    </button>
                                `}
                                <button onclick="deleteNotification(${notification.id})" class="text-xs text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash ml-1"></i>
                                    حذف
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="p-12 text-center">
                <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
                <p class="text-lg text-gray-500">لا توجد إشعارات</p>
                <p class="text-sm text-gray-400">ستظهر إشعاراتك هنا عند وصولها</p>
            </div>
        `;
    }
}

function getPriorityBadge(priority) {
    const badges = {
        'low': '<span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">منخفض</span>',
        'normal': '<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">عادي</span>',
        'high': '<span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">عالي</span>',
        'urgent': '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">عاجل</span>'
    };
    return badges[priority] || badges['normal'];
}

function applyFilters() {
    loadNotifications();
}

function markAsRead(notificationId) {
    fetch(`/api/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification(data.message, 'success');
            loadNotifications();
            loadStatistics();
        }
    })
    .catch(error => console.error('Error marking as read:', error));
}

function markAsUnread(notificationId) {
    fetch(`/api/notifications/${notificationId}/mark-as-unread`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification(data.message, 'success');
            loadNotifications();
            loadStatistics();
        }
    })
    .catch(error => console.error('Error marking as unread:', error));
}

function markAllAsRead() {
    fetch('/api/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification(data.message, 'success');
            loadNotifications();
            loadStatistics();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

function deleteNotification(notificationId) {
    if (confirm('هل أنت متأكد من حذف هذا الإشعار؟')) {
        fetch(`/api/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaxCon.showNotification(data.message, 'success');
                loadNotifications();
                loadStatistics();
            }
        })
        .catch(error => console.error('Error deleting notification:', error));
    }
}

function deleteAllRead() {
    if (confirm('هل أنت متأكد من حذف جميع الإشعارات المقروءة؟')) {
        fetch('/api/notifications/delete-all-read', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaxCon.showNotification(data.message, 'success');
                loadNotifications();
                loadStatistics();
            }
        })
        .catch(error => console.error('Error deleting read notifications:', error));
    }
}

function showSettings() {
    // Load current settings
    fetch('/api/notifications/settings')
        .then(response => response.json())
        .then(data => {
            const settings = data.settings;
            const form = document.getElementById('settings-form');
            
            // Set checkbox values
            Object.keys(settings).forEach(key => {
                const checkbox = form.querySelector(`input[name="${key}"]`);
                if (checkbox) {
                    checkbox.checked = settings[key];
                }
            });
            
            document.getElementById('settingsModal').classList.remove('hidden');
        })
        .catch(error => console.error('Error loading settings:', error));
}

function closeSettings() {
    document.getElementById('settingsModal').classList.add('hidden');
}

function saveSettings(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const settings = {};
    
    // Convert form data to settings object
    ['email_notifications', 'sms_notifications', 'push_notifications', 
     'low_stock_alerts', 'expiry_alerts', 'new_order_alerts', 
     'payment_alerts', 'system_alerts'].forEach(key => {
        settings[key] = formData.has(key);
    });
    
    fetch('/api/notifications/settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification(data.message, 'success');
            closeSettings();
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        MaxCon.showNotification('حدث خطأ في حفظ الإعدادات', 'error');
    });
}
</script>
@endpush
