@extends('layouts.app')

@section('title', 'تفاصيل التفتيش - ' . $inspection->inspection_number)
@section('page-title', 'تفاصيل التفتيش')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $inspection->inspection_number }}</h1>
                <p class="text-indigo-100">{{ $inspection->inspection_scope }}</p>
                <div class="flex items-center mt-2 space-x-4 space-x-reverse">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($inspection->inspection_status === 'completed') bg-green-100 text-green-800
                        @elseif($inspection->inspection_status === 'in_progress') bg-yellow-100 text-yellow-800
                        @elseif($inspection->inspection_status === 'cancelled') bg-red-100 text-red-800
                        @else bg-blue-100 text-blue-800 @endif">
                        {{ $inspection->inspection_status_arabic }}
                    </span>
                    @if($inspection->inspection_result)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($inspection->inspection_result === 'satisfactory') bg-green-100 text-green-800
                            @elseif($inspection->inspection_result === 'minor_deficiencies') bg-yellow-100 text-yellow-800
                            @elseif($inspection->inspection_result === 'major_deficiencies') bg-orange-100 text-orange-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $inspection->inspection_result_arabic }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.inspections.edit', $inspection) }}" 
                   class="bg-white text-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(isset($alerts) && count($alerts) > 0)
    <div class="space-y-3">
        @foreach($alerts as $alert)
        <div class="p-4 rounded-lg border-r-4 
            @if($alert['type'] === 'warning') bg-orange-50 border-orange-500 text-orange-800
            @elseif($alert['type'] === 'danger') bg-red-50 border-red-500 text-red-800
            @else bg-blue-50 border-blue-500 text-blue-800 @endif">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle ml-3"></i>
                <span>{{ $alert['message'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Inspection Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات التفتيش</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم التفتيش</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $inspection->inspection_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">نوع التفتيش</label>
                        <p class="text-sm text-gray-900">{{ $inspection->inspection_type_arabic }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">نطاق التفتيش</label>
                        <p class="text-sm text-gray-900">{{ $inspection->inspection_scope }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الجهة المفتشة</label>
                        <p class="text-sm text-gray-900">{{ $inspection->regulatory_authority }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">قائد فريق التفتيش</label>
                        <p class="text-sm text-gray-900">{{ $inspection->inspection_team_lead }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ التفتيش</label>
                        <p class="text-sm text-gray-900">
                            {{ $inspection->inspection_date->format('Y-m-d') }}
                            @if($inspection->inspection_end_date)
                                إلى {{ $inspection->inspection_end_date->format('Y-m-d') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Inspected Entity -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">الكيان المفتش</h3>
                
                @if($inspection->inspected_entity_type === 'company' && $inspection->company)
                    <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-blue-600 text-xl"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-900">{{ $inspection->company->display_name }}</h4>
                            <p class="text-sm text-gray-600">{{ $inspection->company->display_trade_name }}</p>
                            <p class="text-xs text-gray-500">{{ $inspection->company->registration_number }}</p>
                        </div>
                    </div>
                @elseif($inspection->inspected_entity_type === 'product' && $inspection->product)
                    <div class="flex items-center p-4 bg-green-50 rounded-lg">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pills text-green-600 text-xl"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-900">{{ $inspection->product->display_trade_name }}</h4>
                            <p class="text-sm text-gray-600">{{ $inspection->product->display_generic_name }}</p>
                            <p class="text-xs text-gray-500">{{ $inspection->product->registration_number }}</p>
                        </div>
                    </div>
                @elseif($inspection->inspected_entity_type === 'batch' && $inspection->batch)
                    <div class="flex items-center p-4 bg-purple-50 rounded-lg">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-vials text-purple-600 text-xl"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-900">{{ $inspection->batch->batch_number }}</h4>
                            <p class="text-sm text-gray-600">{{ $inspection->batch->product->display_trade_name }}</p>
                            <p class="text-xs text-gray-500">{{ $inspection->batch->lot_number }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Findings and Results -->
            @if($inspection->inspection_status === 'completed')
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">النتائج والتوصيات</h3>
                
                @if($inspection->findings_summary)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-2">ملخص النتائج</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $inspection->findings_summary }}</p>
                </div>
                @endif

                @if($inspection->recommendations)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-2">التوصيات</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $inspection->recommendations }}</p>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">إجراءات تصحيحية مطلوبة</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $inspection->corrective_actions_required ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $inspection->corrective_actions_required ? 'نعم' : 'لا' }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">متابعة مطلوبة</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $inspection->follow_up_required ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ $inspection->follow_up_required ? 'نعم' : 'لا' }}
                        </span>
                    </div>
                </div>

                @if($inspection->corrective_action_deadline)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">موعد الإجراءات التصحيحية</label>
                    <p class="text-sm text-gray-900">{{ $inspection->corrective_action_deadline->format('Y-m-d') }}</p>
                </div>
                @endif

                @if($inspection->follow_up_date)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">موعد المتابعة</label>
                    <p class="text-sm text-gray-900">{{ $inspection->follow_up_date->format('Y-m-d') }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
                
                <div class="space-y-3">
                    @if($inspection->inspection_status !== 'completed')
                    <button onclick="updateStatus()" 
                            class="block w-full bg-indigo-600 text-white text-center py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-cog ml-2"></i>
                        تحديث الحالة
                    </button>
                    @endif
                    
                    @if($inspection->inspection_status === 'completed')
                    <button onclick="generateReport()" 
                            class="block w-full bg-orange-600 text-white text-center py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-file-pdf ml-2"></i>
                        إنشاء تقرير
                    </button>
                    @endif
                    
                    <a href="{{ route('regulatory-affairs.inspections.edit', $inspection) }}" 
                       class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-edit ml-2"></i>
                        تعديل التفتيش
                    </a>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">الجدول الزمني</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">تاريخ التفتيش</p>
                            <p class="text-xs text-gray-500">{{ $inspection->inspection_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    
                    @if($inspection->inspection_end_date)
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">انتهاء التفتيش</p>
                            <p class="text-xs text-gray-500">{{ $inspection->inspection_end_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($inspection->report_issued_date)
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">إصدار التقرير</p>
                            <p class="text-xs text-gray-500">{{ $inspection->report_issued_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Inspections -->
            @if(isset($relatedInspections) && $relatedInspections->count() > 0)
            <div class="bg-white rounded-lg p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">تفتيشات ذات صلة</h3>
                    <a href="{{ route('regulatory-affairs.inspections.index') }}" 
                       class="text-indigo-600 hover:text-indigo-700 text-sm">عرض الكل</a>
                </div>
                
                <div class="space-y-3">
                    @foreach($relatedInspections as $related)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-search text-indigo-600 text-xs"></i>
                        </div>
                        <div class="mr-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $related->inspection_number }}</p>
                            <p class="text-xs text-gray-500">{{ $related->inspection_date->format('Y-m-d') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($related->inspection_result === 'satisfactory') bg-green-100 text-green-800
                            @elseif($related->inspection_result === 'minor_deficiencies') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $related->inspection_result_arabic ?? 'في الانتظار' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Documents Section -->
    @include('regulatory-affairs.components.document-upload', [
        'entityType' => 'inspection',
        'entityId' => $inspection->id,
        'entityName' => $inspection->inspection_number
    ])
</div>

@push('scripts')
<script>
function updateStatus() {
    // This would open a modal to update inspection status
    alert('سيتم فتح نافذة تحديث حالة التفتيش');
}

function generateReport() {
    // This would generate a PDF report for the inspection
    alert('سيتم إنشاء تقرير PDF للتفتيش');
}
</script>
@endpush
@endsection
