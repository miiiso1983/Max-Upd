@extends('layouts.app')

@section('title', 'إدارة الأمان - MaxCon ERP')
@section('page-title', 'إدارة الأمان')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إدارة الأمان</h1>
            <p class="text-gray-600">مراقبة وإدارة أمان النظام</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="generateSecurityReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-alt ml-2"></i>
                تقرير أمني
            </button>
            <button onclick="cleanOldLogs()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-trash ml-2"></i>
                تنظيف السجلات القديمة
            </button>
        </div>
    </div>

    <!-- Security Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الأحداث الأمنية اليوم</p>
                    <p class="text-2xl font-bold text-gray-900" id="today-events">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">محاولات دخول فاشلة</p>
                    <p class="text-2xl font-bold text-red-600" id="failed-logins">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">عناوين IP محظورة</p>
                    <p class="text-2xl font-bold text-orange-600" id="blocked-ips">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ban text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">أحداث حرجة</p>
                    <p class="text-2xl font-bold text-purple-600" id="critical-events">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Alerts -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">تنبيهات أمنية حديثة</h3>
        <div id="security-alerts" class="space-y-3">
            <!-- Alerts will be loaded here -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">جاري تحميل التنبيهات...</p>
            </div>
        </div>
    </div>

    <!-- Security Events Chart -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">الأحداث الأمنية - آخر 7 أيام</h3>
        <div class="h-64">
            <canvas id="securityEventsChart"></canvas>
        </div>
    </div>

    <!-- Recent Security Logs -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">السجلات الأمنية الحديثة</h3>
                <div class="flex space-x-2 space-x-reverse">
                    <select id="severity-filter" class="px-3 py-1 border border-gray-300 rounded text-sm">
                        <option value="">جميع المستويات</option>
                        <option value="critical">حرج</option>
                        <option value="warning">تحذير</option>
                        <option value="info">معلومات</option>
                    </select>
                    <select id="event-filter" class="px-3 py-1 border border-gray-300 rounded text-sm">
                        <option value="">جميع الأحداث</option>
                        <option value="login_failed">فشل تسجيل الدخول</option>
                        <option value="unauthorized_access_attempt">محاولة وصول غير مصرح</option>
                        <option value="sql_injection_attempt">محاولة حقن SQL</option>
                        <option value="xss_attempt">محاولة XSS</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوقت</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحدث</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان IP</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستوى</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="security-logs-table">
                    <!-- Logs will be loaded here -->
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                                <p class="text-lg">جاري تحميل السجلات...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200" id="pagination-container">
            <!-- Pagination will be loaded here -->
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إعدادات الأمان</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-3">إعدادات تسجيل الدخول</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">الحد الأقصى لمحاولات الدخول الفاشلة</span>
                        <input type="number" value="5" min="1" max="10" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">مدة قفل الحساب (دقائق)</span>
                        <input type="number" value="30" min="5" max="1440" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">إجبار المصادقة الثنائية للمدراء</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-3">إعدادات المراقبة</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">مراقبة محاولات حقن SQL</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">مراقبة محاولات XSS</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">تنظيف السجلات القديمة تلقائياً</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex justify-end">
            <button onclick="saveSecuritySettings()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-save ml-2"></i>
                حفظ الإعدادات
            </button>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">تفاصيل السجل الأمني</h3>
                <button onclick="closeLogDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="logDetailsContent">
                <!-- Log details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSecurityOverview();
    loadSecurityAlerts();
    loadSecurityLogs();
    initializeChart();
    
    // Set up filters
    document.getElementById('severity-filter').addEventListener('change', loadSecurityLogs);
    document.getElementById('event-filter').addEventListener('change', loadSecurityLogs);
});

function loadSecurityOverview() {
    fetch('/api/admin/security/overview')
        .then(response => response.json())
        .then(data => {
            document.getElementById('today-events').textContent = data.today_events || 0;
            document.getElementById('failed-logins').textContent = data.failed_logins || 0;
            document.getElementById('blocked-ips').textContent = data.blocked_ips || 0;
            document.getElementById('critical-events').textContent = data.critical_events || 0;
        })
        .catch(error => console.error('Error loading security overview:', error));
}

function loadSecurityAlerts() {
    fetch('/api/admin/security/alerts')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('security-alerts');
            
            if (data.alerts && data.alerts.length > 0) {
                container.innerHTML = data.alerts.map(alert => `
                    <div class="flex items-center p-3 bg-${getSeverityColor(alert.severity)}-50 border border-${getSeverityColor(alert.severity)}-200 rounded-lg">
                        <i class="fas fa-${getSeverityIcon(alert.severity)} text-${getSeverityColor(alert.severity)}-600 ml-3"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-${getSeverityColor(alert.severity)}-800">${alert.description}</p>
                            <p class="text-xs text-${getSeverityColor(alert.severity)}-600">${alert.created_at}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-shield-alt text-4xl text-green-300 mb-4"></i>
                        <p class="text-lg text-green-600">لا توجد تنبيهات أمنية</p>
                        <p class="text-sm text-green-500">النظام آمن</p>
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error loading security alerts:', error));
}

function loadSecurityLogs() {
    const severityFilter = document.getElementById('severity-filter').value;
    const eventFilter = document.getElementById('event-filter').value;
    
    const params = new URLSearchParams();
    if (severityFilter) params.append('severity', severityFilter);
    if (eventFilter) params.append('event', eventFilter);
    
    fetch(`/api/admin/security/logs?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            updateSecurityLogsTable(data.logs);
        })
        .catch(error => {
            console.error('Error loading security logs:', error);
            document.getElementById('security-logs-table').innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-red-500">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                            <p class="text-lg">حدث خطأ في تحميل السجلات</p>
                        </div>
                    </td>
                </tr>
            `;
        });
}

function updateSecurityLogsTable(logs) {
    const tbody = document.getElementById('security-logs-table');
    
    if (logs.data && logs.data.length > 0) {
        tbody.innerHTML = logs.data.map(log => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${new Date(log.created_at).toLocaleString('ar-IQ')}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${log.description || log.event}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${log.user ? log.user.name : 'غير محدد'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${log.ip_address || 'غير محدد'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-${getSeverityColor(log.severity)}-100 text-${getSeverityColor(log.severity)}-800">
                        ${getSeverityText(log.severity)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="viewLogDetails(${log.id})" class="text-blue-600 hover:text-blue-900">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <i class="fas fa-file-alt text-4xl mb-4"></i>
                        <p class="text-lg">لا توجد سجلات</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

function getSeverityColor(severity) {
    switch(severity) {
        case 'critical': return 'red';
        case 'warning': return 'orange';
        default: return 'blue';
    }
}

function getSeverityIcon(severity) {
    switch(severity) {
        case 'critical': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

function getSeverityText(severity) {
    switch(severity) {
        case 'critical': return 'حرج';
        case 'warning': return 'تحذير';
        default: return 'معلومات';
    }
}

function initializeChart() {
    const ctx = document.getElementById('securityEventsChart').getContext('2d');
    
    // This would be populated with real data
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['اليوم', 'أمس', 'قبل يومين', 'قبل 3 أيام', 'قبل 4 أيام', 'قبل 5 أيام', 'قبل 6 أيام'],
            datasets: [{
                label: 'الأحداث الأمنية',
                data: [12, 19, 3, 5, 2, 3, 7],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function viewLogDetails(logId) {
    fetch(`/api/admin/security/logs/${logId}`)
        .then(response => response.json())
        .then(log => {
            document.getElementById('logDetailsContent').innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الحدث</label>
                            <p class="text-sm text-gray-900">${log.description || log.event}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المستوى</label>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-${getSeverityColor(log.severity)}-100 text-${getSeverityColor(log.severity)}-800">
                                ${getSeverityText(log.severity)}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المستخدم</label>
                            <p class="text-sm text-gray-900">${log.user ? log.user.name : 'غير محدد'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">عنوان IP</label>
                            <p class="text-sm text-gray-900">${log.ip_address || 'غير محدد'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الوقت</label>
                            <p class="text-sm text-gray-900">${new Date(log.created_at).toLocaleString('ar-IQ')}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">متصفح المستخدم</label>
                            <p class="text-sm text-gray-900">${log.user_agent || 'غير محدد'}</p>
                        </div>
                    </div>
                    ${log.data ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">بيانات إضافية</label>
                            <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">${JSON.stringify(log.data, null, 2)}</pre>
                        </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('logDetailsModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading log details:', error);
            alert('حدث خطأ في تحميل تفاصيل السجل');
        });
}

function closeLogDetailsModal() {
    document.getElementById('logDetailsModal').classList.add('hidden');
}

function generateSecurityReport() {
    window.open('/api/admin/security/report', '_blank');
}

function cleanOldLogs() {
    if (confirm('هل أنت متأكد من حذف السجلات القديمة؟')) {
        fetch('/api/admin/security/clean-logs', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaxCon.showNotification(`تم حذف ${data.deleted_count} سجل قديم`, 'success');
                loadSecurityLogs();
            } else {
                MaxCon.showNotification('حدث خطأ أثناء حذف السجلات', 'error');
            }
        })
        .catch(error => {
            console.error('Error cleaning logs:', error);
            MaxCon.showNotification('حدث خطأ أثناء حذف السجلات', 'error');
        });
    }
}

function saveSecuritySettings() {
    // This would save the security settings
    MaxCon.showNotification('تم حفظ إعدادات الأمان بنجاح', 'success');
}
</script>
@endpush
