@extends('layouts.app')

@section('title', 'Master Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">لوحة تحكم Master Admin</h1>
            <p class="text-gray-600 mt-1">إدارة شاملة لنظام MaxCon SaaS - المستأجرين والفوترة والنظام</p>
        </div>
        <div class="text-left">
            <p class="text-sm text-gray-500">آخر تحديث</p>
            <p class="text-sm font-medium text-gray-900">{{ now()->format('Y/m/d H:i') }}</p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('master-admin.dashboard') }}" class="flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors">
                <i class="fas fa-tachometer-alt ml-2"></i>
                <span>لوحة التحكم</span>
            </a>
            <a href="{{ route('master-admin.tenants.index') }}" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-building ml-2"></i>
                <span>إدارة المستأجرين</span>
            </a>
            <a href="{{ route('master-admin.tenants.create') }}" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-plus ml-2"></i>
                <span>إضافة مستأجر</span>
            </a>
            <a href="{{ route('master-admin.system.monitoring') }}" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-chart-line ml-2"></i>
                <span>مراقبة النظام</span>
            </a>
            <a href="{{ route('master-admin.reports.overview') }}" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-chart-bar ml-2"></i>
                <span>التقارير</span>
            </a>
        </div>
    </div>

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white mb-6">
        <div class="flex items-center space-x-4 space-x-reverse">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <i class="fas fa-crown text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold">مرحباً بك في لوحة تحكم Master Admin</h2>
                <p class="text-blue-100 mt-1">إدارة شاملة لنظام MaxCon SaaS - المستأجرين والفوترة والنظام</p>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Tenants -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المستأجرين</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_tenants'] }}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i>
                        {{ $stats['active_tenants'] }} نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المستخدمين</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-users"></i>
                        {{ $stats['active_users'] }} نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الإيرادات الشهرية</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['monthly_revenue']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">دينار عراقي</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- System Uptime -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">وقت تشغيل النظام</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['system_uptime'] }}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-check-circle"></i>
                        ممتاز
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-server text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Expired Tenants -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">تراخيص منتهية</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['expired_tenants'] }}</p>
                    <p class="text-sm text-red-600 mt-1">تحتاج تجديد</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
        </div>

        <!-- Pending Tenants -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">طلبات الانتظار</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_tenants'] }}</p>
                    <p class="text-sm text-orange-600 mt-1">في الانتظار</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600"></i>
                </div>
            </div>
        </div>

        <!-- Growth Rate -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">معدل النمو</p>
                    <p class="text-2xl font-bold text-green-600">+{{ $stats['growth_rate'] }}%</p>
                    <p class="text-sm text-green-600 mt-1">هذا الشهر</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- System Health -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">صحة النظام</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- CPU Usage -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">استخدام المعالج</span>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $systemHealth['cpu_usage'] }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $systemHealth['cpu_usage'] }}%</span>
                        </div>
                    </div>

                    <!-- Memory Usage -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">استخدام الذاكرة</span>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $systemHealth['memory_usage'] }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $systemHealth['memory_usage'] }}%</span>
                        </div>
                    </div>

                    <!-- Disk Usage -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">استخدام القرص</span>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $systemHealth['disk_usage'] }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $systemHealth['disk_usage'] }}%</span>
                        </div>
                    </div>

                    <!-- Active Sessions -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">الجلسات النشطة</span>
                        <span class="text-sm font-bold text-gray-900">{{ $systemHealth['active_sessions'] }}</span>
                    </div>

                    <!-- Response Time -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">وقت الاستجابة</span>
                        <span class="text-sm font-bold text-gray-900">{{ $systemHealth['response_time'] }}ms</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">النشاطات الحديثة</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach(array_slice($recentActivity, 0, 6) as $activity)
                    <div class="flex items-start space-x-3 space-x-reverse">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            @if($activity['color'] === 'green') bg-green-100 @elseif($activity['color'] === 'blue') bg-blue-100 @else bg-gray-100 @endif">
                            <i class="{{ $activity['icon'] }} text-sm
                                @if($activity['color'] === 'green') text-green-600 @elseif($activity['color'] === 'blue') text-blue-600 @else text-gray-600 @endif"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                            <p class="text-sm text-gray-600">{{ $activity['description'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $activity['time']->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if(count($alerts) > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">تنبيهات النظام</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($alerts as $alert)
                <div class="flex items-start space-x-3 space-x-reverse p-4 rounded-lg
                    @if($alert['type'] === 'danger') bg-red-50 border border-red-200 
                    @elseif($alert['type'] === 'warning') bg-yellow-50 border border-yellow-200 
                    @else bg-blue-50 border border-blue-200 @endif">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        @if($alert['type'] === 'danger') bg-red-100 
                        @elseif($alert['type'] === 'warning') bg-yellow-100 
                        @else bg-blue-100 @endif">
                        <i class="fas fa-exclamation-triangle text-sm
                            @if($alert['type'] === 'danger') text-red-600 
                            @elseif($alert['type'] === 'warning') text-yellow-600 
                            @else text-blue-600 @endif"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $alert['message'] }}</p>
                        @if(isset($alert['url']))
                        <a href="{{ $alert['url'] }}" class="text-sm font-medium mt-2 inline-block
                            @if($alert['type'] === 'danger') text-red-600 hover:text-red-800 
                            @elseif($alert['type'] === 'warning') text-yellow-600 hover:text-yellow-800 
                            @else text-blue-600 hover:text-blue-800 @endif">
                            {{ $alert['action'] }} <i class="fas fa-arrow-left mr-1"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('master-admin.tenants.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-plus text-blue-600 text-xl ml-3"></i>
                <span class="font-medium text-blue-900">إضافة مستأجر جديد</span>
            </a>
            <a href="{{ route('master-admin.tenants.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-building text-green-600 text-xl ml-3"></i>
                <span class="font-medium text-green-900">إدارة المستأجرين</span>
            </a>
            <a href="{{ route('master-admin.system.monitoring') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-chart-line text-purple-600 text-xl ml-3"></i>
                <span class="font-medium text-purple-900">مراقبة النظام</span>
            </a>
            <a href="{{ route('master-admin.reports.overview') }}" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                <i class="fas fa-chart-bar text-orange-600 text-xl ml-3"></i>
                <span class="font-medium text-orange-900">التقارير والتحليلات</span>
            </a>
        </div>
    </div>
</div>
<!-- End Container -->
@endsection

@push('scripts')
<script>
// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);

// Add any additional JavaScript for Master Admin Dashboard
console.log('Master Admin Dashboard Loaded');
</script>
@endpush
