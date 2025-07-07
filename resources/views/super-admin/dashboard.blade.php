@extends('layouts.app')

@section('title', 'لوحة تحكم المدير العام - MaxCon ERP')
@section('page-title', 'لوحة تحكم المدير العام')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">مرحباً بك في لوحة تحكم المدير العام</h1>
                <p class="text-purple-100">إدارة شاملة لجميع المستأجرين والنظام</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-crown"></i>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Tenants -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المستأجرين</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-tenants">{{ $stats['total_tenants'] ?? 0 }}</p>
                    <p class="text-xs text-blue-600 flex items-center mt-1">
                        <i class="fas fa-building ml-1"></i>
                        جميع الشركات
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">المستأجرين النشطين</p>
                    <p class="text-2xl font-bold text-gray-900" id="active-tenants">{{ $stats['active_tenants'] ?? 0 }}</p>
                    <p class="text-xs text-green-600 flex items-center mt-1">
                        <i class="fas fa-check-circle ml-1"></i>
                        يعملون حالياً
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Expired Licenses -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">التراخيص المنتهية</p>
                    <p class="text-2xl font-bold text-gray-900" id="expired-licenses">{{ $stats['expired_licenses'] ?? 0 }}</p>
                    <p class="text-xs text-red-600 flex items-center mt-1">
                        <i class="fas fa-exclamation-triangle ml-1"></i>
                        تحتاج تجديد
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المستخدمين</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-users">{{ $stats['total_users'] ?? 0 }}</p>
                    <p class="text-xs text-purple-600 flex items-center mt-1">
                        <i class="fas fa-users ml-1"></i>
                        جميع الأنظمة
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tenants by Type -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المستأجرين حسب النوع</h3>
            <div class="space-y-3">
                @if(isset($stats['tenants_by_type']))
                    @foreach($stats['tenants_by_type'] as $type)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full ml-2"></div>
                            <span class="text-sm text-gray-600">
                                @switch($type->company_type)
                                    @case('pharmacy')
                                        صيدلية
                                        @break
                                    @case('medical_distributor')
                                        موزع طبي
                                        @break
                                    @case('clinic')
                                        عيادة
                                        @break
                                    @case('hospital')
                                        مستشفى
                                        @break
                                    @default
                                        أخرى
                                @endswitch
                            </span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $type->count }}</span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Tenants by Governorate -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المستأجرين حسب المحافظة</h3>
            <div class="space-y-3">
                @if(isset($stats['tenants_by_governorate']))
                    @foreach($stats['tenants_by_governorate']->take(5) as $gov)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full ml-2"></div>
                            <span class="text-sm text-gray-600">{{ $gov->governorate }}</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $gov->count }}</span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Activity and Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Tenants -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">المستأجرين الجدد</h3>
                <a href="{{ route('super-admin.tenants.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    عرض الكل
                </a>
            </div>
            <div class="space-y-3">
                @if(isset($recentTenants))
                    @foreach($recentTenants->take(5) as $tenant)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                                <i class="fas fa-building text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $tenant->company_name }}</p>
                                <p class="text-xs text-gray-500">{{ $tenant->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tenant->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- System Alerts -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">تنبيهات النظام</h3>
                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full" id="alerts-count">
                    {{ isset($alerts) ? count($alerts) : 0 }}
                </span>
            </div>
            <div class="space-y-3" id="alerts-container">
                @if(isset($alerts))
                    @foreach(array_slice($alerts, 0, 5) as $alert)
                    <div class="flex items-start p-3 bg-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-50 rounded-lg">
                        <div class="w-6 h-6 bg-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-100 rounded-full flex items-center justify-center ml-3 mt-0.5">
                            <i class="fas fa-{{ $alert['type'] === 'danger' ? 'exclamation-triangle' : ($alert['type'] === 'warning' ? 'exclamation-circle' : 'info-circle') }} text-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</p>
                            <p class="text-xs text-gray-600">{{ $alert['message'] }}</p>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('super-admin.tenants.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-plus text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">إضافة مستأجر جديد</p>
                    <p class="text-xs text-gray-600">إنشاء حساب جديد</p>
                </div>
            </a>
            
            <a href="{{ route('super-admin.tenants.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-list text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">إدارة المستأجرين</p>
                    <p class="text-xs text-gray-600">عرض وتعديل الحسابات</p>
                </div>
            </a>
            
            <button onclick="refreshDashboard()" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-sync text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">تحديث البيانات</p>
                    <p class="text-xs text-gray-600">إعادة تحميل الإحصائيات</p>
                </div>
            </button>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    // Add loading state
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    icon.classList.add('fa-spin');
    
    // Simulate refresh (you can implement actual API call here)
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        // You can add actual refresh logic here
        location.reload();
    }, 1000);
}

// Auto-refresh dashboard every 5 minutes
setInterval(() => {
    // You can implement auto-refresh logic here
}, 300000);
</script>
@endsection
