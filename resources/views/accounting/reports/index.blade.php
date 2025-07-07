@extends('layouts.app')

@section('title', 'التقارير المالية - MaxCon ERP')
@section('page-title', 'التقارير المالية')

@push('styles')
<style>
/* Financial Reports Page Hover Effects */
.report-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.report-card:hover .report-title {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.report-card:hover .report-icon {
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

.balance-item:hover {
    background: rgba(111, 66, 193, 0.05) !important;
    transition: background 0.3s ease;
}

.balance-item:hover .account-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.balance-item:hover .account-balance {
    color: #6f42c1 !important;
    font-weight: bold;
    transition: all 0.3s ease;
}

.transaction-item:hover {
    background: rgba(111, 66, 193, 0.05) !important;
    transition: background 0.3s ease;
}

.transaction-item:hover .transaction-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.chart-container:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}

.filter-btn:hover {
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
                <h1 class="text-2xl font-bold text-gray-900">التقارير المالية</h1>
                <p class="text-gray-600">تقارير شاملة عن الوضع المالي والأداء المحاسبي</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <input type="date" id="start_date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
                <span class="text-gray-500">إلى</span>
                <input type="date" id="end_date" value="{{ $endDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
                <button onclick="updateReports()" class="filter-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    تحديث
                </button>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="stats-card bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">إجمالي الأصول</p>
                        <p class="stats-number text-2xl font-bold text-green-900">
                            {{ number_format($stats['asset_total'], 0) }} د.ع
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
                        <p class="text-sm font-medium text-red-600">إجمالي الخصوم</p>
                        <p class="stats-number text-2xl font-bold text-red-900">
                            {{ number_format($stats['liability_total'], 0) }} د.ع
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
                            {{ number_format($stats['equity_total'], 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">الإيرادات</p>
                        <p class="stats-number text-2xl font-bold text-blue-900">
                            {{ number_format($stats['revenue_total'], 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-up text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">صافي الدخل</p>
                        <p class="stats-number text-2xl font-bold {{ $stats['net_income'] >= 0 ? 'text-green-900' : 'text-red-900' }}">
                            {{ number_format($stats['net_income'], 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Quick Reports -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">التقارير السريعة</h2>
            <div class="space-y-3">
                <a href="{{ route('accounting.reports.trial-balance') }}" class="report-card block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-purple-300">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="report-icon fas fa-balance-scale text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="report-title font-semibold text-gray-900">ميزان المراجعة</h3>
                            <p class="text-sm text-gray-600">عرض أرصدة جميع الحسابات</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('accounting.reports.income-statement') }}" class="report-card block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-purple-300">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="report-icon fas fa-chart-line text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="report-title font-semibold text-gray-900">قائمة الدخل</h3>
                            <p class="text-sm text-gray-600">الإيرادات والمصروفات</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('accounting.reports.balance-sheet') }}" class="report-card block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-purple-300">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="report-icon fas fa-file-alt text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="report-title font-semibold text-gray-900">الميزانية العمومية</h3>
                            <p class="text-sm text-gray-600">الأصول والخصوم وحقوق الملكية</p>
                        </div>
                    </div>
                </a>

                <a href="#" class="report-card block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-purple-300">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="report-icon fas fa-money-bill-wave text-orange-600"></i>
                        </div>
                        <div>
                            <h3 class="report-title font-semibold text-gray-900">تقرير التدفق النقدي</h3>
                            <p class="text-sm text-gray-600">حركة النقد والسيولة</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Account Balances Summary -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">ملخص أرصدة الحسابات</h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($accountBalances as $type => $accounts)
                <div class="border-b border-gray-200 pb-2">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        @switch($type)
                            @case('asset') الأصول @break
                            @case('liability') الخصوم @break
                            @case('equity') حقوق الملكية @break
                            @case('revenue') الإيرادات @break
                            @case('expense') المصروفات @break
                            @default {{ $type }}
                        @endswitch
                    </h4>
                    @foreach($accounts->take(5) as $account)
                    <div class="balance-item flex items-center justify-between py-1 px-2 rounded">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <span class="text-xs font-mono bg-gray-100 px-1 rounded">{{ $account->code }}</span>
                            <span class="account-name text-sm">{{ $account->name_ar ?? $account->name }}</span>
                        </div>
                        <span class="account-balance text-sm font-semibold {{ $account->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($account->current_balance, 0) }} د.ع
                        </span>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">آخر المعاملات</h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($recentTransactions as $transaction)
                <div class="transaction-item p-3 bg-gray-50 rounded border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="transaction-number font-semibold text-sm">{{ $transaction->transaction_number }}</span>
                        <span class="text-xs text-gray-500">{{ $transaction->transaction_date->format('Y/m/d') }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mb-1">{{ $transaction->description_ar ?? $transaction->description }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs px-2 py-1 rounded 
                            @switch($transaction->status)
                                @case('posted') bg-green-100 text-green-800 @break
                                @case('draft') bg-yellow-100 text-yellow-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            @switch($transaction->status)
                                @case('posted') مرحل @break
                                @case('draft') مسودة @break
                                @default {{ $transaction->status }}
                            @endswitch
                        </span>
                        <span class="font-semibold text-sm">{{ number_format($transaction->total_amount, 0) }} د.ع</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-file-alt text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">لا توجد معاملات حديثة</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Revenue vs Expenses Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">الإيرادات مقابل المصروفات</h2>
            <div class="chart-container">
                <canvas id="revenueExpenseChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Account Types Distribution -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">توزيع أنواع الحسابات</h2>
            <div class="chart-container">
                <canvas id="accountTypesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart Data Container -->
<div id="chart-data"
     data-revenue-total="{{ $stats['revenue_total'] ?? 0 }}"
     data-expense-total="{{ $stats['expense_total'] ?? 0 }}"
     data-net-income="{{ $stats['net_income'] ?? 0 }}"
     data-asset-total="{{ $stats['asset_total'] ?? 0 }}"
     data-liability-total="{{ $stats['liability_total'] ?? 0 }}"
     data-equity-total="{{ $stats['equity_total'] ?? 0 }}"
     style="display: none;">
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare chart data from HTML data attributes
const chartDataElement = document.getElementById('chart-data');
const chartData = {
    revenue_total: parseFloat(chartDataElement.dataset.revenueTotal),
    expense_total: parseFloat(chartDataElement.dataset.expenseTotal),
    net_income: parseFloat(chartDataElement.dataset.netIncome),
    asset_total: parseFloat(chartDataElement.dataset.assetTotal),
    liability_total: parseFloat(chartDataElement.dataset.liabilityTotal),
    equity_total: parseFloat(chartDataElement.dataset.equityTotal)
};

function updateReports() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const url = new URL(window.location);
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);
    window.location.href = url.toString();
}

// Revenue vs Expenses Chart
const revenueExpenseCtx = document.getElementById('revenueExpenseChart').getContext('2d');
new Chart(revenueExpenseCtx, {
    type: 'bar',
    data: {
        labels: ['الإيرادات', 'المصروفات', 'صافي الدخل'],
        datasets: [{
            data: [
                chartData.revenue_total,
                chartData.expense_total,
                chartData.net_income
            ],
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                chartData.net_income >= 0 ? 'rgba(59, 130, 246, 0.8)' : 'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
                'rgb(34, 197, 94)',
                'rgb(239, 68, 68)',
                chartData.net_income >= 0 ? 'rgb(59, 130, 246)' : 'rgb(239, 68, 68)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
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

// Account Types Distribution Chart
const accountTypesCtx = document.getElementById('accountTypesChart').getContext('2d');
new Chart(accountTypesCtx, {
    type: 'doughnut',
    data: {
        labels: ['الأصول', 'الخصوم', 'حقوق الملكية'],
        datasets: [{
            data: [
                chartData.asset_total,
                chartData.liability_total,
                chartData.equity_total
            ],
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(147, 51, 234, 0.8)'
            ],
            borderColor: [
                'rgb(34, 197, 94)',
                'rgb(239, 68, 68)',
                'rgb(147, 51, 234)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection
