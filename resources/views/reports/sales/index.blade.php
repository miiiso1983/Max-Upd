@extends('layouts.app')

@section('title', 'تقارير المبيعات')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">تقارير المبيعات</h1>
            <p class="text-gray-600 mt-1">تقارير شاملة عن أداء المبيعات والإيرادات</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="exportAllReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-download ml-2"></i>
                تصدير جميع التقارير
            </button>
            <button onclick="scheduleReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
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
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي المبيعات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_sales'] ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">عدد الفواتير</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_invoices'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">العملاء النشطين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_customers'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-percentage text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">معدل النمو</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['growth_rate'] ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Summary Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-blue-100 text-blue-600 ml-3">
                    <i class="fas fa-chart-bar text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير المبيعات الإجمالية</h3>
            </div>
            <p class="text-gray-600 mb-4">تقارير شاملة عن أداء المبيعات والإيرادات</p>
            <div class="space-y-2">
                <button onclick="generateReport('daily-sales')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-calendar-day ml-2"></i>
                    تقرير المبيعات اليومية
                </button>
                <button onclick="generateReport('monthly-sales')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-calendar-alt ml-2"></i>
                    تقرير المبيعات الشهرية
                </button>
                <button onclick="generateReport('yearly-sales')" class="block w-full text-right bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-calendar ml-2"></i>
                    تقرير المبيعات السنوية
                </button>
            </div>
        </div>

        <!-- Customer Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-green-100 text-green-600 ml-3">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير العملاء</h3>
            </div>
            <p class="text-gray-600 mb-4">تحليل أداء العملاء وسلوك الشراء</p>
            <div class="space-y-2">
                <button onclick="generateReport('top-customers')" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-star ml-2"></i>
                    أفضل العملاء
                </button>
                <button onclick="generateReport('customer-analysis')" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-chart-pie ml-2"></i>
                    تحليل العملاء
                </button>
                <button onclick="generateReport('customer-retention')" class="block w-full text-right bg-green-50 hover:bg-green-100 text-green-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-user-check ml-2"></i>
                    معدل الاحتفاظ بالعملاء
                </button>
            </div>
        </div>

        <!-- Product Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-yellow-100 text-yellow-600 ml-3">
                    <i class="fas fa-box text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير المنتجات</h3>
            </div>
            <p class="text-gray-600 mb-4">تحليل أداء المنتجات والمبيعات</p>
            <div class="space-y-2">
                <button onclick="generateReport('top-products')" class="block w-full text-right bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-trophy ml-2"></i>
                    أفضل المنتجات مبيعاً
                </button>
                <button onclick="generateReport('product-performance')" class="block w-full text-right bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-chart-line ml-2"></i>
                    أداء المنتجات
                </button>
                <button onclick="generateReport('category-analysis')" class="block w-full text-right bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-tags ml-2"></i>
                    تحليل الفئات
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Financial Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-purple-100 text-purple-600 ml-3">
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">التقارير المالية</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button onclick="generateReport('revenue-analysis')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-chart-area ml-2"></i>
                    تحليل الإيرادات
                </button>
                <button onclick="generateReport('profit-margins')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-percentage ml-2"></i>
                    هوامش الربح
                </button>
                <button onclick="generateReport('payment-analysis')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-credit-card ml-2"></i>
                    تحليل المدفوعات
                </button>
                <button onclick="generateReport('outstanding-invoices')" class="text-right bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-exclamation-triangle ml-2"></i>
                    الفواتير المعلقة
                </button>
            </div>
        </div>

        <!-- Performance Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600 ml-3">
                    <i class="fas fa-tachometer-alt text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">تقارير الأداء</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button onclick="generateReport('sales-trends')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-trending-up ml-2"></i>
                    اتجاهات المبيعات
                </button>
                <button onclick="generateReport('seasonal-analysis')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-calendar-week ml-2"></i>
                    التحليل الموسمي
                </button>
                <button onclick="generateReport('sales-team-performance')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-user-tie ml-2"></i>
                    أداء فريق المبيعات
                </button>
                <button onclick="generateReport('conversion-rates')" class="text-right bg-indigo-50 hover:bg-indigo-100 text-indigo-700 py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-exchange-alt ml-2"></i>
                    معدلات التحويل
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
            <h3 class="text-lg font-semibold text-gray-900">منشئ التقارير المخصصة</h3>
        </div>
        <p class="text-gray-600 mb-4">إنشاء تقارير مخصصة حسب احتياجاتك</p>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التقرير</label>
                <select id="reportType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">اختر نوع التقرير</option>
                    <option value="sales">المبيعات</option>
                    <option value="customers">العملاء</option>
                    <option value="products">المنتجات</option>
                    <option value="financial">مالي</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" id="startDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" id="endDate" value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تنسيق التصدير</label>
                <select id="exportFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
        </div>
        
        <div class="flex justify-end space-x-2 space-x-reverse">
            <button onclick="previewCustomReport()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-eye ml-2"></i>
                معاينة
            </button>
            <button onclick="generateCustomReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-file-alt ml-2"></i>
                إنشاء التقرير
            </button>
        </div>
    </div>
</div>

<script>
function generateReport(reportType) {
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
        alert(`تم إنشاء تقرير "${getReportName(reportType)}" بنجاح!\n\nسيتم تطوير ميزة التحميل قريباً.`);
    }, 2000);
}

function getReportName(reportType) {
    const reportNames = {
        'daily-sales': 'المبيعات اليومية',
        'monthly-sales': 'المبيعات الشهرية',
        'yearly-sales': 'المبيعات السنوية',
        'top-customers': 'أفضل العملاء',
        'customer-analysis': 'تحليل العملاء',
        'customer-retention': 'معدل الاحتفاظ بالعملاء',
        'top-products': 'أفضل المنتجات مبيعاً',
        'product-performance': 'أداء المنتجات',
        'category-analysis': 'تحليل الفئات',
        'revenue-analysis': 'تحليل الإيرادات',
        'profit-margins': 'هوامش الربح',
        'payment-analysis': 'تحليل المدفوعات',
        'outstanding-invoices': 'الفواتير المعلقة',
        'sales-trends': 'اتجاهات المبيعات',
        'seasonal-analysis': 'التحليل الموسمي',
        'sales-team-performance': 'أداء فريق المبيعات',
        'conversion-rates': 'معدلات التحويل'
    };
    return reportNames[reportType] || reportType;
}

function generateCustomReport() {
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const exportFormat = document.getElementById('exportFormat').value;
    
    if (!reportType) {
        alert('يرجى اختيار نوع التقرير');
        return;
    }
    
    // Simulate custom report generation
    alert(`سيتم إنشاء تقرير مخصص:\nالنوع: ${reportType}\nالفترة: ${startDate} إلى ${endDate}\nالتنسيق: ${exportFormat}\n\nسيتم تطوير هذه الميزة قريباً!`);
}

function previewCustomReport() {
    const reportType = document.getElementById('reportType').value;
    
    if (!reportType) {
        alert('يرجى اختيار نوع التقرير أولاً');
        return;
    }
    
    alert('سيتم تطوير ميزة المعاينة قريباً!');
}

function exportAllReports() {
    alert('سيتم تطوير ميزة تصدير جميع التقارير قريباً!\n\nستتمكن من تصدير مجموعة شاملة من التقارير بصيغ مختلفة.');
}

function scheduleReport() {
    alert('سيتم تطوير ميزة جدولة التقارير قريباً!\n\nستتمكن من جدولة التقارير للإرسال التلقائي يومياً أو أسبوعياً أو شهرياً.');
}
</script>
@endsection
