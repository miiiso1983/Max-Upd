@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')
@section('page-title', 'إدارة المستخدمين')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-users text-blue-600 ml-3"></i>
                    إدارة المستخدمين
                </h1>
                <p class="text-gray-600 mt-1">إدارة وتنظيم مستخدمي النظام</p>
            </div>
            @can('create users')
            <button onclick="openCreateModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-plus ml-2"></i>
                إضافة مستخدم جديد
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
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">البحث في المستخدمين</label>
                    <div class="relative">
                        <input type="text" id="search"
                               placeholder="ابحث بالاسم أو البريد الإلكتروني..."
                               value="{{ request('search') }}"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Role Filter -->
                <div>
                    <label for="role-filter" class="block text-sm font-medium text-gray-700 mb-2">تصفية حسب الدور</label>
                    <select id="role-filter" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">جميع الأدوار</option>
                        <option value="admin">مدير</option>
                        <option value="tenant-admin">مدير مستأجر</option>
                        <option value="user">مستخدم</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">قائمة المستخدمين</h3>
            <p class="text-sm text-gray-500 mt-1">إجمالي المستخدمين: {{ $users->total() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الدور</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإنشاء</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">العمليات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150" id="user-row-{{ $user->id }}">
                            <!-- User Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 ml-4">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center shadow-sm">
                                            <span class="text-white font-bold text-lg">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500 truncate">{{ $user->email }}</div>
                                        @if($user->phone)
                                            <div class="text-xs text-gray-400 mt-1">
                                                <i class="fas fa-phone ml-1"></i>
                                                {{ $user->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Roles -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-wrap justify-center gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $role->display_name ?? $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400">لا يوجد دور</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-1.5 h-1.5 ml-1.5 rounded-full {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                    {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>

                            <!-- Created Date -->
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-900">{{ $user->created_at->format('Y-m-d') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->created_at->format('H:i') }}</div>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center space-x-1 space-x-reverse">
                                    @can('view users')
                                    <button data-user-id="{{ $user->id }}"
                                            onclick="viewUser(this.dataset.userId)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="عرض التفاصيل">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    @endcan

                                    @can('edit users')
                                    <button data-user-id="{{ $user->id }}"
                                            onclick="editUser(this.dataset.userId)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-all duration-200"
                                            title="تعديل البيانات">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    @endcan

                                    @can('edit users')
                                    <button data-user-id="{{ $user->id }}"
                                            onclick="toggleUserStatus(this.dataset.userId)"
                                            class="inline-flex items-center justify-center w-8 h-8 {{ $user->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }} rounded-lg transition-all duration-200"
                                            title="{{ $user->is_active ? 'تعطيل المستخدم' : 'تفعيل المستخدم' }}">
                                        <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }} text-sm"></i>
                                    </button>
                                    @endcan

                                    @can('delete users')
                                    @if($user->id !== auth()->id())
                                    <button data-user-id="{{ $user->id }}"
                                            onclick="deleteUser(this.dataset.userId)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200"
                                            title="حذف المستخدم">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد مستخدمين</h3>
                                    <p class="text-gray-500 mb-4">لم يتم العثور على أي مستخدمين في النظام</p>
                                    @can('create users')
                                    <button onclick="openCreateModal()"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                        <i class="fas fa-plus ml-2"></i>
                                        إضافة مستخدم جديد
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        عرض {{ $users->firstItem() }} إلى {{ $users->lastItem() }} من أصل {{ $users->total() }} مستخدم
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
</div>
</div>


<!-- Create User Modal -->
<div id="createUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">إضافة مستخدم جديد</h3>
            </div>
            <form id="createUserForm" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الاسم الكامل *</label>
                        <input type="text" name="name" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                        <input type="email" name="email" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                        <input type="text" name="phone"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور *</label>
                        <input type="password" name="password" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تأكيد كلمة المرور *</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الدور *</label>
                        <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">اختر الدور</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->display_name ?? $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked 
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label class="mr-2 text-sm text-gray-700">المستخدم نشط</label>
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

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">تعديل المستخدم</h3>
            </div>
            <form id="editUserForm" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="editUserId" name="user_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الاسم الكامل *</label>
                        <input type="text" id="editUserName" name="name" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                        <input type="email" id="editUserEmail" name="email" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                        <input type="text" id="editUserPhone" name="phone"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور الجديدة</label>
                        <input type="password" name="password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">اتركه فارغاً إذا كنت لا تريد تغيير كلمة المرور</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الدور *</label>
                        <select id="editUserRole" name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">اختر الدور</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->display_name ?? $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="editUserActive" name="is_active" value="1" 
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label class="mr-2 text-sm text-gray-700">المستخدم نشط</label>
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
// Filter users
function filterUsers() {
    const search = document.getElementById('search').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);

    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

// Open create modal
function openCreateModal() {
    document.getElementById('createUserModal').classList.remove('hidden');
}

// Close create modal
function closeCreateModal() {
    document.getElementById('createUserModal').classList.add('hidden');
    document.getElementById('createUserForm').reset();
}

// Open edit modal
function editUser(userId) {
    fetch(`/admin/users/${userId}/edit`, {
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
            if (data.success) {
                const user = data.user;
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUserName').value = user.name;
                document.getElementById('editUserEmail').value = user.email;
                document.getElementById('editUserPhone').value = user.phone || '';
                document.getElementById('editUserActive').checked = user.is_active;

                // Set role in dropdown
                const roleSelect = document.getElementById('editUserRole');
                if (roleSelect && user.roles.length > 0) {
                    roleSelect.value = user.roles[0].name;
                }

                document.getElementById('editUserModal').classList.remove('hidden');
            } else {
                alert(data.message || 'حدث خطأ في تحميل بيانات المستخدم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل بيانات المستخدم: ' + error.message);
        });
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editUserModal').classList.add('hidden');
    document.getElementById('editUserForm').reset();
}

// View user
function viewUser(userId) {
    window.location.href = `/admin/users/${userId}`;
}

// Toggle user status
function toggleUserStatus(userId) {
    if (confirm('هل أنت متأكد من تغيير حالة المستخدم؟')) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                alert(data.message || 'حدث خطأ في تغيير حالة المستخدم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تغيير حالة المستخدم: ' + error.message);
        });
    }
}

// Delete user
function deleteUser(userId) {
    if (confirm('هل أنت متأكد من حذف هذا المستخدم؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
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
            if (data.success) {
                const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (userRow) {
                    userRow.remove();
                }
                showSuccessMessage(data.message);
            } else {
                alert(data.message || 'حدث خطأ في حذف المستخدم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في حذف المستخدم: ' + error.message);
        });
    }
}

// Handle create form submission
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/users', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
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
            closeCreateModal();
            showSuccessMessage('تم إنشاء المستخدم بنجاح');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'حدث خطأ في إنشاء المستخدم');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إنشاء المستخدم: ' + error.message);
    });
});

// Handle edit form submission
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const userId = document.getElementById('editUserId').value;
    const formData = new FormData(this);
    formData.append('_method', 'PUT');

    fetch(`/admin/users/${userId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
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
            closeEditModal();
            showSuccessMessage('تم تحديث المستخدم بنجاح');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'حدث خطأ في تحديث المستخدم');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث المستخدم: ' + error.message);
    });
});

// Search on Enter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        filterUsers();
    }
});

// Auto search on typing
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        filterUsers();
    }, 500);
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
