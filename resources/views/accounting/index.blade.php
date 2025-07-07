@extends('layouts.app')

@section('title', 'المحاسبة - MaxCon ERP')
@section('page-title', 'المحاسبة')

@push('styles')
<style>
/* Accounting Page Hover Effects */
.account-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.account-card:hover .account-code {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

/* Dropdown Menu Styles */
.dropdown-menu {
    position: absolute;
    left: 0;
    margin-top: 0.5rem;
    width: 12rem;
    background-color: white;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 50;
    border: 1px solid #e5e7eb;
}

.dropdown-menu.hidden {
    display: none;
}

.dropdown-menu a {
    display: block;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: #374151;
    text-decoration: none;
    transition: background-color 0.2s ease;
}

.dropdown-menu a:hover {
    background-color: #f3f4f6;
}

.dropdown-menu a.text-red-600:hover {
    background-color: #fef2f2;
}

.account-card:hover .account-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.account-card:hover .account-balance {
    color: #6f42c1 !important;
    font-weight: bold;
    transition: all 0.3s ease;
}

.type-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.hierarchy-indicator:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(111, 66, 193, 0.2);
    transition: all 0.3s ease;
}

.stats-card:hover .stats-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.action-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
}

.tree-toggle:hover {
    background: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}

.balance-positive {
    color: #10b981;
}

.balance-negative {
    color: #ef4444;
}

.balance-zero {
    color: #6b7280;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">دليل الحسابات</h1>
                <p class="text-gray-600">إدارة الحسابات المالية ودليل الحسابات</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('accounting.accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    حساب جديد
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="stats-card bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">إجمالي الحسابات</p>
                        <p class="stats-number text-2xl font-bold text-blue-900">{{ $accounts->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">الأصول</p>
                        <p class="stats-number text-2xl font-bold text-green-900">
                            {{ $accounts->where('type', 'asset')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-coins text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600">الخصوم</p>
                        <p class="stats-number text-2xl font-bold text-red-900">
                            {{ $accounts->where('type', 'liability')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">حقوق الملكية</p>
                        <p class="stats-number text-2xl font-bold text-purple-900">
                            {{ $accounts->where('type', 'equity')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">الإيرادات والمصروفات</p>
                        <p class="stats-number text-2xl font-bold text-orange-900">
                            {{ $accounts->whereIn('type', ['revenue', 'expense'])->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="اسم الحساب أو الكود..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع الحساب</label>
                <select id="type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الأنواع</option>
                    @foreach($filters['types_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">النوع الفرعي</label>
                <select id="subtype" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الأنواع الفرعية</option>
                    @foreach($filters['subtypes_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحساب الأب</label>
                <select id="parent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الحسابات</option>
                    @foreach($filters['parent_accounts'] as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name_ar ?? $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحسابات الرئيسية فقط</label>
                <select id="root_only" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">الكل</option>
                    <option value="true">الرئيسية فقط</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Accounts List -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                @forelse($accounts as $account)
                <div class="account-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 space-x-reverse mb-2">
                                <!-- Hierarchy Indicator -->
                                @if($account->parent_id)
                                <span class="hierarchy-indicator text-gray-400">
                                    @for($i = 0; $i < substr_count($account->hierarchy_path ?? '', ' > '); $i++)
                                        <i class="fas fa-angle-left"></i>
                                    @endfor
                                </span>
                                @endif

                                <!-- Tree Toggle for accounts with children -->
                                @if($account->children && $account->children->count() > 0)
                                <button class="tree-toggle w-6 h-6 bg-gray-200 rounded text-xs flex items-center justify-center" 
                                        onclick="toggleChildren('children-{{ $account->id }}')">
                                    <i class="fas fa-plus"></i>
                                </button>
                                @endif

                                <span class="account-code text-sm font-mono bg-gray-200 px-2 py-1 rounded">{{ $account->code }}</span>
                                <h3 class="account-name text-lg font-semibold text-gray-900">
                                    {{ $account->name_ar ?? $account->name }}
                                </h3>
                                <span class="type-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($account->type)
                                        @case('asset') bg-green-100 text-green-800 @break
                                        @case('liability') bg-red-100 text-red-800 @break
                                        @case('equity') bg-purple-100 text-purple-800 @break
                                        @case('revenue') bg-blue-100 text-blue-800 @break
                                        @case('expense') bg-orange-100 text-orange-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['types_ar'][$account->type] ?? $account->type }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-2">
                                <div>
                                    <span class="font-medium">النوع الفرعي:</span>
                                    <span>{{ $filters['subtypes_ar'][$account->subtype] ?? $account->subtype }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">الرصيد الافتتاحي:</span>
                                    <span class="font-semibold">{{ number_format($account->opening_balance, 0) }} د.ع</span>
                                </div>
                                <div>
                                    <span class="font-medium">الرصيد الحالي:</span>
                                    <span class="account-balance font-semibold 
                                        @if($account->calculated_balance > 0) balance-positive
                                        @elseif($account->calculated_balance < 0) balance-negative
                                        @else balance-zero
                                        @endif
                                    ">
                                        {{ number_format($account->calculated_balance, 0) }} د.ع
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium">العملة:</span>
                                    <span>{{ $account->currency }}</span>
                                </div>
                            </div>

                            @if($account->description || $account->description_ar)
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">الوصف:</span>
                                <span>{{ $account->description_ar ?? $account->description }}</span>
                            </div>
                            @endif

                            @if($account->hierarchy_path_ar)
                            <div class="text-xs text-gray-500 mt-1">
                                <span class="font-medium">المسار:</span>
                                <span>{{ $account->hierarchy_path_ar }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 space-x-reverse">
                            <a href="{{ route('accounting.accounts.show', $account) }}"
                               class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                               title="عرض التفاصيل">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            
                            @if(!$account->is_system_account)
                            <a href="{{ route('accounting.accounts.edit', $account) }}"
                               class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-all duration-200"
                               title="تعديل">
                                <i class="fas fa-edit text-sm"></i>
                            </a>
                            @endif

                            <div class="relative">
                                <button class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-all duration-200"
                                        onclick="toggleDropdown('dropdown-{{ $account->id }}')"
                                        title="المزيد من الخيارات">
                                    <i class="fas fa-ellipsis-v text-sm"></i>
                                </button>
                                <div id="dropdown-{{ $account->id }}" class="dropdown-menu hidden">
                                    <div class="py-1">
                                        <a href="{{ route('accounting.accounts.report', $account) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-chart-line ml-2"></i>
                                            تقرير الحساب
                                        </a>
                                        <a href="{{ route('accounting.accounts.transactions', $account) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-history ml-2"></i>
                                            سجل الحركات
                                        </a>
                                        <a href="{{ route('accounting.accounts.create', ['parent_id' => $account->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-plus ml-2"></i>
                                            إضافة حساب فرعي
                                        </a>
                                        @if(!$account->is_system_account && !$account->children->count())
                                        <a href="#"
                                           data-account-id="{{ $account->id }}"
                                           onclick="deleteAccount(this.dataset.accountId); return false;"
                                           class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-trash ml-2"></i>
                                            حذف الحساب
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Children Accounts (Collapsible) -->
                    @if($account->children && $account->children->count() > 0)
                    <div id="children-{{ $account->id }}" class="hidden mt-4 mr-8 space-y-2">
                        @foreach($account->children as $child)
                        <div class="bg-white rounded p-3 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">{{ $child->code }}</span>
                                    <span class="font-medium">{{ $child->name_ar ?? $child->name }}</span>
                                    <span class="text-sm text-gray-600">{{ number_format($child->current_balance, 0) }} د.ع</span>
                                </div>
                                <div class="flex items-center space-x-1 space-x-reverse">
                                    <a href="{{ route('accounting.accounts.show', $child) }}"
                                       class="inline-flex items-center justify-center w-6 h-6 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded transition-all duration-200"
                                       title="عرض">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @if(!$child->is_system_account)
                                    <a href="{{ route('accounting.accounts.edit', $child) }}"
                                       class="inline-flex items-center justify-center w-6 h-6 text-green-600 hover:text-green-900 hover:bg-green-50 rounded transition-all duration-200"
                                       title="تعديل">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-12">
                    <i class="fas fa-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد حسابات</h3>
                    <p class="text-gray-500 mb-4">لم يتم العثور على أي حسابات</p>
                    <a href="{{ route('accounting.accounts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        إنشاء حساب جديد
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($accounts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $accounts->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('hidden');
    
    // Close other dropdowns
    document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
        if (el.id !== id) {
            el.classList.add('hidden');
        }
    });
}

function toggleChildren(id) {
    const children = document.getElementById(id);
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    
    children.classList.toggle('hidden');
    
    if (children.classList.contains('hidden')) {
        icon.classList.remove('fa-minus');
        icon.classList.add('fa-plus');
    } else {
        icon.classList.remove('fa-plus');
        icon.classList.add('fa-minus');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick*="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            el.classList.add('hidden');
        });
    }
});

// Filter functionality
document.getElementById('search').addEventListener('input', function() {
    filterAccounts();
});

document.getElementById('type').addEventListener('change', function() {
    filterAccounts();
});

document.getElementById('subtype').addEventListener('change', function() {
    filterAccounts();
});

document.getElementById('parent_id').addEventListener('change', function() {
    filterAccounts();
});

document.getElementById('root_only').addEventListener('change', function() {
    filterAccounts();
});

function filterAccounts() {
    const search = document.getElementById('search').value;
    const type = document.getElementById('type').value;
    const subtype = document.getElementById('subtype').value;
    const parentId = document.getElementById('parent_id').value;
    const rootOnly = document.getElementById('root_only').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (type) params.append('type', type);
    if (subtype) params.append('subtype', subtype);
    if (parentId) params.append('parent_id', parentId);
    if (rootOnly) params.append('root_only', rootOnly);

    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

function toggleDropdown(dropdownId) {
    // Close all other dropdowns first
    document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
        if (dropdown.id !== dropdownId) {
            dropdown.classList.add('hidden');
        }
    });

    // Toggle the clicked dropdown
    const dropdown = document.getElementById(dropdownId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');

        // Position the dropdown correctly
        const button = dropdown.previousElementSibling;
        const rect = button.getBoundingClientRect();
        const dropdownRect = dropdown.getBoundingClientRect();

        // Check if dropdown goes off screen and adjust position
        if (rect.left + dropdownRect.width > window.innerWidth) {
            dropdown.style.left = 'auto';
            dropdown.style.right = '0';
        } else {
            dropdown.style.left = '0';
            dropdown.style.right = 'auto';
        }
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    // Check if the click is outside any dropdown container
    if (!event.target.closest('.relative')) {
        document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
            dropdown.classList.add('hidden');
        });
    }
});

// Close dropdowns when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
            dropdown.classList.add('hidden');
        });
    }
});

function deleteAccount(accountId) {
    if (confirm('هل أنت متأكد من حذف هذا الحساب؟')) {
        // Implement delete functionality
        fetch(`/accounting/accounts/${accountId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('تم حذف الحساب بنجاح');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حذف الحساب');
        });
    }
}
</script>
@endsection
