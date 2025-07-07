@extends('layouts.admin')

@section('title', 'إدارة الصلاحيات')
@section('page-title', 'إدارة الصلاحيات')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-key text-blue-600 ml-3"></i>
                    إدارة الصلاحيات
                </h1>
                <p class="text-gray-600 mt-1">إدارة وتنظيم صلاحيات النظام وربطها بالأدوار</p>
            </div>
            @can('create permissions')
            <button onclick="openCreateModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus ml-2"></i>
                إضافة صلاحية جديدة
            </button>
            @endcan
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">البحث في الصلاحيات</label>
                    <div class="relative">
                        <input type="text" id="search"
                               placeholder="ابحث بالاسم أو المجموعة..."
                               value="{{ request('search') }}"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Group Filter -->
                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700 mb-2">تصفية حسب المجموعة</label>
                    <select id="group" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">جميع المجموعات</option>
                        @foreach($groups as $group)
                            <option value="{{ $group }}" {{ request('group') === $group ? 'selected' : '' }}>
                                {{ ucfirst($group) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="button" onclick="applyFilters()"
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-filter ml-2"></i>
                        تطبيق الفلتر
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة الصلاحيات</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الصلاحية
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المجموعة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الأدوار المرتبطة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            العمليات
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permissions as $permission)
                    <tr class="hover:bg-gray-50" id="permission-row-{{ $permission->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $permission->display_name ?? $permission->name }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $permission->name }}</div>
                                @if($permission->description)
                                    <div class="text-sm text-gray-600 mt-1">{{ $permission->description }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($permission->group) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @foreach($permission->roles as $role)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $role->display_name ?? $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                @can('edit permissions')
                                <button data-permission-id="{{ $permission->id }}"
                                        onclick="editPermission(this.dataset.permissionId)"
                                        class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-all duration-200"
                                        title="تعديل">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                @endcan

                                @can('edit permissions')
                                <button data-permission-id="{{ $permission->id }}"
                                        onclick="manageRoles(this.dataset.permissionId)"
                                        class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                        title="إدارة الأدوار">
                                    <i class="fas fa-user-tag text-sm"></i>
                                </button>
                                @endcan

                                @can('delete permissions')
                                <button data-permission-id="{{ $permission->id }}"
                                        onclick="deletePermission(this.dataset.permissionId)"
                                        class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200"
                                        title="حذف">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            لا توجد صلاحيات
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($permissions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    عرض {{ $permissions->firstItem() }} إلى {{ $permissions->lastItem() }} من أصل {{ $permissions->total() }} صلاحية
                </div>
                <div>
                    {{ $permissions->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

    <!-- Role-Permission Matrix -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">مصفوفة الأدوار والصلاحيات</h3>
            <p class="text-sm text-gray-600 mt-1">انقر على الخانات لتفعيل أو إلغاء تفعيل الصلاحيات للأدوار</p>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-right py-2 px-3 font-medium text-gray-900">الصلاحية</th>
                        @foreach($roles as $role)
                            <th class="text-center py-2 px-3 font-medium text-gray-900">
                                {{ $role->display_name ?? $role->name }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($permissions->groupBy('group') as $group => $groupPermissions)
                        <tr class="bg-gray-50">
                            <td colspan="{{ count($roles) + 1 }}" class="py-2 px-3 font-medium text-gray-700">
                                {{ ucfirst($group) }}
                            </td>
                        </tr>
                        @foreach($groupPermissions as $permission)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 text-gray-900">
                                {{ $permission->display_name ?? $permission->name }}
                            </td>
                            @foreach($roles as $role)
                                <td class="py-2 px-3 text-center">
                                    <input type="checkbox" 
                                           {{ $role->hasPermissionTo($permission) ? 'checked' : '' }}
                                           onchange="togglePermission({{ $permission->id }}, {{ $role->id }})"
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div id="createPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">إضافة صلاحية جديدة</h3>
            </div>
            <form id="createPermissionForm" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسم الصلاحية (بالإنجليزية) *</label>
                        <input type="text" name="name" required 
                               placeholder="view users, create posts"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الاسم المعروض *</label>
                        <input type="text" name="display_name" required 
                               placeholder="عرض المستخدمين، إنشاء المنشورات"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المجموعة *</label>
                        <select name="group" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">اختر المجموعة</option>
                            @foreach($groups as $group)
                                <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                            @endforeach
                            <option value="new">مجموعة جديدة</option>
                        </select>
                        <input type="text" name="new_group" placeholder="اسم المجموعة الجديدة" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-2 hidden">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                        <textarea name="description" rows="3" 
                                  placeholder="وصف مختصر للصلاحية..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                    </div>
                </div>
                <div class="flex space-x-3 space-x-reverse mt-6">
                    <button type="button" onclick="closeCreateModal()"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        إلغاء
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save ml-2"></i>
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div id="editPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">تعديل الصلاحية</h3>
            </div>
            <form id="editPermissionForm" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="editPermissionId" name="permission_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسم الصلاحية (بالإنجليزية) *</label>
                        <input type="text" id="editPermissionName" name="name" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الاسم المعروض *</label>
                        <input type="text" id="editPermissionDisplayName" name="display_name" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المجموعة *</label>
                        <select id="editPermissionGroup" name="group" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @foreach($groups as $group)
                                <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                        <textarea id="editPermissionDescription" name="description" rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                    </div>
                </div>
                <div class="flex space-x-3 space-x-reverse mt-6">
                    <button type="button" onclick="closeEditModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        تحديث
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('scripts')
<script>
// Filter permissions
function filterPermissions() {
    const search = document.getElementById('search').value;
    const group = document.getElementById('group').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (group) params.append('group', group);

    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

// Toggle permission for role
function togglePermission(permissionId, roleId) {
    fetch('/admin/permissions/assign-to-role', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            permission_id: permissionId,
            role_id: roleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message);
            // Revert checkbox state
            event.target.checked = !event.target.checked;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الصلاحية');
        // Revert checkbox state
        event.target.checked = !event.target.checked;
    });
}

// Open create modal
function openCreateModal() {
    document.getElementById('createPermissionModal').classList.remove('hidden');
}

// Close create modal
function closeCreateModal() {
    document.getElementById('createPermissionModal').classList.add('hidden');
    document.getElementById('createPermissionForm').reset();
    document.querySelector('input[name="new_group"]').classList.add('hidden');
}

// Open edit modal
function editPermission(permissionId) {
    fetch(`/admin/permissions/${permissionId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const permission = data.permission;
                document.getElementById('editPermissionId').value = permission.id;
                document.getElementById('editPermissionName').value = permission.name;
                document.getElementById('editPermissionDisplayName').value = permission.display_name || '';
                document.getElementById('editPermissionGroup').value = permission.group;
                document.getElementById('editPermissionDescription').value = permission.description || '';

                document.getElementById('editPermissionModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل بيانات الصلاحية');
        });
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editPermissionModal').classList.add('hidden');
    document.getElementById('editPermissionForm').reset();
}

// Delete permission
function deletePermission(permissionId) {
    if (confirm('هل أنت متأكد من حذف هذه الصلاحية؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        fetch(`/admin/permissions/${permissionId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`permission-row-${permissionId}`).remove();
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في حذف الصلاحية');
        });
    }
}

// Manage roles for permission
function manageRoles(permissionId) {
    // This could open a modal to manage which roles have this permission
    // For now, we'll just show an alert
    alert('استخدم مصفوفة الأدوار والصلاحيات أدناه لإدارة الأدوار المرتبطة بهذه الصلاحية');
}

// Handle create form submission
document.getElementById('createPermissionForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/permissions', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateModal();
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ في إنشاء الصلاحية');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إنشاء الصلاحية');
    });
});

// Handle edit form submission
document.getElementById('editPermissionForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const permissionId = document.getElementById('editPermissionId').value;
    const formData = new FormData(this);

    fetch(`/admin/permissions/${permissionId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ في تحديث الصلاحية');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الصلاحية');
    });
});

// Handle group selection in create form
document.querySelector('select[name="group"]').addEventListener('change', function() {
    const newGroupInput = document.querySelector('input[name="new_group"]');
    if (this.value === 'new') {
        newGroupInput.classList.remove('hidden');
        newGroupInput.required = true;
    } else {
        newGroupInput.classList.add('hidden');
        newGroupInput.required = false;
        newGroupInput.value = '';
    }
});

// Search on Enter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        filterPermissions();
    }
});

// Group filter change
document.getElementById('group').addEventListener('change', function() {
    filterPermissions();
});
</script>
@endsection
