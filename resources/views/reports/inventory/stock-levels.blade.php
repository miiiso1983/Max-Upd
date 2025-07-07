@extends('layouts.app')

@section('title', 'تقرير مستويات المخزون - MaxCon ERP')
@section('page-title', 'تقرير مستويات المخزون')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تقرير مستويات المخزون</h1>
            <p class="text-gray-600">عرض شامل لحالة المخزون الحالية</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="exportReport('pdf')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-pdf ml-2"></i>
                تصدير PDF
            </button>
            <button onclick="exportReport('excel')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-excel ml-2"></i>
                تصدير Excel
            </button>
            <button onclick="printReport()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('reports.inventory.stock-levels') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المستودع</label>
                <select name="warehouse_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">جميع المستودعات</option>
                    <!-- Warehouses will be loaded dynamically -->
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">حالة المخزون</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">جميع الحالات</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>مخزون منخفض</option>
                    <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>نفد المخزون</option>
                    <option value="high" {{ request('status') == 'high' ? 'selected' : '' }}>مخزون عالي</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-search ml-1"></i>
                    تحديث التقرير
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
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
                    <p class="text-sm font-medium text-gray-600">مخزون منخفض</p>
                    <p class="text-2xl font-bold text-orange-600" id="low-stock">0</p>
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
                    <p class="text-2xl font-bold text-red-600" id="out-of-stock">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">قيمة المخزون</p>
                    <p class="text-2xl font-bold text-green-600" id="stock-value">0</p>
                    <p class="text-xs text-gray-500">د.ع</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Status Chart -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">توزيع حالة المخزون</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="h-64">
                <canvas id="stockStatusChart"></canvas>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">مخزون طبيعي</span>
                    </div>
                    <span class="text-sm font-bold text-green-600" id="normal-stock-count">0</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-orange-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">مخزون منخفض</span>
                    </div>
                    <span class="text-sm font-bold text-orange-600" id="low-stock-count">0</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">نفد المخزون</span>
                    </div>
                    <span class="text-sm font-bold text-red-600" id="out-stock-count">0</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">مخزون عالي</span>
                    </div>
                    <span class="text-sm font-bold text-blue-600" id="high-stock-count">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Levels Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">تفاصيل مستويات المخزون</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستودع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزون الحالي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحد الأدنى</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحد الأقصى</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">القيمة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="stock-levels-table">
                    <!-- Stock levels will be loaded here -->
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                                <p class="text-lg">جاري تحميل البيانات...</p>
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

    <!-- Alerts Section -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">تنبيهات المخزون</h3>
        <div class="space-y-3" id="stock-alerts">
            <!-- Alerts will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadStockLevels();
    loadStockStats();
});

function loadStockLevels() {
    const params = new URLSearchParams(window.location.search);
    const apiUrl = '/api/reports/inventory/stock-levels?' + params.toString();
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            updateStockLevelsTable(data.stock_levels);
            updatePagination(data.stock_levels);
        })
        .catch(error => {
            console.error('Error loading stock levels:', error);
            document.getElementById('stock-levels-table').innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="text-red-500">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                            <p class="text-lg">حدث خطأ في تحميل البيانات</p>
                        </div>
                    </td>
                </tr>
            `;
        });
}

function loadStockStats() {
    fetch('/api/inventory/dashboard')
        .then(response => response.json())
        .then(data => {
            updateStats(data.stats);
            initializeChart(data.inventory_chart);
        })
        .catch(error => console.error('Error loading stats:', error));
}

function updateStats(stats) {
    document.getElementById('total-products').textContent = stats.total_products || 0;
    document.getElementById('low-stock').textContent = stats.low_stock_products || 0;
    document.getElementById('out-of-stock').textContent = stats.out_of_stock_products || 0;
    document.getElementById('stock-value').textContent = MaxCon.formatNumber(stats.total_stock_value || 0);
}

function updateStockLevelsTable(stockLevels) {
    const tbody = document.getElementById('stock-levels-table');
    
    if (stockLevels.data && stockLevels.data.length > 0) {
        tbody.innerHTML = stockLevels.data.map(stock => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                            <i class="fas fa-pills text-blue-600"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">${stock.product.name}</div>
                            <div class="text-sm text-gray-500">${stock.product.sku}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${stock.warehouse ? stock.warehouse.name : 'غير محدد'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium ${getStockLevelClass(stock.current_stock, stock.minimum_stock, stock.maximum_stock)}">
                        ${stock.current_stock || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${stock.minimum_stock || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${stock.maximum_stock || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${MaxCon.formatCurrency((stock.current_stock || 0) * (stock.product.cost_price || 0))}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${getStockStatusBadge(stock.current_stock, stock.minimum_stock, stock.maximum_stock)}
                </td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <i class="fas fa-boxes text-4xl mb-4"></i>
                        <p class="text-lg">لا توجد بيانات مخزون</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

function getStockLevelClass(current, minimum, maximum) {
    if (current <= 0) return 'text-red-600';
    if (current <= minimum) return 'text-orange-600';
    if (current >= maximum) return 'text-blue-600';
    return 'text-green-600';
}

function getStockStatusBadge(current, minimum, maximum) {
    if (current <= 0) {
        return '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">نفد المخزون</span>';
    } else if (current <= minimum) {
        return '<span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">مخزون منخفض</span>';
    } else if (current >= maximum) {
        return '<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">مخزون عالي</span>';
    } else {
        return '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">مخزون طبيعي</span>';
    }
}

function initializeChart(chartData) {
    const ctx = document.getElementById('stockStatusChart').getContext('2d');
    
    // Update counts
    document.getElementById('normal-stock-count').textContent = chartData.normal_stock || 0;
    document.getElementById('low-stock-count').textContent = chartData.low_stock || 0;
    document.getElementById('out-stock-count').textContent = chartData.out_of_stock || 0;
    document.getElementById('high-stock-count').textContent = chartData.high_stock || 0;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['مخزون طبيعي', 'مخزون منخفض', 'نفد المخزون', 'مخزون عالي'],
            datasets: [{
                data: [
                    chartData.normal_stock || 0,
                    chartData.low_stock || 0,
                    chartData.out_of_stock || 0,
                    chartData.high_stock || 0
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function updatePagination(stockLevels) {
    // Implement pagination UI update
    const container = document.getElementById('pagination-container');
    // This would be implemented based on the pagination structure
}

function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('format', format);
    
    const url = `/reports/inventory/stock-levels/export?${params.toString()}`;
    window.open(url, '_blank');
}

function printReport() {
    window.print();
}
</script>
@endpush
