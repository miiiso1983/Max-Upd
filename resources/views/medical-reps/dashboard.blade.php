@extends('layouts.app')

@section('page-title', 'المندوبين الطبيين')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center space-x-4 space-x-reverse">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-md text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold">مرحباً بك في إدارة المندوبين الطبيين</h1>
                <p class="text-blue-100 mt-1">نظام إدارة شامل للمندوبين الطبيين والزيارات والعمولات</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Reps -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المندوبين</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_reps'] }}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i>
                        {{ $stats['active_reps'] }} نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Territories -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">المناطق</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['territories'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">منطقة مغطاة</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-map-marked-alt text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Today's Visits -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">زيارات اليوم</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['visits_today'] }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-calendar-check"></i>
                        {{ $stats['monthly_visits'] }} هذا الشهر
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Commissions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 md:col-span-2 lg:col-span-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">العمولات المعلقة</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['commissions_pending']) }} د.ع</p>
                    <p class="text-sm text-orange-600 mt-1">
                        <i class="fas fa-clock"></i>
                        في انتظار الدفع
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Visits and Top Reps -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Visits -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">الزيارات الحديثة</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentVisits as $visit)
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-md text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $visit->rep_name }}</p>
                            <p class="text-sm text-gray-600">{{ $visit->customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $visit->visit_date->diffForHumans() }}</p>
                        </div>
                        <div class="text-left">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                مكتملة
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    <a href="{{ route('medical-reps.visits.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        عرض جميع الزيارات <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Reps -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">أفضل المندوبين</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($topReps as $rep)
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-star text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $rep->name }}</p>
                            <p class="text-sm text-gray-600">{{ $rep->territory }}</p>
                            <p class="text-xs text-gray-500">{{ $rep->visits_count }} زيارة</p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">{{ number_format($rep->sales_amount) }} د.ع</p>
                            <p class="text-xs text-green-600">{{ number_format($rep->commission) }} د.ع عمولة</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    <a href="{{ route('medical-reps.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        عرض جميع المندوبين <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('medical-reps.index') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-user-md text-blue-600 text-xl ml-3"></i>
                <span class="font-medium text-blue-900">إدارة المندوبين</span>
            </a>
            <a href="{{ route('medical-reps.territories.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-map-marked-alt text-green-600 text-xl ml-3"></i>
                <span class="font-medium text-green-900">إدارة المناطق</span>
            </a>
            <a href="{{ route('medical-reps.visits.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-calendar-check text-purple-600 text-xl ml-3"></i>
                <span class="font-medium text-purple-900">تتبع الزيارات</span>
            </a>
            <a href="{{ route('medical-reps.commissions.index') }}" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                <i class="fas fa-percentage text-orange-600 text-xl ml-3"></i>
                <span class="font-medium text-orange-900">إدارة العمولات</span>
            </a>
        </div>
    </div>
</div>
@endsection
