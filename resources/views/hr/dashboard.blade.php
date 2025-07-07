@extends('layouts.app')

@section('title', 'لوحة تحكم الموارد البشرية')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">لوحة تحكم الموارد البشرية</h1>
            <p class="text-gray-600 mt-1">نظرة شاملة على إدارة الموارد البشرية</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="showComingSoon('تقرير شامل')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-chart-bar ml-2"></i>
                تقرير شامل
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي الموظفين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_employees'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">الموظفين النشطين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_employees'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">الأقسام</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_departments'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-percentage text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">نسبة الحضور</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Employees Management -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-blue-100 text-blue-600 ml-3">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">إدارة الموظفين</h3>
            </div>
            <p class="text-gray-600 mb-4">إدارة بيانات الموظفين والمعلومات الشخصية</p>
            <div class="space-y-2">
                <a href="{{ route('hr.employees.index') }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-200">
                    عرض جميع الموظفين
                </a>
                <a href="{{ route('hr.employees.create') }}" class="block w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    إضافة موظف جديد
                </a>
            </div>
        </div>

        <!-- Departments Management -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-green-100 text-green-600 ml-3">
                    <i class="fas fa-building text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">إدارة الأقسام</h3>
            </div>
            <p class="text-gray-600 mb-4">إدارة أقسام الشركة والهيكل التنظيمي</p>
            <div class="space-y-2">
                <a href="{{ route('hr.departments.index') }}" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-200">
                    عرض جميع الأقسام
                </a>
                <a href="{{ route('hr.departments.create') }}" class="block w-full text-center bg-green-100 hover:bg-green-200 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    إضافة قسم جديد
                </a>
            </div>
        </div>

        <!-- Attendance Management -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-yellow-100 text-yellow-600 ml-3">
                    <i class="fas fa-clock text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">إدارة الحضور</h3>
            </div>
            <p class="text-gray-600 mb-4">متابعة حضور وغياب الموظفين</p>
            <div class="space-y-2">
                <a href="{{ route('hr.attendance.index') }}" class="block w-full text-center bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg transition duration-200">
                    عرض سجلات الحضور
                </a>
                <button onclick="showComingSoon('تسجيل حضور سريع')" class="block w-full text-center bg-yellow-100 hover:bg-yellow-200 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    تسجيل حضور سريع
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Today's Attendance Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">ملخص حضور اليوم</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-user-check text-green-600 ml-2"></i>
                        <span class="text-sm font-medium text-gray-700">حاضر</span>
                    </div>
                    <span class="text-lg font-bold text-green-600">{{ $todayStats['present'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-yellow-600 ml-2"></i>
                        <span class="text-sm font-medium text-gray-700">متأخر</span>
                    </div>
                    <span class="text-lg font-bold text-yellow-600">{{ $todayStats['late'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-user-times text-red-600 ml-2"></i>
                        <span class="text-sm font-medium text-gray-700">غائب</span>
                    </div>
                    <span class="text-lg font-bold text-red-600">{{ $todayStats['absent'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Employees -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">الموظفين الجدد</h3>
            <div class="space-y-3">
                @forelse($recentEmployees ?? [] as $employee)
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    @if($employee->profile_photo)
                        <img class="h-10 w-10 rounded-full object-cover ml-3" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->full_name_ar }}">
                    @else
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center ml-3">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $employee->full_name_ar }}</p>
                        <p class="text-xs text-gray-500">{{ $employee->department->name_ar ?? 'غير محدد' }}</p>
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $employee->created_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-user-plus text-3xl text-gray-300 mb-2"></i>
                    <p>لا توجد موظفين جدد</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Additional Modules -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Payroll Management -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-purple-100 text-purple-600 ml-3">
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">إدارة الرواتب</h3>
            </div>
            <p class="text-gray-600 mb-4">إدارة رواتب الموظفين والمكافآت</p>
            <div class="space-y-2">
                <a href="{{ route('hr.payroll.index') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg transition duration-200">
                    عرض كشوف الرواتب
                </a>
                <button onclick="showComingSoon('إنشاء كشف راتب')" class="block w-full text-center bg-purple-100 hover:bg-purple-200 text-purple-700 py-2 px-4 rounded-lg transition duration-200">
                    إنشاء كشف راتب جديد
                </button>
            </div>
        </div>

        <!-- Reports & Analytics -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600 ml-3">
                    <i class="fas fa-chart-bar text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">التقارير والتحليلات</h3>
            </div>
            <p class="text-gray-600 mb-4">تقارير شاملة عن الموارد البشرية</p>
            <div class="space-y-2">
                <button onclick="showComingSoon('تقرير الحضور')" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition duration-200">
                    تقرير الحضور
                </button>
                <button onclick="showComingSoon('تقرير الرواتب')" class="block w-full text-center bg-indigo-100 hover:bg-indigo-200 text-indigo-700 py-2 px-4 rounded-lg transition duration-200">
                    تقرير الرواتب
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
