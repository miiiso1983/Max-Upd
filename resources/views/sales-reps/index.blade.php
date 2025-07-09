@extends('layouts.app')

@section('title', 'إدارة مندوبي المبيعات')

@section('content')
<div class="container-responsive">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">إدارة مندوبي المبيعات</h1>
            <p class="text-gray-600">إدارة وتتبع أداء مندوبي المبيعات والمناطق المخصصة لهم</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 mt-4 md:mt-0">
            <a href="{{ route('sales-reps.import') }}" class="btn-responsive bg-green-600 hover:bg-green-700 text-white">
                <i class="fas fa-file-excel mr-2"></i>
                استيراد من Excel
            </a>
            <a href="{{ route('sales-reps.create') }}" class="btn-responsive btn-primary-responsive">
                <i class="fas fa-plus mr-2"></i>
                إضافة مندوب جديد
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card bg-blue-50 border-l-4 border-blue-500">
            <div class="stat-number text-blue-600">{{ $salesReps->total() }}</div>
            <div class="stat-label">إجمالي المندوبين</div>
        </div>
        <div class="stat-card bg-green-50 border-l-4 border-green-500">
            <div class="stat-number text-green-600">
                {{ $salesReps->where('status', 'active')->count() }}
            </div>
            <div class="stat-label">المندوبين النشطين</div>
        </div>
        <div class="stat-card bg-yellow-50 border-l-4 border-yellow-500">
            <div class="stat-number text-yellow-600">
                {{ $salesReps->where('status', 'inactive')->count() }}
            </div>
            <div class="stat-label">المندوبين غير النشطين</div>
        </div>
        <div class="stat-card bg-purple-50 border-l-4 border-purple-500">
            <div class="stat-number text-purple-600">
                {{ $salesReps->whereNotNull('supervisor_id')->count() }}
            </div>
            <div class="stat-label">تحت الإشراف</div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card-responsive mb-6">
        <form method="GET" action="{{ route('sales-reps.index') }}" class="form-responsive">
            <div class="form-group-responsive">
                <label for="search" class="block text-sm font-medium text-gray-700">البحث</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="البحث بالاسم، البريد الإلكتروني، أو رقم الهاتف..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>

            <div class="form-group-responsive">
                <label for="status" class="block text-sm font-medium text-gray-700">الحالة</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>معلق</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>منتهي الخدمة</option>
                </select>
            </div>

            <div class="form-group-responsive">
                <label for="supervisor_id" class="block text-sm font-medium text-gray-700">المشرف</label>
                <select id="supervisor_id" name="supervisor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">جميع المشرفين</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" {{ request('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                            {{ $supervisor->name_ar ?: $supervisor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group-responsive">
                <label for="governorate" class="block text-sm font-medium text-gray-700">المحافظة</label>
                <select id="governorate" name="governorate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">جميع المحافظات</option>
                    @foreach($governorates as $governorate)
                        <option value="{{ $governorate }}" {{ request('governorate') == $governorate ? 'selected' : '' }}>
                            {{ $governorate }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group-responsive flex items-end">
                <div class="flex gap-2 w-full">
                    <button type="submit" class="btn-responsive btn-primary-responsive flex-1">
                        <i class="fas fa-search mr-2"></i>
                        بحث
                    </button>
                    <a href="{{ route('sales-reps.index') }}" class="btn-responsive bg-gray-500 hover:bg-gray-600 text-white flex-1 text-center">
                        <i class="fas fa-times mr-2"></i>
                        إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="card-responsive mb-6" id="bulk-actions" style="display: none;">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center">
                <span class="text-sm text-gray-600 mr-3">تم تحديد <span id="selected-count">0</span> مندوب</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="bulkAction('activate')" class="btn-responsive bg-green-600 hover:bg-green-700 text-white text-sm">
                    <i class="fas fa-check mr-1"></i>
                    تفعيل
                </button>
                <button type="button" onclick="bulkAction('deactivate')" class="btn-responsive bg-yellow-600 hover:bg-yellow-700 text-white text-sm">
                    <i class="fas fa-pause mr-1"></i>
                    إلغاء تفعيل
                </button>
                <button type="button" onclick="bulkAction('delete')" class="btn-responsive bg-red-600 hover:bg-red-700 text-white text-sm">
                    <i class="fas fa-trash mr-1"></i>
                    حذف
                </button>
            </div>
        </div>
    </div>

    <!-- Sales Representatives Table -->
    <div class="card-responsive">
        <div class="table-responsive">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المندوب
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            معلومات الاتصال
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المنطقة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المشرف
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الحالة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الأداء
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الإجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($salesReps as $salesRep)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_reps[]" value="{{ $salesRep->id }}" 
                                       class="rep-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-purple-600">
                                                {{ substr($salesRep->name, 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mr-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $salesRep->name_ar ?: $salesRep->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $salesRep->employee_code }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $salesRep->email }}</div>
                                <div class="text-sm text-gray-500">{{ $salesRep->phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $salesRep->governorate }}</div>
                                <div class="text-sm text-gray-500">{{ $salesRep->city }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($salesRep->supervisor)
                                    <div class="text-sm text-gray-900">
                                        {{ $salesRep->supervisor->name_ar ?: $salesRep->supervisor->name }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">لا يوجد مشرف</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-gray-100 text-gray-800',
                                        'suspended' => 'bg-yellow-100 text-yellow-800',
                                        'terminated' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusLabels = [
                                        'active' => 'نشط',
                                        'inactive' => 'غير نشط',
                                        'suspended' => 'معلق',
                                        'terminated' => 'منتهي الخدمة'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$salesRep->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$salesRep->status] ?? $salesRep->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ number_format($salesRep->monthly_target) }} د.ع
                                </div>
                                <div class="text-sm text-gray-500">
                                    الهدف الشهري
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <a href="{{ route('sales-reps.show', $salesRep) }}" 
                                       class="text-purple-600 hover:text-purple-900" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sales-reps.edit', $salesRep) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('sales-reps.performance', $salesRep) }}" 
                                       class="text-green-600 hover:text-green-900" title="الأداء">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                    <a href="{{ route('sales-reps.location', $salesRep) }}" 
                                       class="text-yellow-600 hover:text-yellow-900" title="الموقع">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </a>
                                    <button onclick="deleteSalesRep({{ $salesRep->id }})" 
                                            class="text-red-600 hover:text-red-900" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                لا توجد بيانات مندوبي مبيعات
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($salesReps->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $salesReps->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Bulk selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const repCheckboxes = document.querySelectorAll('.rep-checkbox');
    const bulkActionsDiv = document.getElementById('bulk-actions');
    const selectedCountSpan = document.getElementById('selected-count');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        repCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    repCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.rep-checkbox:checked');
        const count = selectedCheckboxes.length;
        
        selectedCountSpan.textContent = count;
        bulkActionsDiv.style.display = count > 0 ? 'block' : 'none';
        
        // Update select all checkbox state
        selectAllCheckbox.checked = count === repCheckboxes.length;
        selectAllCheckbox.indeterminate = count > 0 && count < repCheckboxes.length;
    }
});

// Bulk actions
function bulkAction(action) {
    const selectedCheckboxes = document.querySelectorAll('.rep-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('يرجى تحديد مندوب واحد على الأقل');
        return;
    }
    
    let confirmMessage = '';
    switch(action) {
        case 'activate':
            confirmMessage = `هل أنت متأكد من تفعيل ${selectedIds.length} مندوب؟`;
            break;
        case 'deactivate':
            confirmMessage = `هل أنت متأكد من إلغاء تفعيل ${selectedIds.length} مندوب؟`;
            break;
        case 'delete':
            confirmMessage = `هل أنت متأكد من حذف ${selectedIds.length} مندوب؟ هذا الإجراء لا يمكن التراجع عنه.`;
            break;
    }
    
    if (confirm(confirmMessage)) {
        // Send AJAX request
        fetch('{{ route("sales-reps.bulk-action") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                action: action,
                sales_rep_ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('حدث خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تنفيذ العملية');
        });
    }
}

// Delete single sales rep
function deleteSalesRep(id) {
    if (confirm('هل أنت متأكد من حذف هذا المندوب؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/sales-reps/${id}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
