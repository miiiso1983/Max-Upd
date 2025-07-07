@extends('layouts.admin')

@section('title', 'إدارة الأدوار')
@section('page-title', 'إدارة الأدوار')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-tag text-blue-600 ml-3"></i>
                    إدارة الأدوار
                </h1>
                <p class="text-gray-600 mt-1">إدارة وتنظيم أدوار المستخدمين وصلاحياتهم</p>
            </div>
            @can('create roles')
            <button onclick="openCreateModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus ml-2"></i>
                إضافة دور جديد
            </button>
            @endcan
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">البحث في الأدوار</label>
                    <div class="relative">
                        <input type="text" id="search"
                               placeholder="ابحث بالاسم أو الوصف..."
                               value="{{ request('search') }}"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button onclick="filterRoles()"
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-filter ml-2"></i>
                        تطبيق الفلتر
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roles as $role)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" id="role-card-{{ $role->id }}">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $role->display_name ?? $role->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $role->name }}</p>
                    @if($role->description)
                        <p class="text-sm text-gray-600 mt-2">{{ $role->description }}</p>
                    @endif
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    @can('edit roles')
                    <button data-role-id="{{ $role->id }}"
                            onclick="editRole(this.dataset.roleId)"
                            class="text-blue-600 hover:text-blue-900" title="تعديل">
                        <i class="fas fa-edit"></i>
                    </button>
                    @endcan

                    @can('delete roles')
                    @if($role->name !== 'super_admin')
                    <button data-role-id="{{ $role->id }}"
                            onclick="deleteRole(this.dataset.roleId)"
                            class="text-red-600 hover:text-red-900" title="حذف">
                        <i class="fas fa-trash"></i>
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">عدد المستخدمين:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $role->users_count }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">عدد الصلاحيات:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $role->permissions->count() }}</span>
                </div>
                
                <div class="pt-3 border-t border-gray-200">
                    <button data-role-id="{{ $role->id }}"
                            onclick="managePermissions(this.dataset.roleId)"
                            class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm" title="إدارة الصلاحيات">
                        <i class="fas fa-key ml-2"></i>
                        إدارة الصلاحيات
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-user-tag text-gray-400 text-4xl mb-4"></i>
            <p class="text-gray-500">لا توجد أدوار</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($roles->hasPages())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    عرض {{ $roles->firstItem() }} إلى {{ $roles->lastItem() }} من أصل {{ $roles->total() }} دور
                </div>
                <div>
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</div>

<!-- Create Role Modal -->
<div id="createRoleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">إضافة دور جديد</h3>
            </div>
            <form id="createRoleForm" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم الدور (بالإنجليزية) *</label>
                            <input type="text" name="name" required 
                                   placeholder="admin, manager, user"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الاسم المعروض *</label>
                            <input type="text" name="display_name" required 
                                   placeholder="مدير، مشرف، مستخدم"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                        <textarea name="description" rows="3" 
                                  placeholder="وصف مختصر للدور..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                    </div>
                    
                    <!-- Permissions -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">الصلاحيات</label>
                        <div class="space-y-4 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-4">
                            @foreach($permissions as $group => $groupPermissions)
                            <div>
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="group-{{ $group }}" 
                                           onchange="toggleGroupPermissions('{{ $group }}')"
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <label for="group-{{ $group }}" class="mr-2 text-sm font-medium text-gray-900">
                                        {{ ucfirst($group) }}
                                    </label>
                                </div>
                                <div class="mr-6 space-y-1">
                                    @foreach($groupPermissions as $permission)
                                    <div class="flex items-center">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                               id="perm-{{ $permission->id }}" 
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded group-{{ $group }}-permission">
                                        <label for="perm-{{ $permission->id }}" class="mr-2 text-sm text-gray-700">
                                            {{ $permission->display_name ?? $permission->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3 space-x-reverse mt-6">
                    <button type="button" onclick="closeCreateModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div id="editRoleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">تعديل الدور</h3>
            </div>
            <form id="editRoleForm" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="editRoleId" name="role_id">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم الدور (بالإنجليزية) *</label>
                            <input type="text" id="editRoleName" name="name" required 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الاسم المعروض *</label>
                            <input type="text" id="editRoleDisplayName" name="display_name" required 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                        <textarea id="editRoleDescription" name="description" rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                    </div>
                    
                    <!-- Permissions -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">الصلاحيات</label>
                        <div id="editPermissionsContainer" class="space-y-4 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-4">
                            <!-- Will be populated by JavaScript -->
                        </div>
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

<!-- Manage Permissions Modal -->
<div id="managePermissionsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">إدارة صلاحيات الدور: <span id="manageRoleName"></span></h3>
            </div>
            <div class="p-6">
                <input type="hidden" id="manageRoleId">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($permissions as $group => $groupPermissions)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="manage-group-{{ $group }}" 
                                   onchange="toggleManageGroupPermissions('{{ $group }}')"
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="manage-group-{{ $group }}" class="mr-2 text-sm font-medium text-gray-900">
                                {{ ucfirst($group) }}
                            </label>
                        </div>
                        <div class="space-y-2">
                            @foreach($groupPermissions as $permission)
                            <div class="flex items-center">
                                <input type="checkbox" id="manage-perm-{{ $permission->id }}"
                                       name="permissions[]"
                                       value="{{ $permission->name }}"
                                       data-permission-id="{{ $permission->id }}"
                                       onchange="updateRolePermission(this.dataset.permissionId)"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded manage-group-{{ $group }}-permission">
                                <label for="manage-perm-{{ $permission->id }}" class="mr-2 text-sm text-gray-700">
                                    {{ $permission->display_name ?? $permission->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="closeManagePermissionsModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script>
// Filter roles
function filterRoles() {
    const search = document.getElementById('search').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);

    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

// Open create modal
function openCreateModal() {
    document.getElementById('createRoleModal').classList.remove('hidden');
}

// Close create modal
function closeCreateModal() {
    document.getElementById('createRoleModal').classList.add('hidden');
    document.getElementById('createRoleForm').reset();
}

// Open edit modal
function editRole(roleId) {
    fetch(`/admin/roles/${roleId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const role = data.role;
                document.getElementById('editRoleId').value = role.id;
                document.getElementById('editRoleName').value = role.name;
                document.getElementById('editRoleDisplayName').value = role.display_name || '';
                document.getElementById('editRoleDescription').value = role.description || '';

                // Populate permissions
                populateEditPermissions(data.permissions, role.permissions);

                document.getElementById('editRoleModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل بيانات الدور');
        });
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editRoleModal').classList.add('hidden');
    document.getElementById('editRoleForm').reset();
}

// Delete role
function deleteRole(roleId) {
    if (confirm('هل أنت متأكد من حذف هذا الدور؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        fetch(`/admin/roles/${roleId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`role-card-${roleId}`).remove();
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في حذف الدور');
        });
    }
}

// Manage permissions
function managePermissions(roleId) {
    fetch(`/admin/roles/${roleId}/permissions`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success !== false) {
                document.getElementById('manageRoleId').value = roleId;
                document.getElementById('manageRoleName').textContent = data.role.display_name || data.role.name;

                // Check current permissions
                const currentPermissions = data.permissions || [];
                document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                    checkbox.checked = currentPermissions.includes(checkbox.value);
                });

                // Update group checkboxes
                updateGroupCheckboxes('manage-');

                document.getElementById('managePermissionsModal').classList.remove('hidden');
            } else {
                alert(data.message || 'حدث خطأ في تحميل صلاحيات الدور');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل صلاحيات الدور: ' + error.message);
        });
}

// Close manage permissions modal
function closeManagePermissionsModal() {
    document.getElementById('managePermissionsModal').classList.add('hidden');
}

// Toggle group permissions
function toggleGroupPermissions(group) {
    const groupCheckbox = document.getElementById(`group-${group}`);
    const permissionCheckboxes = document.querySelectorAll(`.group-${group}-permission`);

    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = groupCheckbox.checked;
    });
}

// Toggle manage group permissions
function toggleManageGroupPermissions(group) {
    const groupCheckbox = document.getElementById(`manage-group-${group}`);
    const permissionCheckboxes = document.querySelectorAll(`.manage-group-${group}-permission`);

    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = groupCheckbox.checked;
    });

    // Update role permissions
    const roleId = document.getElementById('manageRoleId').value;
    const permissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked'))
                           .map(cb => cb.value);

    updateRolePermissions(roleId, permissions);
}

// Update role permission
function updateRolePermission(permissionId) {
    const roleId = document.getElementById('manageRoleId').value;
    const permissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked'))
                           .map(cb => cb.value);

    updateRolePermissions(roleId, permissions);
    updateGroupCheckboxes('manage-');
}

// Update role permissions via AJAX
function updateRolePermissions(roleId, permissions) {
    fetch(`/admin/roles/${roleId}/permissions`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ permissions: permissions })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message || 'تم تحديث الصلاحيات بنجاح');
        } else {
            alert(data.message || 'حدث خطأ في تحديث الصلاحيات');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الصلاحيات: ' + error.message);
    });
}

// Update group checkboxes based on individual permissions
function updateGroupCheckboxes(prefix = '') {
    const groups = ['users', 'roles', 'permissions', 'sales', 'inventory', 'reports'];

    groups.forEach(group => {
        const groupCheckbox = document.getElementById(`${prefix}group-${group}`);
        const permissionCheckboxes = document.querySelectorAll(`.${prefix}group-${group}-permission`);

        if (groupCheckbox && permissionCheckboxes.length > 0) {
            const checkedCount = Array.from(permissionCheckboxes).filter(cb => cb.checked).length;

            if (checkedCount === 0) {
                groupCheckbox.checked = false;
                groupCheckbox.indeterminate = false;
            } else if (checkedCount === permissionCheckboxes.length) {
                groupCheckbox.checked = true;
                groupCheckbox.indeterminate = false;
            } else {
                groupCheckbox.checked = false;
                groupCheckbox.indeterminate = true;
            }
        }
    });
}

// Populate edit permissions
function populateEditPermissions(allPermissions, rolePermissions) {
    const container = document.getElementById('editPermissionsContainer');
    container.innerHTML = '';

    const rolePermissionNames = rolePermissions.map(p => p.name);

    Object.keys(allPermissions).forEach(group => {
        const groupDiv = document.createElement('div');
        groupDiv.innerHTML = `
            <div class="flex items-center mb-2">
                <input type="checkbox" id="edit-group-${group}"
                       onchange="toggleEditGroupPermissions('${group}')"
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="edit-group-${group}" class="mr-2 text-sm font-medium text-gray-900">
                    ${group.charAt(0).toUpperCase() + group.slice(1)}
                </label>
            </div>
            <div class="mr-6 space-y-1">
                ${allPermissions[group].map(permission => `
                    <div class="flex items-center">
                        <input type="checkbox" name="permissions[]" value="${permission.name}"
                               id="edit-perm-${permission.id}"
                               ${rolePermissionNames.includes(permission.name) ? 'checked' : ''}
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded edit-group-${group}-permission">
                        <label for="edit-perm-${permission.id}" class="mr-2 text-sm text-gray-700">
                            ${permission.display_name || permission.name}
                        </label>
                    </div>
                `).join('')}
            </div>
        `;
        container.appendChild(groupDiv);
    });

    // Update group checkboxes
    updateGroupCheckboxes('edit-');
}

// Toggle edit group permissions
function toggleEditGroupPermissions(group) {
    const groupCheckbox = document.getElementById(`edit-group-${group}`);
    const permissionCheckboxes = document.querySelectorAll(`.edit-group-${group}-permission`);

    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = groupCheckbox.checked;
    });
}

// Handle create form submission
document.getElementById('createRoleForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/roles', {
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
            alert(data.message || 'حدث خطأ في إنشاء الدور');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إنشاء الدور');
    });
});

// Handle edit form submission
document.getElementById('editRoleForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const roleId = document.getElementById('editRoleId').value;
    const formData = new FormData(this);

    fetch(`/admin/roles/${roleId}`, {
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
            alert(data.message || 'حدث خطأ في تحديث الدور');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الدور');
    });
});

// Search on Enter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        filterRoles();
    }
});

// Add event listeners for permission checkboxes to update group checkboxes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('edit-group-') && e.target.classList.contains('-permission')) {
        updateGroupCheckboxes('edit-');
    }
});

// Show success message
function showSuccessMessage(message) {
    // Remove existing messages
    const existing = document.querySelectorAll('.success-message');
    existing.forEach(el => el.remove());

    // Create success message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'success-message fixed top-4 left-4 right-4 md:right-auto md:w-96 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
    messageDiv.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle ml-2"></i>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.appendChild(messageDiv);

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (messageDiv.parentElement) {
            messageDiv.remove();
        }
    }, 3000);
}
</script>
@endsection
