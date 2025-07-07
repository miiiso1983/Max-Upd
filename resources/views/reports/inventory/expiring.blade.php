@extends('layouts.app')

@section('title', 'تقرير المنتجات منتهية الصلاحية - MaxCon ERP')
@section('page-title', 'تقرير المنتجات منتهية الصلاحية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تقرير المنتجات منتهية الصلاحية</h1>
            <p class="text-gray-600">تقرير شامل عن المنتجات التي تنتهي صلاحيتها خلال {{ $days ?? 30 }} يوم</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="printReport()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>
            <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download ml-2"></i>
                تصدير Excel
            </button>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">فلاتر التقرير</h3>
        <form method="GET" action="{{ route('reports.inventory.expiring') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Days Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">عدد الأيام</label>
                <select name="days" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="7" {{ request('days') == '7' ? 'selected' : '' }}>7 أيام</option>
                    <option value="15" {{ request('days') == '15' ? 'selected' : '' }}>15 يوم</option>
                    <option value="30" {{ request('days', '30') == '30' ? 'selected' : '' }}>30 يوم</option>
                    <option value="60" {{ request('days') == '60' ? 'selected' : '' }}>60 يوم</option>
                    <option value="90" {{ request('days') == '90' ? 'selected' : '' }}>90 يوم</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الفئة</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الفئات</option>
                    @foreach($stats['categories'] ?? [] as $category => $count)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ $category }} ({{ $count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end space-x-2 space-x-reverse">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search ml-1"></i>
                    تطبيق الفلاتر
                </button>
                <a href="{{ route('reports.inventory.expiring') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-undo ml-1"></i>
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <!-- Report Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المنتجات</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['total_products'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-boxes text-orange-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي الكمية</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_quantity'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي القيمة</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_value'] ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tags text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">عدد الفئات</p>
                    <p class="text-2xl font-bold text-blue-600">{{ count($stats['categories'] ?? []) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">تفاصيل المنتجات منتهية الصلاحية</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفئة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة المصنعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المنتهية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الأيام المتبقية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قيمة المخزون</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expiringProducts as $index => $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($product->image)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-box text-gray-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="mr-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->sku ?? 'لا يوجد' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->category->name ?? 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->manufacturer->name ?? 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($product->expiring_stock ?? 0) }} {{ $product->unit ?? 'قطعة' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $expiryDate = $product->stockEntries->first()?->expiry_date;
                            @endphp
                            @if($expiryDate)
                                {{ \Carbon\Carbon::parse($expiryDate)->format('Y-m-d') }}
                            @else
                                غير محدد
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($expiryDate)
                                @php
                                    $daysRemaining = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($expiryDate), false);
                                @endphp
                                @if($daysRemaining < 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        منتهي الصلاحية
                                    </span>
                                @elseif($daysRemaining <= 7)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $daysRemaining }} يوم
                                    </span>
                                @elseif($daysRemaining <= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $daysRemaining }} يوم
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $daysRemaining }} يوم
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-500">غير محدد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($product->stock_value ?? 0, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    نشط
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    غير نشط
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد منتجات منتهية الصلاحية</h3>
                                <p class="text-gray-500">جميع المنتجات في حالة جيدة ولا تحتاج إجراءات فورية</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                تم إنشاء التقرير في: {{ now()->format('Y-m-d H:i:s') }}
            </div>
            <div class="text-sm text-gray-500">
                إجمالي السجلات: {{ count($expiringProducts) }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Print report
function printReport() {
    window.print();
}

// Export report to Excel
function exportReport() {
    const params = new URLSearchParams(window.location.search);
    const exportUrl = '/api/reports/inventory/expiring/export?' + params.toString();
    window.location.href = exportUrl;
}

// Print styles
const printStyles = `
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
        .bg-white {
            background: white !important;
        }
        .text-gray-900 {
            color: black !important;
        }
        .text-gray-600 {
            color: #666 !important;
        }
        .border {
            border: 1px solid #ccc !important;
        }
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f5f5f5 !important;
            font-weight: bold;
        }
    }
`;

// Add print styles to document
const styleSheet = document.createElement('style');
styleSheet.type = 'text/css';
styleSheet.innerText = printStyles;
document.head.appendChild(styleSheet);

// Add print class to main content
document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.querySelector('.space-y-6');
    if (mainContent) {
        mainContent.classList.add('print-area');
    }

    // Add no-print class to buttons
    const buttons = document.querySelectorAll('button, .no-print');
    buttons.forEach(button => {
        button.classList.add('no-print');
    });
});
</script>
@endpush
