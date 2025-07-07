@extends('layouts.app')

@section('title', 'التفتيشات الدوائية - MaxCon ERP')
@section('page-title', 'التفتيشات الدوائية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">التفتيشات الدوائية</h1>
                <p class="text-indigo-100">إدارة ومتابعة التفتيشات التنظيمية والداخلية</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.inspections.create') }}" 
                   class="bg-white text-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    تفتيش جديد
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search text-indigo-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي التفتيشات</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تفتيشات مرضية</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['satisfactory'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">نواقص بسيطة</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['minor_deficiencies'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">نواقص كبيرة</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['major_deficiencies'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">مجدولة</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['scheduled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('regulatory-affairs.inspections.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في التفتيشات..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التفتيش</label>
                <select name="inspection_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">جميع الأنواع</option>
                    <option value="regulatory" {{ request('inspection_type') === 'regulatory' ? 'selected' : '' }}>تنظيمي</option>
                    <option value="internal" {{ request('inspection_type') === 'internal' ? 'selected' : '' }}>داخلي</option>
                    <option value="customer" {{ request('inspection_type') === 'customer' ? 'selected' : '' }}>عميل</option>
                    <option value="supplier" {{ request('inspection_type') === 'supplier' ? 'selected' : '' }}>مورد</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة التفتيش</label>
                <select name="inspection_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">جميع الحالات</option>
                    <option value="scheduled" {{ request('inspection_status') === 'scheduled' ? 'selected' : '' }}>مجدول</option>
                    <option value="in_progress" {{ request('inspection_status') === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                    <option value="completed" {{ request('inspection_status') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                    <option value="cancelled" {{ request('inspection_status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نتيجة التفتيش</label>
                <select name="inspection_result" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">جميع النتائج</option>
                    <option value="satisfactory" {{ request('inspection_result') === 'satisfactory' ? 'selected' : '' }}>مرضي</option>
                    <option value="minor_deficiencies" {{ request('inspection_result') === 'minor_deficiencies' ? 'selected' : '' }}>نواقص بسيطة</option>
                    <option value="major_deficiencies" {{ request('inspection_result') === 'major_deficiencies' ? 'selected' : '' }}>نواقص كبيرة</option>
                    <option value="critical_deficiencies" {{ request('inspection_result') === 'critical_deficiencies' ? 'selected' : '' }}>نواقص حرجة</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الجهة المفتشة</label>
                <select name="inspecting_authority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">جميع الجهات</option>
                    @foreach($inspectingAuthorities as $authority)
                        <option value="{{ $authority }}" {{ request('inspecting_authority') === $authority ? 'selected' : '' }}>
                            {{ $authority }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Inspections Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة التفتيشات الدوائية</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم التفتيش</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الجهة المفتشة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع التفتيش</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ التفتيش</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النتيجة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inspections as $inspection)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-search text-indigo-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900 font-mono">
                                        {{ $inspection->inspection_number }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $inspection->inspection_scope }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $inspection->inspecting_authority }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $inspection->lead_inspector }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inspection->inspection_type_arabic }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inspection->inspection_date->format('Y-m-d') }}
                            @if($inspection->inspection_end_date)
                                <div class="text-xs text-gray-500">
                                    إلى {{ $inspection->inspection_end_date->format('Y-m-d') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($inspection->inspection_end_date)
                                {{ $inspection->inspection_date->diffInDays($inspection->inspection_end_date) + 1 }} يوم
                            @else
                                <span class="text-gray-400">غير محدد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($inspection->inspection_status === 'completed') bg-green-100 text-green-800
                                @elseif($inspection->inspection_status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($inspection->inspection_status === 'cancelled') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $inspection->inspection_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($inspection->inspection_result)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($inspection->inspection_result === 'satisfactory') bg-green-100 text-green-800
                                    @elseif($inspection->inspection_result === 'minor_deficiencies') bg-yellow-100 text-yellow-800
                                    @elseif($inspection->inspection_result === 'major_deficiencies') bg-orange-100 text-orange-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $inspection->inspection_result_arabic }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">في الانتظار</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('regulatory-affairs.inspections.show', $inspection) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('regulatory-affairs.inspections.edit', $inspection) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-inspection-id="{{ $inspection->id }}"
                                        onclick="updateStatus(this.dataset.inspectionId)"
                                        class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-cog"></i>
                                </button>
                                @if($inspection->inspection_status === 'completed')
                                    <button data-inspection-id="{{ $inspection->id }}"
                                            onclick="generateReport(this.dataset.inspectionId)"
                                            class="text-orange-600 hover:text-orange-900">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-search text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد تفتيشات دوائية</p>
                                <p class="text-sm">ابدأ بإضافة تفتيش دوائي جديد</p>
                                <a href="{{ route('regulatory-affairs.inspections.create') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة تفتيش جديد
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inspections->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $inspections->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateStatus(inspectionId) {
    // This would open a modal to update inspection status
    alert('سيتم فتح نافذة تحديث حالة التفتيش');
}

function generateReport(inspectionId) {
    // This would generate a PDF report for the inspection
    alert('سيتم إنشاء تقرير PDF للتفتيش');
}
</script>
@endpush
@endsection
