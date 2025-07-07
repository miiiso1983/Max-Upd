@extends('layouts.app')

@section('title', 'إدارة الأقسام')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إدارة الأقسام</h1>
            <p class="text-gray-600 mt-1">إدارة أقسام الشركة والهيكل التنظيمي</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="exportDepartments()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-download ml-2"></i>
                تصدير Excel
            </button>
            <a href="{{ route('hr.departments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-plus ml-2"></i>
                إضافة قسم جديد
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي الأقسام</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">الأقسام النشطة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي الموظفين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->sum(function($dept) { return $dept->employees_count ?? 0; }) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-sitemap text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">المناصب المتاحة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->sum(function($dept) { return $dept->positions_count ?? 0; }) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="البحث بالاسم أو الكود" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select id="status_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="1">نشط</option>
                    <option value="0">غير نشط</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2 space-x-reverse">
                <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-search ml-2"></i>
                    تطبيق الفلاتر
                </button>
                <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-times ml-2"></i>
                    مسح الفلاتر
                </button>
            </div>
        </div>
    </div>

    <!-- Departments Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">القسم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكود</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدير</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عدد الموظفين</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عدد المناصب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="departments-table-body">
                    @foreach($departments as $department)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $department->name_ar }}</div>
                                <div class="text-sm text-gray-500">{{ $department->name }}</div>
                                @if($department->description_ar)
                                    <div class="text-xs text-gray-400 mt-1">{{ Str::limit($department->description_ar, 50) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex px-2 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded">
                                {{ $department->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($department->manager)
                                <div class="flex items-center">
                                    @if($department->manager->profile_photo)
                                        <img class="h-8 w-8 rounded-full object-cover ml-2" src="{{ asset('storage/' . $department->manager->profile_photo) }}" alt="{{ $department->manager->full_name_ar }}">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center ml-2">
                                            <i class="fas fa-user text-gray-600 text-xs"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $department->manager->full_name_ar }}</div>
                                        <div class="text-xs text-gray-500">{{ $department->manager->email }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400">غير محدد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $department->employees_count ?? 0 }} موظف
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $department->positions_count ?? 0 }} منصب
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($department->is_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle ml-1"></i>
                                    نشط
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle ml-1"></i>
                                    غير نشط
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('hr.departments.show', $department) }}" class="text-blue-600 hover:text-blue-900 transition duration-200" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('hr.departments.edit', $department) }}" class="text-yellow-600 hover:text-yellow-900 transition duration-200" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="toggleStatus({{ $department->id }})" class="text-purple-600 hover:text-purple-900 transition duration-200" title="تغيير الحالة">
                                    <i class="fas fa-toggle-{{ $department->is_active ? 'on' : 'off' }}"></i>
                                </button>
                                <button onclick="deleteDepartment({{ $department->id }})" class="text-red-600 hover:text-red-900 transition duration-200" title="حذف">
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
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $departments->links() }}
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('status_filter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status !== '') params.append('is_active', status);
    
    window.location.href = '{{ route("hr.departments.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("hr.departments.index") }}';
}

function deleteDepartment(id) {
    if (confirm('هل أنت متأكد من حذف هذا القسم؟\n\nتحذير: سيتم حذف جميع المناصب المرتبطة بهذا القسم.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/hr/departments/${id}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleStatus(id) {
    if (confirm('هل تريد تغيير حالة هذا القسم؟')) {
        fetch(`/hr/departments/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء تغيير الحالة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تغيير الحالة');
        });
    }
}

function exportDepartments() {
    window.location.href = '{{ route("hr.departments.index") }}?export=excel';
}

// Auto-apply filters on input change
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(applyFilters, 500);
});
</script>
@endsection
