@extends('layouts.app')

@section('title', 'إدارة المستأجرين - MaxCon ERP')
@section('page-title', 'إدارة المستأجرين')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إدارة المستأجرين</h1>
            <p class="text-gray-600">إدارة الشركات والمؤسسات المشتركة في النظام</p>
        </div>
        <a href="{{ route('super-admin.tenants.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus ml-2"></i>
            مستأجر جديد
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المستأجرين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">مستأجرين نشطين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">مستأجرين جدد هذا الشهر</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus-circle text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">اشتراكات منتهية</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->where('subscription_status', 'expired')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('super-admin.tenants.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البحث</label>
                <input type="text" name="search" value="{{ $request->get('search') }}" 
                       placeholder="اسم الشركة، البريد الإلكتروني..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ $request->get('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ $request->get('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <!-- Company Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع الشركة</label>
                <select name="company_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الأنواع</option>
                    @foreach($filters['company_types'] as $type)
                        <option value="{{ $type }}" {{ $request->get('company_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search ml-1"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة المستأجرين</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المحافظة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاشتراك</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدمين</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tenants as $tenant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                                    <i class="fas fa-building text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $tenant->company_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $tenant->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $tenant->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tenant->company_type === 'pharmacy' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $tenant->company_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $tenant->governorate }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tenant->subscription_status === 'active' ? 'bg-green-100 text-green-800' : ($tenant->subscription_status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($tenant->subscription_status) }}
                            </span>
                            @if($tenant->subscription_end_date)
                                <div class="text-xs text-gray-500 mt-1">
                                    ينتهي: {{ $tenant->subscription_end_date->format('Y-m-d') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $tenant->statistics['total_users'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($tenant->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">نشط</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">غير نشط</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($tenant->is_active)
                                    <button onclick="toggleTenantStatus({{ $tenant->id }}, false)" class="text-orange-600 hover:text-orange-900">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                @else
                                    <button onclick="toggleTenantStatus({{ $tenant->id }}, true)" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-play"></i>
                                    </button>
                                @endif
                                <button onclick="deleteTenant({{ $tenant->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-building text-4xl mb-4"></i>
                                <p class="text-lg">لا توجد مستأجرين</p>
                                <p class="text-sm">ابدأ بإضافة مستأجر جديد</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($tenants->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $tenants->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2" id="modalTitle">تأكيد الإجراء</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">هل أنت متأكد من هذا الإجراء؟</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmButton" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    تأكيد
                </button>
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleTenantStatus(tenantId, status) {
    const action = status ? 'تفعيل' : 'إيقاف';
    document.getElementById('modalTitle').textContent = `${action} المستأجر`;
    document.getElementById('modalMessage').textContent = `هل أنت متأكد من ${action} هذا المستأجر؟`;
    
    document.getElementById('confirmButton').onclick = function() {
        // Make API call to toggle status
        fetch(`/api/super-admin/tenants/${tenantId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_active: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء تحديث الحالة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تحديث الحالة');
        });
        
        closeModal();
    };
    
    document.getElementById('confirmModal').classList.remove('hidden');
}

function deleteTenant(tenantId) {
    document.getElementById('modalTitle').textContent = 'حذف المستأجر';
    document.getElementById('modalMessage').textContent = 'هل أنت متأكد من حذف هذا المستأجر؟ هذا الإجراء لا يمكن التراجع عنه.';
    
    document.getElementById('confirmButton').onclick = function() {
        // Make API call to delete tenant
        fetch(`/api/super-admin/tenants/${tenantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء حذف المستأجر');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حذف المستأجر');
        });
        
        closeModal();
    };
    
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}
</script>
@endpush
