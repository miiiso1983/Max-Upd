@extends('layouts.app')

@section('title', 'المنتجات - MaxCon ERP')
@section('page-title', 'إدارة المنتجات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إدارة المنتجات</h1>
            <p class="text-gray-600">إدارة قاعدة بيانات المنتجات والأدوية</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('inventory.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus ml-2"></i>
                منتج جديد
            </a>
            <button onclick="openImportModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-upload ml-2"></i>
                رفع Excel
            </button>
            <button onclick="downloadTemplate()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-file-excel ml-2"></i>
                تحميل النموذج
            </button>
            <button onclick="exportProducts()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-download ml-2"></i>
                تصدير
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المنتجات</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-products">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">منتجات نشطة</p>
                    <p class="text-2xl font-bold text-gray-900" id="active-products">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">مخزون منخفض</p>
                    <p class="text-2xl font-bold text-gray-900" id="low-stock-count">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">نفد المخزون</p>
                    <p class="text-2xl font-bold text-gray-900" id="out-of-stock-count">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.products.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="اسم المنتج، الكود، الباركود..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الفئة</label>
                <x-searchable-dropdown
                    name="category_id"
                    placeholder="جميع الفئات"
                    search-placeholder="ابحث في الفئات..."
                    :options="[['value' => '', 'text' => 'جميع الفئات']]"
                    value="{{ request('category_id') }}"
                />
            </div>

            <!-- Manufacturer Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الشركة المصنعة</label>
                <x-searchable-dropdown
                    name="manufacturer_id"
                    placeholder="جميع الشركات"
                    search-placeholder="ابحث في الشركات..."
                    :options="[['value' => '', 'text' => 'جميع الشركات']]"
                    value="{{ request('manufacturer_id') }}"
                />
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <x-searchable-dropdown
                    name="status"
                    placeholder="جميع الحالات"
                    search-placeholder="ابحث في الحالات..."
                    :options="[
                        ['value' => '', 'text' => 'جميع الحالات'],
                        ['value' => 'active', 'text' => 'نشط'],
                        ['value' => 'inactive', 'text' => 'غير نشط'],
                        ['value' => 'low_stock', 'text' => 'مخزون منخفض'],
                        ['value' => 'out_of_stock', 'text' => 'نفد المخزون']
                    ]"
                    value="{{ request('status') }}"
                />
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search ml-1"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('inventory.products.create') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors hover-scale">
                <i class="fas fa-plus-circle text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">إضافة منتج</span>
            </a>
            <a href="{{ route('inventory.products.low-stock') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors hover-scale">
                <i class="fas fa-exclamation-triangle text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">مخزون منخفض</span>
            </a>
            <a href="{{ route('inventory.products.expiring') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors hover-scale">
                <i class="fas fa-calendar-times text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">منتهية الصلاحية</span>
            </a>
            <button onclick="exportProducts()" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors hover-scale">
                <i class="fas fa-download text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">تصدير البيانات</span>
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة المنتجات</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفئة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة المصنعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزون</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السعر</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                    <!-- Products will be loaded here -->
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                                <p class="text-lg">جاري تحميل المنتجات...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200" id="pagination-container">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">تفاصيل المنتج</h3>
                <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalContent">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">رفع منتجات من ملف Excel</h3>
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
                                <li>الحقول المطلوبة: اسم المنتج، الفئة، وحدة القياس</li>
                                <li>يمكن رفع حتى 1000 منتج في المرة الواحدة</li>
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
                                <span class="mr-2 text-sm text-gray-700">تخطي المنتجات المكررة</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="update_existing" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-700">تحديث المنتجات الموجودة</span>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    loadStats();
});

function loadProducts() {
    const params = new URLSearchParams(window.location.search);
    const apiUrl = '/api/inventory/products?' + params.toString();
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            updateProductsTable(data.products);
            updatePagination(data.products);
        })
        .catch(error => {
            console.error('Error loading products:', error);
            document.getElementById('products-table-body').innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="text-red-500">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                            <p class="text-lg">حدث خطأ في تحميل المنتجات</p>
                        </div>
                    </td>
                </tr>
            `;
        });
}

function loadStats() {
    fetch('/api/inventory/dashboard')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-products').textContent = data.stats.total_products || 0;
            document.getElementById('active-products').textContent = data.stats.total_products || 0;
            document.getElementById('low-stock-count').textContent = data.stats.low_stock_products || 0;
            document.getElementById('out-of-stock-count').textContent = data.stats.out_of_stock_products || 0;
        })
        .catch(error => console.error('Error loading stats:', error));
}

function updateProductsTable(products) {
    const tbody = document.getElementById('products-table-body');
    
    if (products.data && products.data.length > 0) {
        tbody.innerHTML = products.data.map(product => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center ml-3">
                            <i class="fas fa-pills text-green-600"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">${product.name}</div>
                            <div class="text-sm text-gray-500">${product.sku}</div>
                            ${product.barcode ? `<div class="text-sm text-gray-500">${product.barcode}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${product.category ? product.category.name : 'غير محدد'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${product.manufacturer ? product.manufacturer.name : 'غير محدد'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium ${getStockClass(product.current_stock, product.minimum_stock)}">
                        ${product.current_stock || 0}
                    </div>
                    ${product.minimum_stock ? `<div class="text-xs text-gray-500">الحد الأدنى: ${product.minimum_stock}</div>` : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${product.selling_price ? MaxCon.formatCurrency(product.selling_price) : 'غير محدد'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${product.is_active ? 'نشط' : 'غير نشط'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2 space-x-reverse">
                        <button onclick="viewProduct(${product.id})" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="/inventory/products/${product.id}/edit" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="adjustStock(${product.id})" class="text-purple-600 hover:text-purple-900">
                            <i class="fas fa-warehouse"></i>
                        </button>
                        <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <i class="fas fa-boxes text-4xl mb-4"></i>
                        <p class="text-lg">لا توجد منتجات</p>
                        <p class="text-sm">ابدأ بإضافة منتج جديد</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

function getStockClass(currentStock, minimumStock) {
    if (currentStock <= 0) return 'text-red-600';
    if (currentStock <= minimumStock) return 'text-orange-600';
    return 'text-gray-900';
}

function updatePagination(products) {
    // Implement pagination UI update
    const container = document.getElementById('pagination-container');
    // This would be implemented based on the pagination structure
}

function viewProduct(productId) {
    fetch(`/api/inventory/products/${productId}`)
        .then(response => response.json())
        .then(product => {
            document.getElementById('modalTitle').textContent = `تفاصيل المنتج: ${product.name}`;
            document.getElementById('modalContent').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">المعلومات الأساسية</h4>
                        <p><strong>الاسم:</strong> ${product.name}</p>
                        <p><strong>الكود:</strong> ${product.sku}</p>
                        <p><strong>الباركود:</strong> ${product.barcode || 'غير محدد'}</p>
                        <p><strong>الفئة:</strong> ${product.category ? product.category.name : 'غير محدد'}</p>
                        <p><strong>الشركة المصنعة:</strong> ${product.manufacturer ? product.manufacturer.name : 'غير محدد'}</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">المخزون والأسعار</h4>
                        <p><strong>المخزون الحالي:</strong> ${product.current_stock || 0}</p>
                        <p><strong>الحد الأدنى:</strong> ${product.minimum_stock || 'غير محدد'}</p>
                        <p><strong>سعر التكلفة:</strong> ${product.cost_price ? MaxCon.formatCurrency(product.cost_price) : 'غير محدد'}</p>
                        <p><strong>سعر البيع:</strong> ${product.selling_price ? MaxCon.formatCurrency(product.selling_price) : 'غير محدد'}</p>
                        <p><strong>الحالة:</strong> ${product.is_active ? 'نشط' : 'غير نشط'}</p>
                    </div>
                </div>
                ${product.description ? `<div class="mt-4"><h4 class="font-medium text-gray-900 mb-2">الوصف</h4><p>${product.description}</p></div>` : ''}
            `;
            document.getElementById('productModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading product details:', error);
            alert('حدث خطأ في تحميل تفاصيل المنتج');
        });
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}

function adjustStock(productId) {
    // Implement stock adjustment functionality
    window.location.href = `/inventory/products/${productId}/adjust-stock`;
}

function deleteProduct(productId) {
    if (confirm('هل أنت متأكد من حذف هذا المنتج؟')) {
        fetch(`/api/inventory/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadProducts();
                MaxCon.showNotification('تم حذف المنتج بنجاح', 'success');
            } else {
                alert('حدث خطأ أثناء حذف المنتج');
            }
        })
        .catch(error => {
            console.error('Error deleting product:', error);
            alert('حدث خطأ أثناء حذف المنتج');
        });
    }
}

function exportProducts() {
    // Show loading notification
    MaxCon.showNotification('جاري تصدير المنتجات...', 'info');

    // Use web route instead of API route to avoid authentication issues
    window.location.href = '{{ route("inventory.products.export") }}';
}

// Import Modal Functions
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
    MaxCon.showNotification('جاري تحميل النموذج...', 'info');

    // Use web route instead of API route
    window.location.href = '{{ route("inventory.products.template") }}';
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
            MaxCon.showNotification('يرجى اختيار ملف Excel صحيح (.xlsx أو .xls)', 'error');
            input.value = '';
            fileInfo.classList.add('hidden');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            MaxCon.showNotification('حجم الملف كبير جداً. الحد الأقصى 10 ميجابايت', 'error');
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
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const fileInput = document.getElementById('excel-file');
    const file = fileInput.files[0];

    if (!file) {
        MaxCon.showNotification('يرجى اختيار ملف Excel', 'warning');
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
                MaxCon.showNotification(response.message, 'success');
                closeImportModal();
                loadProducts(); // Reload products list

                // Show import summary
                if (response.summary) {
                    showImportSummary(response.summary);
                }
            } else {
                MaxCon.showNotification(response.message || 'حدث خطأ أثناء رفع الملف', 'error');

                // Show validation errors if any
                if (response.errors && response.errors.length > 0) {
                    showValidationErrors(response.errors);
                }
            }
        } else {
            MaxCon.showNotification('حدث خطأ أثناء رفع الملف', 'error');
        }

        // Reset form
        uploadProgress.classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload ml-2"></i>رفع الملف';
        progressBar.style.width = '0%';
        progressPercentage.textContent = '0%';
    });

    xhr.addEventListener('error', function() {
        MaxCon.showNotification('حدث خطأ في الشبكة', 'error');
        uploadProgress.classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload ml-2"></i>رفع الملف';
    });

    xhr.open('POST', '{{ route("inventory.products.import") }}');
    xhr.send(formData);
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
@endpush
