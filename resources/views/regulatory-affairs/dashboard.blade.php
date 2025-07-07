@extends('layouts.app')

@section('title', 'الشؤون التنظيمية الدوائية - MaxCon ERP')
@section('page-title', 'الشؤون التنظيمية الدوائية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">الشؤون التنظيمية الدوائية</h1>
                <p class="text-blue-100">إدارة شاملة للامتثال الدوائي والتراخيص والفحوصات</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الشركات المسجلة</p>
                    <p class="text-2xl font-bold text-blue-600" id="total-companies">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">المنتجات المسجلة</p>
                    <p class="text-2xl font-bold text-green-600" id="total-products">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vials text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الدفعات المطلقة</p>
                    <p class="text-2xl font-bold text-purple-600" id="released-batches">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flask text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الفحوصات المعلقة</p>
                    <p class="text-2xl font-bold text-orange-600" id="pending-tests">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Companies Management -->
        <a href="{{ route('regulatory-affairs.companies.index') }}" class="block bg-white rounded-lg p-6 card-shadow hover:shadow-lg transition-shadow hover-scale">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-left text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">إدارة الشركات الدوائية</h3>
            <p class="text-gray-600 text-sm">تسجيل ومتابعة الشركات المصنعة والموزعة للأدوية</p>
            <div class="mt-4 flex items-center text-sm text-blue-600">
                <span>عرض الشركات</span>
                <i class="fas fa-chevron-left mr-2"></i>
            </div>
        </a>

        <!-- Products Management -->
        <a href="{{ route('regulatory-affairs.products.index') }}" class="block bg-white rounded-lg p-6 card-shadow hover:shadow-lg transition-shadow hover-scale">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-green-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-left text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">إدارة الأصناف الدوائية</h3>
            <p class="text-gray-600 text-sm">تسجيل ومتابعة المنتجات الدوائية وتراخيصها</p>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <span>عرض المنتجات</span>
                <i class="fas fa-chevron-left mr-2"></i>
            </div>
        </a>

        <!-- Batches Management -->
        <a href="{{ route('regulatory-affairs.batches.index') }}" class="block bg-white rounded-lg p-6 card-shadow hover:shadow-lg transition-shadow hover-scale">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vials text-purple-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-left text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">إدارة الدفعات</h3>
            <p class="text-gray-600 text-sm">متابعة دفعات الإنتاج والفحوصات المطلوبة</p>
            <div class="mt-4 flex items-center text-sm text-purple-600">
                <span>عرض الدفعات</span>
                <i class="fas fa-chevron-left mr-2"></i>
            </div>
        </a>

        <!-- Tests Management -->
        <a href="{{ route('regulatory-affairs.tests.index') }}" class="block bg-white rounded-lg p-6 card-shadow hover:shadow-lg transition-shadow hover-scale">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flask text-orange-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-left text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">إدارة الفحوصات</h3>
            <p class="text-gray-600 text-sm">متابعة الفحوصات الدوائية ونتائجها</p>
            <div class="mt-4 flex items-center text-sm text-orange-600">
                <span>عرض الفحوصات</span>
                <i class="fas fa-chevron-left mr-2"></i>
            </div>
        </a>

        <!-- Inspections Management -->
        <a href="{{ route('regulatory-affairs.inspections.index') }}" class="block bg-white rounded-lg p-6 card-shadow hover:shadow-lg transition-shadow hover-scale">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search text-red-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-left text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">إدارة التفتيشات</h3>
            <p class="text-gray-600 text-sm">جدولة ومتابعة التفتيشات الرقابية</p>
            <div class="mt-4 flex items-center text-sm text-red-600">
                <span>عرض التفتيشات</span>
                <i class="fas fa-chevron-left mr-2"></i>
            </div>
        </a>

        <!-- Reports -->
        <a href="{{ route('regulatory-affairs.reports.index') }}" class="block bg-white rounded-lg p-6 card-shadow hover:shadow-lg transition-shadow hover-scale">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-indigo-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-left text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">التقارير والإحصائيات</h3>
            <p class="text-gray-600 text-sm">تقارير الامتثال والتراخيص والفحوصات</p>
            <div class="mt-4 flex items-center text-sm text-indigo-600">
                <span>عرض التقارير</span>
                <i class="fas fa-chevron-left mr-2"></i>
            </div>
        </a>
    </div>

    <!-- Alerts and Notifications -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expiry Alerts -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">تنبيهات انتهاء الصلاحية</h3>
                <a href="{{ route('regulatory-affairs.reports.expiry-alerts') }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium">عرض الكل</a>
            </div>
            <div class="space-y-3" id="expiry-alerts">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>جاري تحميل التنبيهات...</p>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">الأنشطة الأخيرة</h3>
                <span class="text-sm text-gray-500">آخر 24 ساعة</span>
            </div>
            <div class="space-y-3" id="recent-activities">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>جاري تحميل الأنشطة...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadExpiryAlerts();
    loadRecentActivities();
});

function loadDashboardStats() {
    fetch('{{ route("regulatory-affairs.api.dashboard-stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-companies').textContent = data.companies.total;
            document.getElementById('total-products').textContent = data.products.total;
            document.getElementById('released-batches').textContent = data.batches.released;
            document.getElementById('pending-tests').textContent = data.tests.pending;
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
        });
}

function loadExpiryAlerts() {
    // Simulate loading expiry alerts
    setTimeout(() => {
        const alertsContainer = document.getElementById('expiry-alerts');
        alertsContainer.innerHTML = `
            <div class="flex items-center p-3 bg-orange-50 rounded-lg">
                <i class="fas fa-exclamation-triangle text-orange-600 ml-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-orange-800">3 تراخيص شركات تنتهي خلال 30 يوم</p>
                    <p class="text-xs text-orange-600">يجب تجديد التراخيص قبل انتهائها</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-red-50 rounded-lg">
                <i class="fas fa-clock text-red-600 ml-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">5 دفعات تنتهي صلاحيتها قريباً</p>
                    <p class="text-xs text-red-600">مراجعة الدفعات المنتهية الصلاحية</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-yellow-50 rounded-lg">
                <i class="fas fa-certificate text-yellow-600 ml-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-800">2 شهادات GMP تحتاج تجديد</p>
                    <p class="text-xs text-yellow-600">متابعة تجديد شهادات الجودة</p>
                </div>
            </div>
        `;
    }, 1000);
}

function loadRecentActivities() {
    // Simulate loading recent activities
    setTimeout(() => {
        const activitiesContainer = document.getElementById('recent-activities');
        activitiesContainer.innerHTML = `
            <div class="flex items-center p-3 border-r-4 border-blue-500 bg-blue-50">
                <i class="fas fa-plus-circle text-blue-600 ml-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-800">تم تسجيل شركة دوائية جديدة</p>
                    <p class="text-xs text-blue-600">منذ ساعتين</p>
                </div>
            </div>
            <div class="flex items-center p-3 border-r-4 border-green-500 bg-green-50">
                <i class="fas fa-check-circle text-green-600 ml-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800">تم اعتماد نتائج فحص دفعة</p>
                    <p class="text-xs text-green-600">منذ 4 ساعات</p>
                </div>
            </div>
            <div class="flex items-center p-3 border-r-4 border-purple-500 bg-purple-50">
                <i class="fas fa-vial text-purple-600 ml-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-purple-800">تم إطلاق دفعة جديدة للسوق</p>
                    <p class="text-xs text-purple-600">منذ 6 ساعات</p>
                </div>
            </div>
        `;
    }, 1500);
}
</script>
@endpush
@endsection
