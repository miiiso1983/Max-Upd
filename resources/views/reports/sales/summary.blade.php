@extends('layouts.app')

@section('title', 'تقرير ملخص المبيعات - MaxCon ERP')
@section('page-title', 'تقرير ملخص المبيعات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تقرير ملخص المبيعات</h1>
            <p class="text-gray-600">من {{ $startDate->format('Y-m-d') }} إلى {{ $endDate->format('Y-m-d') }}</p>
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

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('reports.sales.summary') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md ml-2">
                    <i class="fas fa-search ml-1"></i>
                    تحديث التقرير
                </button>
                <div class="flex space-x-1 space-x-reverse">
                    <button type="button" onclick="setDateRange('today')" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">اليوم</button>
                    <button type="button" onclick="setDateRange('week')" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">أسبوع</button>
                    <button type="button" onclick="setDateRange('month')" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">شهر</button>
                    <button type="button" onclick="setDateRange('year')" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">سنة</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المبيعات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['totals']['total_sales'], 0) }}</p>
                    <p class="text-xs text-gray-500">د.ع</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">عدد الطلبات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['totals']['total_orders']) }}</p>
                    <p class="text-xs text-gray-500">طلب</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">متوسط قيمة الطلب</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['totals']['average_order_value'], 0) }}</p>
                    <p class="text-xs text-gray-500">د.ع</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">عدد العملاء</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['totals']['total_customers']) }}</p>
                    <p class="text-xs text-gray-500">عميل</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Sales Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المبيعات اليومية</h3>
            <div class="h-64" id="daily-sales-chart">
                <canvas id="dailySalesCanvas"></canvas>
            </div>
        </div>

        <!-- Sales by Payment Method -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المبيعات حسب طريقة الدفع</h3>
            <div class="h-64" id="payment-method-chart">
                <canvas id="paymentMethodCanvas"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales by Status -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المبيعات حسب الحالة</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">العدد</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($summary['by_status'] as $status)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ getStatusClass($status->status) }}">
                                    {{ getStatusText($status->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($status->count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($status->total_amount, 0) }} د.ع
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales by Payment Method -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المبيعات حسب طريقة الدفع</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">طريقة الدفع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">العدد</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($summary['by_payment_method'] as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ getPaymentMethodText($payment->payment_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($payment->count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($payment->total_amount, 0) }} د.ع
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Section (Hidden) -->
    <div id="print-section" class="hidden print:block">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">تقرير ملخص المبيعات</h1>
            <p class="text-gray-600">من {{ $startDate->format('Y-m-d') }} إلى {{ $endDate->format('Y-m-d') }}</p>
            <p class="text-sm text-gray-500">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
        
        <!-- Print content will be populated by JavaScript -->
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Daily Sales Chart
    const dailySalesData = @json($summary['daily_sales']);
    const dailyCtx = document.getElementById('dailySalesCanvas').getContext('2d');
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailySalesData.map(item => item.date),
            datasets: [{
                label: 'المبيعات اليومية',
                data: dailySalesData.map(item => item.total_sales),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('ar-IQ').format(value) + ' د.ع';
                        }
                    }
                }
            }
        }
    });

    // Payment Method Chart
    const paymentMethodData = @json($summary['by_payment_method']);
    const paymentCtx = document.getElementById('paymentMethodCanvas').getContext('2d');
    
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: paymentMethodData.map(item => getPaymentMethodText(item.payment_method)),
            datasets: [{
                data: paymentMethodData.map(item => item.total_amount),
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function setDateRange(range) {
    const today = new Date();
    let startDate, endDate = today;
    
    switch(range) {
        case 'today':
            startDate = today;
            break;
        case 'week':
            startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            break;
        case 'year':
            startDate = new Date(today.getFullYear(), 0, 1);
            break;
    }
    
    document.querySelector('input[name="start_date"]').value = startDate.toISOString().split('T')[0];
    document.querySelector('input[name="end_date"]').value = endDate.toISOString().split('T')[0];
}

function exportReport(format) {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    const url = `/reports/sales/summary/export?format=${format}&start_date=${startDate}&end_date=${endDate}`;
    window.open(url, '_blank');
}

function printReport() {
    window.print();
}

function getPaymentMethodText(method) {
    const methods = {
        'cash': 'نقداً',
        'credit_card': 'بطاقة ائتمان',
        'bank_transfer': 'تحويل بنكي',
        'check': 'شيك',
        'installment': 'تقسيط'
    };
    return methods[method] || method;
}
</script>
@endpush

@php
function getStatusClass($status) {
    switch($status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        case 'confirmed': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText($status) {
    switch($status) {
        case 'completed': return 'مكتمل';
        case 'pending': return 'معلق';
        case 'cancelled': return 'ملغي';
        case 'confirmed': return 'مؤكد';
        default: return $status;
    }
}

function getPaymentMethodText($method) {
    switch($method) {
        case 'cash': return 'نقداً';
        case 'credit_card': return 'بطاقة ائتمان';
        case 'bank_transfer': return 'تحويل بنكي';
        case 'check': return 'شيك';
        case 'installment': return 'تقسيط';
        default: return $method;
    }
}
@endphp
