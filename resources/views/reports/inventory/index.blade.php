@extends('layouts.app')

@section('title', 'تقارير المخزون')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">تقارير المخزون</h1>
            <p class="text-gray-600 mt-1">تقارير شاملة عن حالة المخزون والحركات والتقييم</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="exportAllInventoryReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-download ml-2"></i>
                تصدير جميع التقارير
            </button>
            <button onclick="scheduleInventoryReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-calendar-alt ml-2"></i>
                جدولة التقارير
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي المنتجات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-warehouse text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">قيمة المخزون</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['inventory_value'] ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">منتجات منخفضة المخزون</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['low_stock_items'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">منتجات نفدت من المخزون</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['out_of_stock_items'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Stock Level Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-blue-100 text-blue-600 ml-3">
                    <i class="fas fa-layer-group text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير مستويات المخزون</h3>
            </div>
            <p class="text-gray-600 mb-4">تقارير عن حالة المخزون الحالية والتنبيهات</p>
            <div class="space-y-2">
                <a href="{{ route('reports.inventory.stock-levels') }}" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-chart-bar ml-2"></i>
                    مستويات المخزون الحالية
                </a>
                <button onclick="generateInventoryReport('low-stock')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-exclamation-triangle ml-2"></i>
                    تقرير المخزون المنخفض
                </button>
                <button onclick="generateInventoryReport('out-of-stock')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-times-circle ml-2"></i>
                    تقرير المخزون المنتهي
                </button>
            </div>
        </div>

        <!-- Movement Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-green-100 text-green-600 ml-3">
                    <i class="fas fa-exchange-alt text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير حركة المخزون</h3>
            </div>
            <p class="text-gray-600 mb-4">تتبع حركات الدخول والخروج للمخزون</p>
            <div class="space-y-2">
                <a href="{{ route('reports.inventory.movements') }}" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-list ml-2"></i>
                    جميع حركات المخزون
                </a>
                <button onclick="generateInventoryReport('inbound-movements')" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-down ml-2"></i>
                    حركات الدخول
                </button>
                <button onclick="generateInventoryReport('outbound-movements')" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-up ml-2"></i>
                    حركات الخروج
                </button>
            </div>
        </div>

        <!-- Valuation Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-yellow-100 text-yellow-600 ml-3">
                    <i class="fas fa-dollar-sign text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير تقييم المخزون</h3>
            </div>
            <p class="text-gray-600 mb-4">تقييم قيمة المخزون والتحليل المالي</p>
            <div class="space-y-2">
                <a href="{{ route('reports.inventory.valuation') }}" class="block w-full text-right bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-calculator ml-2"></i>
                    تقييم المخزون الحالي
                </a>
                <button onclick="generateInventoryReport('aging-analysis')" class="block w-full text-right bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-clock ml-2"></i>
                    تحليل عمر المخزون
                </button>
                <button onclick="generateInventoryReport('cost-analysis')" class="block w-full text-right bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-chart-pie ml-2"></i>
                    تحليل التكاليف
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Performance Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-purple-100 text-purple-600 ml-3">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير الأداء</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button onclick="generateInventoryReport('turnover-analysis')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-sync-alt ml-2"></i>
                    معدل دوران المخزون
                </button>
                <button onclick="generateInventoryReport('abc-analysis')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-sort-alpha-down ml-2"></i>
                    تحليل ABC
                </button>
                <button onclick="generateInventoryReport('fast-slow-moving')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-tachometer-alt ml-2"></i>
                    المنتجات سريعة/بطيئة الحركة
                </button>
                <button onclick="generateInventoryReport('seasonal-trends')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-calendar-week ml-2"></i>
                    الاتجاهات الموسمية
                </button>
            </div>
        </div>

        <!-- Warehouse Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600 ml-3">
                    <i class="fas fa-warehouse text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير المستودعات</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button onclick="generateInventoryReport('warehouse-utilization')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-chart-area ml-2"></i>
                    استخدام المستودعات
                </button>
                <button onclick="generateInventoryReport('location-analysis')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    تحليل المواقع
                </button>
                <button onclick="generateInventoryReport('capacity-planning')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-expand-arrows-alt ml-2"></i>
                    تخطيط السعة
                </button>
                <button onclick="generateInventoryReport('cross-dock-analysis')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-truck ml-2"></i>
                    تحليل التوزيع المباشر
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Report Builder -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center mb-4">
            <div class="p-2 rounded-lg bg-gray-100 text-gray-600 ml-3">
                <i class="fas fa-cogs text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">منشئ تقارير المخزون المخصصة</h3>
        </div>
        <p class="text-gray-600 mb-4">إنشاء تقارير مخصصة للمخزون حسب احتياجاتك</p>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التقرير</label>
                <select id="inventoryReportType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">اختر نوع التقرير</option>
                    <option value="stock-levels">مستويات المخزون</option>
                    <option value="movements">حركات المخزون</option>
                    <option value="valuation">تقييم المخزون</option>
                    <option value="performance">تحليل الأداء</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المستودع</label>
                <select id="warehouseFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع المستودعات</option>
                    <option value="main">المستودع الرئيسي</option>
                    <option value="secondary">المستودع الثانوي</option>
                    <option value="retail">مستودع التجزئة</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" id="inventoryStartDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" id="inventoryEndDate" value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تنسيق التصدير</label>
                <select id="inventoryExportFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
        </div>
        
        <div class="flex justify-end space-x-2 space-x-reverse">
            <button onclick="previewInventoryReport()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-eye ml-2"></i>
                معاينة
            </button>
            <button onclick="generateCustomInventoryReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-file-alt ml-2"></i>
                إنشاء التقرير
            </button>
        </div>
    </div>
</div>

<script>
function generateInventoryReport(reportType) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الإنشاء...';
    button.disabled = true;
    
    // Simulate report generation
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Show success message and download
        alert(`تم إنشاء تقرير "${getInventoryReportName(reportType)}" بنجاح!\n\nسيتم تطوير ميزة التحميل قريباً.`);
    }, 2000);
}

function getInventoryReportName(reportType) {
    const reportNames = {
        'low-stock': 'المخزون المنخفض',
        'out-of-stock': 'المخزون المنتهي',
        'inbound-movements': 'حركات الدخول',
        'outbound-movements': 'حركات الخروج',
        'aging-analysis': 'تحليل عمر المخزون',
        'cost-analysis': 'تحليل التكاليف',
        'turnover-analysis': 'معدل دوران المخزون',
        'abc-analysis': 'تحليل ABC',
        'fast-slow-moving': 'المنتجات سريعة/بطيئة الحركة',
        'seasonal-trends': 'الاتجاهات الموسمية',
        'warehouse-utilization': 'استخدام المستودعات',
        'location-analysis': 'تحليل المواقع',
        'capacity-planning': 'تخطيط السعة',
        'cross-dock-analysis': 'تحليل التوزيع المباشر'
    };
    return reportNames[reportType] || reportType;
}

function generateCustomInventoryReport() {
    const reportType = document.getElementById('inventoryReportType').value;
    const warehouse = document.getElementById('warehouseFilter').value;
    const startDate = document.getElementById('inventoryStartDate').value;
    const endDate = document.getElementById('inventoryEndDate').value;
    const exportFormat = document.getElementById('inventoryExportFormat').value;
    
    if (!reportType) {
        alert('يرجى اختيار نوع التقرير');
        return;
    }
    
    // Simulate custom report generation
    alert(`سيتم إنشاء تقرير مخزون مخصص:\nالنوع: ${reportType}\nالمستودع: ${warehouse || 'جميع المستودعات'}\nالفترة: ${startDate} إلى ${endDate}\nالتنسيق: ${exportFormat}\n\nسيتم تطوير هذه الميزة قريباً!`);
}

function previewInventoryReport() {
    const reportType = document.getElementById('inventoryReportType').value;
    
    if (!reportType) {
        alert('يرجى اختيار نوع التقرير أولاً');
        return;
    }
    
    alert('سيتم تطوير ميزة المعاينة قريباً!');
}

function exportAllInventoryReports() {
    alert('سيتم تطوير ميزة تصدير جميع تقارير المخزون قريباً!\n\nستتمكن من تصدير مجموعة شاملة من التقارير بصيغ مختلفة.');
}

function scheduleInventoryReport() {
    alert('سيتم تطوير ميزة جدولة تقارير المخزون قريباً!\n\nستتمكن من جدولة التقارير للإرسال التلقائي يومياً أو أسبوعياً أو شهرياً.');
}
</script>
@endsection
