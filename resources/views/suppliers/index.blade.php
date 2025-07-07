@extends('layouts.app')

@section('title', 'الموردين - MaxCon ERP')
@section('page-title', 'الموردين')

@push('styles')
<style>
/* Suppliers Page Hover Effects */
.supplier-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.supplier-card:hover .supplier-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.supplier-card:hover .supplier-code {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.supplier-card:hover .contact-info {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.type-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.status-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.rating-stars:hover {
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

.preferred-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">الموردين</h1>
                <p class="text-gray-600">إدارة ومتابعة الموردين والشركاء التجاريين</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    مورد جديد
                </a>
                <button onclick="openImportModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-upload ml-2"></i>
                    رفع Excel
                </button>
                <button onclick="downloadTemplate()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel ml-2"></i>
                    تحميل النموذج
                </button>
                <button onclick="exportSuppliers()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-download ml-2"></i>
                    تصدير
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="stats-card bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">إجمالي الموردين</p>
                        <p class="stats-number text-2xl font-bold text-blue-900">{{ $suppliers->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">الموردين النشطين</p>
                        <p class="stats-number text-2xl font-bold text-green-900">
                            {{ $suppliers->where('status', 'active')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">الموردين المفضلين</p>
                        <p class="stats-number text-2xl font-bold text-purple-900">
                            {{ $suppliers->where('is_preferred', true)->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">إجمالي المشتريات</p>
                        <p class="stats-number text-2xl font-bold text-orange-900">
                            {{ number_format($suppliers->sum('total_purchases'), 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600">المبالغ المستحقة</p>
                        <p class="stats-number text-2xl font-bold text-red-900">
                            {{ number_format($suppliers->sum('outstanding_amount'), 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="اسم المورد أو الكود..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">النوع</label>
                <x-searchable-dropdown
                    name="type"
                    placeholder="جميع الأنواع"
                    search-placeholder="ابحث في الأنواع..."
                    :options="collect($filters['types_ar'])->map(function($value, $key) {
                        return ['value' => $key, 'text' => $value];
                    })->prepend(['value' => '', 'text' => 'جميع الأنواع'])->values()->toArray()"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <x-searchable-dropdown
                    name="status"
                    placeholder="جميع الحالات"
                    search-placeholder="ابحث في الحالات..."
                    :options="collect($filters['statuses_ar'])->map(function($value, $key) {
                        return ['value' => $key, 'text' => $value];
                    })->prepend(['value' => '', 'text' => 'جميع الحالات'])->values()->toArray()"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البلد</label>
                <x-searchable-dropdown
                    name="country"
                    placeholder="جميع البلدان"
                    search-placeholder="ابحث في البلدان..."
                    :options="collect($filters['countries'])->map(function($value, $key) {
                        return ['value' => $key, 'text' => $value];
                    })->prepend(['value' => '', 'text' => 'جميع البلدان'])->values()->toArray()"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المفضلين فقط</label>
                <x-searchable-dropdown
                    name="preferred"
                    placeholder="الكل"
                    search-placeholder="ابحث..."
                    :options="[
                        ['value' => '', 'text' => 'الكل'],
                        ['value' => 'true', 'text' => 'المفضلين فقط']
                    ]"
                />
            </div>
        </div>
    </div>

    <!-- Suppliers List -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                @forelse($suppliers as $supplier)
                <div class="supplier-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 space-x-reverse mb-2">
                                <h3 class="supplier-name text-lg font-semibold text-gray-900">
                                    {{ $supplier->name_ar ?? $supplier->name }}
                                </h3>
                                <span class="supplier-code text-sm text-gray-500 bg-gray-200 px-2 py-1 rounded">
                                    {{ $supplier->code }}
                                </span>
                                <span class="type-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($supplier->type)
                                        @case('manufacturer') bg-blue-100 text-blue-800 @break
                                        @case('distributor') bg-green-100 text-green-800 @break
                                        @case('wholesaler') bg-purple-100 text-purple-800 @break
                                        @case('importer') bg-orange-100 text-orange-800 @break
                                        @case('local') bg-gray-100 text-gray-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['types_ar'][$supplier->type] ?? $supplier->type }}
                                </span>
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($supplier->status)
                                        @case('active') bg-green-100 text-green-800 @break
                                        @case('inactive') bg-gray-100 text-gray-800 @break
                                        @case('suspended') bg-yellow-100 text-yellow-800 @break
                                        @case('blacklisted') bg-red-100 text-red-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['statuses_ar'][$supplier->status] ?? $supplier->status }}
                                </span>
                                @if($supplier->is_preferred)
                                <span class="preferred-badge px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star ml-1"></i>
                                    مفضل
                                </span>
                                @endif
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-2">
                                <div>
                                    <span class="font-medium">جهة الاتصال:</span>
                                    <span class="contact-info">{{ $supplier->contact_person ?? 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">الهاتف:</span>
                                    <span class="contact-info">{{ $supplier->phone ?? 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">البريد الإلكتروني:</span>
                                    <span class="contact-info">{{ $supplier->email ?? 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">المدينة:</span>
                                    <span>{{ $supplier->city }}, {{ $supplier->country }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">إجمالي المشتريات:</span>
                                    <span class="font-semibold text-green-600">{{ number_format($supplier->total_purchases, 0) }} د.ع</span>
                                </div>
                                <div>
                                    <span class="font-medium">عدد الطلبات:</span>
                                    <span>{{ $supplier->total_orders }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">متوسط الطلب:</span>
                                    <span>{{ number_format($supplier->average_order_value, 0) }} د.ع</span>
                                </div>
                                <div>
                                    <span class="font-medium">التقييم:</span>
                                    <span class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($supplier->rating))
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @elseif($i - 0.5 <= $supplier->rating)
                                                <i class="fas fa-star-half-alt text-yellow-400"></i>
                                            @else
                                                <i class="far fa-star text-gray-300"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-1">{{ number_format($supplier->rating, 1) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 space-x-reverse">
                            <a href="{{ route('suppliers.show', $supplier) }}" 
                               class="action-btn bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600">
                                <i class="fas fa-eye"></i>
                                عرض
                            </a>
                            
                            <a href="{{ route('suppliers.edit', $supplier) }}" 
                               class="action-btn bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>

                            <div class="relative">
                                <button class="action-btn bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600" 
                                        onclick="toggleDropdown('dropdown-{{ $supplier->id }}')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-{{ $supplier->id }}" class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <div class="py-1">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-shopping-cart ml-2"></i>
                                            طلب شراء جديد
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-chart-line ml-2"></i>
                                            تقرير الأداء
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-file-contract ml-2"></i>
                                            العقود
                                        </a>
                                        @if(!$supplier->is_preferred)
                                        <a href="#" class="block px-4 py-2 text-sm text-yellow-600 hover:bg-gray-100">
                                            <i class="fas fa-star ml-2"></i>
                                            إضافة للمفضلة
                                        </a>
                                        @else
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                            <i class="far fa-star ml-2"></i>
                                            إزالة من المفضلة
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i class="fas fa-truck text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا يوجد موردين</h3>
                    <p class="text-gray-500 mb-4">لم يتم العثور على أي موردين</p>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة مورد جديد
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($suppliers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">رفع موردين من ملف Excel</h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    <div class="mr-3">
                        <h3 class="text-sm font-medium text-blue-800">تعليمات الرفع</h3>
                        <div class="text-sm text-blue-700 mt-1">
                            <ul class="list-disc list-inside space-y-1">
                                <li>قم بتحميل النموذج أولاً وملء البيانات</li>
                                <li>تأكد من صحة البيانات قبل الرفع</li>
                                <li>الحقول المطلوبة: اسم المورد، النوع، البريد الإلكتروني</li>
                                <li>يمكن رفع حتى 500 مورد في المرة الواحدة</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اختر ملف Excel</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-excel text-4xl text-green-500 mb-3"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="excel-file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>اختر ملف</span>
                                        <input id="excel-file" name="excel_file" type="file" accept=".xlsx,.xls" class="sr-only" onchange="handleFileSelect(this)">
                                    </label>
                                    <p class="pr-1">أو اسحب الملف هنا</p>
                                </div>
                                <p class="text-xs text-gray-500">Excel files only (.xlsx, .xls)</p>
                            </div>
                        </div>
                        <div id="file-info" class="mt-2 hidden">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-file-excel text-green-500 ml-2"></i>
                                <span id="file-name"></span>
                                <span id="file-size" class="text-gray-400 mr-2"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="skip_duplicates" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-700">تخطي الموردين المكررين</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="update_existing" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-700">تحديث الموردين الموجودين</span>
                            </label>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="upload-progress" class="hidden">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                            <span>جاري الرفع...</span>
                            <span id="progress-percentage">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 space-x-reverse mt-6">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button type="submit" id="upload-btn" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-upload ml-2"></i>
                        رفع الملف
                    </button>
                </div>
            </form>
        </div>
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
    filterSuppliers();
});

document.getElementById('type').addEventListener('change', function() {
    filterSuppliers();
});

document.getElementById('status').addEventListener('change', function() {
    filterSuppliers();
});

document.getElementById('country').addEventListener('change', function() {
    filterSuppliers();
});

document.getElementById('preferred').addEventListener('change', function() {
    filterSuppliers();
});

function filterSuppliers() {
    const search = document.getElementById('search').value;
    const type = document.getElementById('type').value;
    const status = document.getElementById('status').value;
    const country = document.getElementById('country').value;
    const preferred = document.getElementById('preferred').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (type) params.append('type', type);
    if (status) params.append('status', status);
    if (country) params.append('country', country);
    if (preferred) params.append('preferred', preferred);
    
    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

// Import/Export Functions
function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
    document.getElementById('importForm').reset();
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('upload-progress').classList.add('hidden');
}

function downloadTemplate() {
    // Show loading notification
    if (typeof MaxCon !== 'undefined') {
        MaxCon.showNotification('جاري تحميل النموذج...', 'info');
    }

    // Use web route for template download
    window.location.href = '/suppliers/template';
}

function exportSuppliers() {
    // Show loading notification
    if (typeof MaxCon !== 'undefined') {
        MaxCon.showNotification('جاري تصدير الموردين...', 'info');
    }

    // Use web route for export
    window.location.href = '/suppliers/export';
}

function handleFileSelect(input) {
    const file = input.files[0];
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = `(${formatFileSize(file.size)})`;
        fileInfo.classList.remove('hidden');

        // Validate file type
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ];

        if (!allowedTypes.includes(file.type)) {
            alert('يرجى اختيار ملف Excel صحيح (.xlsx أو .xls)');
            input.value = '';
            fileInfo.classList.add('hidden');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('حجم الملف كبير جداً. الحد الأقصى 10 ميجابايت');
            input.value = '';
            fileInfo.classList.add('hidden');
            return;
        }
    } else {
        fileInfo.classList.add('hidden');
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const fileInput = document.getElementById('excel-file');
        const file = fileInput.files[0];

        if (!file) {
            alert('يرجى اختيار ملف Excel');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('skip_duplicates', document.querySelector('[name="skip_duplicates"]').checked);
        formData.append('update_existing', document.querySelector('[name="update_existing"]').checked);
        formData.append('_token', document.querySelector('[name="_token"]').value);

        // Show progress
        const uploadProgress = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const uploadBtn = document.getElementById('upload-btn');

        uploadProgress.classList.remove('hidden');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الرفع...';

        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressPercentage.textContent = Math.round(percentComplete) + '%';
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                if (response.success) {
                    if (typeof MaxCon !== 'undefined') {
                        MaxCon.showNotification(response.message, 'success');
                    } else {
                        alert(response.message);
                    }
                    closeImportModal();

                    // Reload page to show new suppliers
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                    // Show import summary
                    if (response.summary) {
                        showImportSummary(response.summary);
                    }
                } else {
                    if (typeof MaxCon !== 'undefined') {
                        MaxCon.showNotification(response.message || 'حدث خطأ أثناء رفع الملف', 'error');
                    } else {
                        alert(response.message || 'حدث خطأ أثناء رفع الملف');
                    }

                    // Show validation errors if any
                    if (response.errors && response.errors.length > 0) {
                        showValidationErrors(response.errors);
                    }
                }
            } else {
                if (typeof MaxCon !== 'undefined') {
                    MaxCon.showNotification('حدث خطأ أثناء رفع الملف', 'error');
                } else {
                    alert('حدث خطأ أثناء رفع الملف');
                }
            }

            // Reset form
            uploadProgress.classList.add('hidden');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload ml-2"></i>رفع الملف';
            progressBar.style.width = '0%';
            progressPercentage.textContent = '0%';
        });

        xhr.addEventListener('error', function() {
            if (typeof MaxCon !== 'undefined') {
                MaxCon.showNotification('حدث خطأ في الشبكة', 'error');
            } else {
                alert('حدث خطأ في الشبكة');
            }
            uploadProgress.classList.add('hidden');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload ml-2"></i>رفع الملف';
        });

        xhr.open('POST', '/suppliers/import');
        xhr.send(formData);
    });
});

function showImportSummary(summary) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">ملخص الاستيراد</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">إجمالي الصفوف:</span>
                        <span class="font-semibold">${summary.total_rows || 0}</span>
                    </div>
                    <div class="flex justify-between text-green-600">
                        <span>تم إضافتها بنجاح:</span>
                        <span class="font-semibold">${summary.imported || 0}</span>
                    </div>
                    <div class="flex justify-between text-yellow-600">
                        <span>تم تخطيها:</span>
                        <span class="font-semibold">${summary.skipped || 0}</span>
                    </div>
                    <div class="flex justify-between text-red-600">
                        <span>فشل في الإضافة:</span>
                        <span class="font-semibold">${summary.failed || 0}</span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        موافق
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function showValidationErrors(errors) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">أخطاء التحقق</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الصف</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحقل</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الخطأ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${errors.map(error => `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${error.row}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${error.field}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">${error.message}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>
@endsection
