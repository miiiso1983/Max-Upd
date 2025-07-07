@extends('layouts.master-admin')

@section('title', 'إدارة المستأجرين')
@section('page-title', 'إدارة المستأجرين')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إدارة المستأجرين</h1>
            <p class="text-gray-600 mt-1">إدارة شاملة لجميع المستأجرين في النظام</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('master-admin.tenants.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                إضافة مستأجر جديد
            </a>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download ml-2"></i>
                تصدير البيانات
            </button>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" placeholder="اسم المستأجر، النطاق، البريد..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                    <option value="expired">منتهي الصلاحية</option>
                    <option value="expiring">ينتهي قريباً</option>
                </select>
            </div>
            
            <!-- Company Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع الشركة</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الأنواع</option>
                    <option value="pharmacy">صيدلية</option>
                    <option value="clinic">عيادة</option>
                    <option value="hospital">مستشفى</option>
                    <option value="laboratory">مختبر</option>
                    <option value="medical_center">مركز طبي</option>
                    <option value="other">أخرى</option>
                </select>
            </div>
            
            <!-- Actions -->
            <div class="flex items-end">
                <button class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستأجر</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النطاق</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الشركة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدمين</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">انتهاء الترخيص</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tenants as $tenant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center ml-3">
                                        <i class="fas fa-building text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $tenant->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $tenant->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $tenant->domain }}</div>
                                <div class="text-sm text-gray-500">{{ $tenant->city }}, {{ $tenant->governorate }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($tenant->company_type === 'pharmacy') bg-green-100 text-green-800
                                    @elseif($tenant->company_type === 'hospital') bg-red-100 text-red-800
                                    @elseif($tenant->company_type === 'clinic') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $tenant->company_type === 'pharmacy' ? 'صيدلية' : 
                                       ($tenant->company_type === 'hospital' ? 'مستشفى' : 
                                       ($tenant->company_type === 'clinic' ? 'عيادة' : 'أخرى')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $tenant->total_users }}/{{ $tenant->max_users }}</div>
                                <div class="text-sm text-gray-500">{{ $tenant->active_users }} نشط</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $tenant->license_expires_at->format('Y-m-d') }}</div>
                                @if($tenant->days_until_expiry !== null)
                                    @if($tenant->days_until_expiry < 0)
                                        <div class="text-sm text-red-600">منتهي منذ {{ abs($tenant->days_until_expiry) }} يوم</div>
                                    @elseif($tenant->days_until_expiry <= 30)
                                        <div class="text-sm text-orange-600">ينتهي خلال {{ $tenant->days_until_expiry }} يوم</div>
                                    @else
                                        <div class="text-sm text-green-600">{{ $tenant->days_until_expiry }} يوم متبقي</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tenant->is_active && $tenant->license_status === 'valid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        نشط
                                    </span>
                                @elseif($tenant->license_status === 'expired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        منتهي
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        غير نشط
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2 space-x-reverse">
                                    <a href="{{ route('master-admin.tenants.show', $tenant) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('master-admin.tenants.edit', $tenant) }}" 
                                       class="text-green-600 hover:text-green-900" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($tenant->license_status === 'expired' || $tenant->days_until_expiry <= 30)
                                    <button class="text-purple-600 hover:text-purple-900" title="تمديد الترخيص">
                                        <i class="fas fa-calendar-plus"></i>
                                    </button>
                                    @endif
                                    <button class="text-orange-600 hover:text-orange-900" title="تغيير الحالة">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-900" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المستأجرين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">نشط</p>
                    <p class="text-2xl font-bold text-green-600">{{ $tenants->where('is_active', true)->where('license_status', 'valid')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">منتهي الصلاحية</p>
                    <p class="text-2xl font-bold text-red-600">{{ $tenants->where('license_status', 'expired')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-clock text-orange-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">ينتهي قريباً</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $tenants->where('days_until_expiry', '<=', 30)->where('days_until_expiry', '>', 0)->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
