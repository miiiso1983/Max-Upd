@extends('layouts.app')

@section('title', 'ذكاء الأعمال - MaxCon ERP')
@section('page-title', 'ذكاء الأعمال')

@push('styles')
<style>
/* Business Intelligence Page Hover Effects */
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.kpi-card:hover .kpi-value {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.kpi-card:hover .kpi-title {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.competitor-item:hover {
    background: rgba(111, 66, 193, 0.05) !important;
    transition: background 0.3s ease;
}

.competitor-item:hover .competitor-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.recommendation-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.recommendation-card:hover .recommendation-title {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.chart-container:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}

.progress-bar:hover {
    background: #6f42c1 !important;
    transition: background 0.3s ease;
}

.progress-bar-dynamic {
    width: var(--progress-width);
}

.trend-indicator:hover {
    transform: scale(1.2);
    transition: transform 0.3s ease;
}

.priority-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
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
                <h1 class="text-2xl font-bold text-gray-900">ذكاء الأعمال</h1>
                <p class="text-gray-600">تحليلات متقدمة ومؤشرات الأداء الرئيسية</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <button onclick="refreshData()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-sync-alt ml-2"></i>
                    تحديث البيانات
                </button>
                <div class="relative">
                    <button onclick="toggleExportMenu()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-download ml-2"></i>
                        تصدير التقرير
                        <i class="fas fa-chevron-down mr-2"></i>
                    </button>
                    <div id="export-menu" class="hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                        <div class="py-1">
                            <button onclick="exportReport('pdf')" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-pdf text-red-600 ml-2"></i>
                                تصدير PDF
                            </button>
                            <button onclick="exportReport('excel')" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-excel text-green-600 ml-2"></i>
                                تصدير Excel
                            </button>
                            <button onclick="exportReport('csv')" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-csv text-blue-600 ml-2"></i>
                                تصدير CSV
                            </button>
                            <button onclick="exportReport('html')" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-code text-orange-600 ml-2"></i>
                                تصدير HTML
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @foreach($kpis as $key => $kpi)
        <div class="kpi-card bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="kpi-title text-lg font-semibold text-gray-900">
                        @switch($key)
                            @case('revenue_growth') نمو الإيرادات @break
                            @case('customer_retention') الاحتفاظ بالعملاء @break
                            @case('market_penetration') اختراق السوق @break
                            @case('operational_efficiency') الكفاءة التشغيلية @break
                            @default {{ $key }}
                        @endswitch
                    </h3>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="kpi-value text-3xl font-bold text-blue-600">{{ $kpi['value'] }}%</span>
                        <span class="trend-indicator">
                            @if($kpi['trend'] === 'up')
                                <i class="fas fa-arrow-up text-green-500"></i>
                            @elseif($kpi['trend'] === 'down')
                                <i class="fas fa-arrow-down text-red-500"></i>
                            @else
                                <i class="fas fa-minus text-gray-500"></i>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    @switch($key)
                        @case('revenue_growth')
                            <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                        @break
                        @case('customer_retention')
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        @break
                        @case('market_penetration')
                            <i class="fas fa-bullseye text-blue-600 text-2xl"></i>
                        @break
                        @case('operational_efficiency')
                            <i class="fas fa-cogs text-blue-600 text-2xl"></i>
                        @break
                    @endswitch
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-2">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                    <span>التقدم نحو الهدف</span>
                    <span>{{ $kpi['target'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="progress-bar progress-bar-dynamic bg-blue-600 h-2 rounded-full" style="--progress-width: {{ ($kpi['value'] / $kpi['target']) * 100 }}%"></div>
                </div>
            </div>
            
            <div class="text-sm text-gray-600">
                الهدف: {{ $kpi['target'] }}%
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts and Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Competitor Analysis -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">تحليل المنافسين</h2>
            <div class="space-y-3">
                @foreach($competitorAnalysis as $competitor)
                <div class="competitor-item flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            @if($competitor['competitor'] === 'MaxCon')
                                <i class="fas fa-crown text-yellow-600"></i>
                            @else
                                <i class="fas fa-building text-blue-600"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="competitor-name font-semibold {{ $competitor['competitor'] === 'MaxCon' ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $competitor['competitor'] }}
                            </h4>
                            <p class="text-sm text-gray-600">حصة السوق: {{ $competitor['market_share'] }}%</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold {{ $competitor['growth_rate'] > 10 ? 'text-green-600' : 'text-gray-900' }}">
                            {{ $competitor['growth_rate'] }}%
                        </div>
                        <div class="text-sm text-gray-600">معدل النمو</div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Market Share Chart -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">توزيع حصص السوق</h3>
                <div class="chart-container">
                    <canvas id="marketShareChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">توصيات الذكاء الاصطناعي</h2>
            <div class="space-y-4">
                @foreach($recommendations as $recommendation)
                <div class="recommendation-card p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 space-x-reverse mb-2">
                                <h4 class="recommendation-title font-semibold text-gray-900">{{ $recommendation['title'] }}</h4>
                                <span class="priority-badge px-2 py-1 rounded text-xs font-medium
                                    @if($recommendation['priority'] === 'high') bg-red-100 text-red-800
                                    @elseif($recommendation['priority'] === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif
                                ">
                                    @if($recommendation['priority'] === 'high') أولوية عالية
                                    @elseif($recommendation['priority'] === 'medium') أولوية متوسطة
                                    @else أولوية منخفضة
                                    @endif
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $recommendation['description'] }}</p>
                            <div class="text-sm font-semibold text-green-600">
                                التأثير المتوقع: {{ $recommendation['estimated_impact'] }}
                            </div>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            @if($recommendation['category'] === 'المبيعات')
                                <i class="fas fa-chart-line text-purple-600"></i>
                            @else
                                <i class="fas fa-cogs text-purple-600"></i>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ $recommendation['category'] }}</span>
                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            تطبيق التوصية
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Performance Trends -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h2 class="text-xl font-bold text-gray-900 mb-4">اتجاهات الأداء</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Trend -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">اتجاه الإيرادات</h3>
                <div class="chart-container">
                    <canvas id="revenueTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- KPI Trends -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">اتجاهات المؤشرات</h3>
                <div class="chart-container">
                    <canvas id="kpiTrendsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Data Container -->
<div id="chart-data"
     data-kpis="{{ json_encode($kpis) }}"
     data-competitor-analysis="{{ json_encode($competitorAnalysis) }}"
     style="display: none;">
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare chart data from HTML data attributes
const chartDataElement = document.getElementById('chart-data');
const chartData = {
    kpis: JSON.parse(chartDataElement.dataset.kpis),
    competitorAnalysis: JSON.parse(chartDataElement.dataset.competitorAnalysis)
};

// Market Share Chart
const marketShareCtx = document.getElementById('marketShareChart').getContext('2d');
new Chart(marketShareCtx, {
    type: 'doughnut',
    data: {
        labels: chartData.competitorAnalysis.map(competitor => competitor.competitor),
        datasets: [{
            data: chartData.competitorAnalysis.map(competitor => competitor.market_share),
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
                'rgb(59, 130, 246)',
                'rgb(16, 185, 129)',
                'rgb(245, 158, 11)',
                'rgb(239, 68, 68)'
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

// Revenue Trend Chart
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
new Chart(revenueTrendCtx, {
    type: 'line',
    data: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        datasets: [{
            label: 'الإيرادات الفعلية',
            data: [85, 92, 101, 98, 112, 125],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }, {
            label: 'الإيرادات المتوقعة',
            data: [88, 95, 103, 101, 115, 128],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderDash: [5, 5],
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + ' مليون د.ع';
                    }
                }
            }
        }
    }
});

// KPI Trends Chart
const kpiTrendsCtx = document.getElementById('kpiTrendsChart').getContext('2d');
new Chart(kpiTrendsCtx, {
    type: 'radar',
    data: {
        labels: ['نمو الإيرادات', 'الاحتفاظ بالعملاء', 'اختراق السوق', 'الكفاءة التشغيلية'],
        datasets: [{
            label: 'الأداء الحالي',
            data: Object.values(chartData.kpis).map(kpi => kpi.value),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            pointBackgroundColor: 'rgb(59, 130, 246)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(59, 130, 246)'
        }, {
            label: 'الهدف',
            data: Object.values(chartData.kpis).map(kpi => kpi.target),
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            pointBackgroundColor: 'rgb(16, 185, 129)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(16, 185, 129)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            r: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Export and utility functions
function toggleExportMenu() {
    const menu = document.getElementById('export-menu');
    menu.classList.toggle('hidden');
}

// Close export menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('export-menu');
    const button = event.target.closest('button');

    if (!button || !button.onclick || button.onclick.toString().indexOf('toggleExportMenu') === -1) {
        menu.classList.add('hidden');
    }
});

function refreshData() {
    // Show loading indicator
    showLoadingIndicator();

    // Reload the page to refresh data
    window.location.reload();
}

function exportReport(format) {
    // Hide export menu
    document.getElementById('export-menu').classList.add('hidden');

    // Show loading indicator
    showLoadingIndicator('جاري تصدير التقرير...');

    // Prepare export data
    const exportData = {
        format: format,
        data: {
            kpis: chartData.kpis,
            competitorAnalysis: chartData.competitorAnalysis,
            period: {
                start_date: new Date().toISOString().split('T')[0],
                end_date: new Date().toISOString().split('T')[0]
            }
        },
        metadata: {
            report_name: 'تقرير ذكاء الأعمال',
            generated_at: new Date().toISOString(),
            generated_by: '{{ auth()->user()->name ?? "المستخدم" }}'
        }
    };

    // Make export request
    fetch('/api/business-intelligence/export-report', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(exportData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('فشل في تصدير التقرير');
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;

        // Set filename based on format
        const timestamp = new Date().toISOString().split('T')[0];
        let filename = `business-intelligence-report-${timestamp}`;

        switch(format) {
            case 'pdf':
                filename += '.pdf';
                break;
            case 'excel':
                filename += '.xlsx';
                break;
            case 'csv':
                filename += '.csv';
                break;
            case 'html':
                filename += '.html';
                break;
            default:
                filename += '.txt';
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoadingIndicator();
        showSuccessMessage('تم تصدير التقرير بنجاح');
    })
    .catch(error => {
        console.error('Export error:', error);
        hideLoadingIndicator();
        showErrorMessage('فشل في تصدير التقرير: ' + error.message);
    });
}

function showLoadingIndicator(message = 'جاري التحميل...') {
    // Remove existing indicator
    const existing = document.getElementById('loading-indicator');
    if (existing) {
        existing.remove();
    }

    // Create loading indicator
    const indicator = document.createElement('div');
    indicator.id = 'loading-indicator';
    indicator.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    indicator.innerHTML = `
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3 space-x-reverse">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">${message}</span>
        </div>
    `;

    document.body.appendChild(indicator);
}

function hideLoadingIndicator() {
    const indicator = document.getElementById('loading-indicator');
    if (indicator) {
        indicator.remove();
    }
}

function showSuccessMessage(message) {
    showNotification(message, 'success');
}

function showErrorMessage(message) {
    showNotification(message, 'error');
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.notification');
    existing.forEach(el => el.remove());

    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 left-4 right-4 md:right-auto md:w-96 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;

    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-info-circle'
                } ml-2"></i>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection
