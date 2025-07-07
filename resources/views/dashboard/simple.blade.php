@extends('layouts.app')

@section('title', 'لوحة التحكم - MaxCon ERP')
@section('page-title', 'لوحة التحكم')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-8 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">مرحباً بك في MaxCon ERP</h1>
                    <p class="text-blue-100 text-lg">نظام إدارة الموارد المؤسسية المتكامل</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-chart-line text-6xl text-blue-200"></i>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Sales Card -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-semibold text-gray-900">المبيعات</h3>
                        <p class="text-gray-600">إدارة المبيعات والعملاء</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('sales.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        عرض التفاصيل <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>

            <!-- Inventory Card -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-boxes text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-semibold text-gray-900">المخزون</h3>
                        <p class="text-gray-600">إدارة المنتجات والمخزون</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('inventory.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        عرض التفاصيل <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>

            <!-- Reports Card -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-chart-bar text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-semibold text-gray-900">التقارير</h3>
                        <p class="text-gray-600">تقارير وإحصائيات</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        عرض التفاصيل <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>

            <!-- Users Card -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-semibold text-gray-900">المستخدمين</h3>
                        <p class="text-gray-600">إدارة المستخدمين</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        عرض التفاصيل <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">الإجراءات السريعة</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('sales.customers.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-user-plus text-blue-600 text-xl ml-3"></i>
                    <span class="text-blue-800 font-medium">إضافة عميل جديد</span>
                </a>
                
                <a href="{{ route('inventory.products.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-plus-circle text-green-600 text-xl ml-3"></i>
                    <span class="text-green-800 font-medium">إضافة منتج جديد</span>
                </a>
                
                <a href="{{ route('sales.invoices.create') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <i class="fas fa-file-invoice text-purple-600 text-xl ml-3"></i>
                    <span class="text-purple-800 font-medium">إنشاء فاتورة</span>
                </a>
                
                <a href="{{ route('admin.users.create') }}" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                    <i class="fas fa-user-cog text-orange-600 text-xl ml-3"></i>
                    <span class="text-orange-800 font-medium">إضافة مستخدم</span>
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">حالة النظام</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">النظام يعمل</h3>
                    <p class="text-gray-600">جميع الخدمات متاحة</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
                        <i class="fas fa-database text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">قاعدة البيانات</h3>
                    <p class="text-gray-600">متصلة وتعمل بشكل طبيعي</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-3">
                        <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">الأمان</h3>
                    <p class="text-gray-600">النظام محمي ومؤمن</p>
                </div>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
/* Dashboard Hover Effects */
.bg-white:hover h1,
.bg-white:hover h2,
.bg-white:hover h3,
.bg-white:hover p,
.bg-white:hover span {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.bg-white:hover .text-gray-600,
.bg-white:hover .text-gray-900 {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.bg-white:hover i {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

/* Welcome section hover */
.bg-gradient-to-r:hover h1 {
    color: #6f42c1 !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.bg-gradient-to-r:hover p {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

/* Icon background hover */
.bg-green-100:hover,
.bg-blue-100:hover,
.bg-purple-100:hover,
.bg-orange-100:hover {
    background: #6f42c1 !important;
    transition: background 0.3s ease;
}

.bg-green-100:hover i,
.bg-blue-100:hover i,
.bg-purple-100:hover i,
.bg-orange-100:hover i {
    color: white !important;
    transition: color 0.3s ease;
}
</style>
@endpush
@endsection
