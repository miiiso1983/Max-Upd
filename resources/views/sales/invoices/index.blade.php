@extends('layouts.app')

@section('title', 'إدارة الفواتير - MaxCon ERP')
@section('page-title', 'إدارة الفواتير')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">إدارة الفواتير</h1>
                <p class="text-gray-600">عرض وإدارة جميع فواتير المبيعات</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    فاتورة جديدة
                </a>
                <div class="relative inline-block text-left">
                    <button onclick="toggleExportMenu()" class="btn btn-secondary" id="exportButton">
                        <i class="fas fa-download ml-2"></i>
                        تصدير
                        <i class="fas fa-chevron-down mr-2"></i>
                    </button>
                    <div id="exportMenu" class="hidden absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <button onclick="exportInvoices('excel')" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-excel text-green-600 ml-2"></i>
                                تصدير Excel
                            </button>
                            <button onclick="exportInvoices('csv')" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-csv text-blue-600 ml-2"></i>
                                تصدير CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البحث</label>
                <input type="text" id="search" placeholder="رقم الفاتورة أو اسم العميل" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحالات</option>
                    @foreach($filters['statuses_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">العميل</label>
                <select id="customer-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع العملاء</option>
                    @foreach($filters['customers'] as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name_ar ?? $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="btn btn-primary w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center ml-4">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي الفواتير</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $invoices->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center ml-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">الفواتير المدفوعة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $invoices->where('status', 'paid')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center ml-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">الفواتير المرسلة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $invoices->where('status', 'sent')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center ml-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">الفواتير المتأخرة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $invoices->where('status', 'overdue')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            رقم الفاتورة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            العميل
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            التاريخ
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            تاريخ الاستحقاق
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المبلغ الإجمالي
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المبلغ المدفوع
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الرصيد المستحق
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الحالة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الإجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                            <div class="text-sm text-gray-500">{{ $invoice->type }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $invoice->customer->name_ar ?? $invoice->customer->name ?? 'غير محدد' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $invoice->customer->type ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->invoice_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($invoice->total_amount, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($invoice->paid_amount, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($invoice->balance_due, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : 
                                   ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 
                                   ($invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))) }}">
                                {{ $filters['statuses_ar'][$invoice->status] ?? $invoice->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('sales.invoices.show', $invoice) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.invoices.edit', $invoice) }}" 
                                   class="text-green-600 hover:text-green-900" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('sales.invoices.pdf', $invoice) }}" 
                                   class="text-purple-600 hover:text-purple-900" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button onclick="sendInvoice({{ $invoice->id }})" 
                                        class="text-orange-600 hover:text-orange-900" title="إرسال">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                @if($invoice->status !== 'paid')
                                <button onclick="recordPayment({{ $invoice->id }})" 
                                        class="text-green-600 hover:text-green-900" title="تسجيل دفعة">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                                @endif
                                <button onclick="deleteInvoice({{ $invoice->id }})" 
                                        class="text-red-600 hover:text-red-900" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-file-invoice text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد فواتير</p>
                                <p class="text-sm">ابدأ بإنشاء فاتورة جديدة</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($invoices->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('status-filter').value;
    const customer = document.getElementById('customer-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (customer) params.append('customer_id', customer);
    
    window.location.href = `{{ route('sales.invoices.index') }}?${params.toString()}`;
}

function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('hidden');

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const button = document.getElementById('exportButton');
        if (!button.contains(event.target) && !menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
}

function exportInvoices(format = 'csv') {
    // Hide export menu
    document.getElementById('exportMenu').classList.add('hidden');

    // Get current filters
    const status = document.querySelector('select[name="status"]')?.value || '';
    const customerId = document.querySelector('select[name="customer_id"]')?.value || '';
    const dateFrom = document.querySelector('input[name="date_from"]')?.value || '';
    const dateTo = document.querySelector('input[name="date_to"]')?.value || '';

    // Build export URL with filters
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (customerId) params.append('customer_id', customerId);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    params.append('format', format);

    const exportUrl = `/sales/invoices/export?${params.toString()}`;

    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function sendInvoice(invoiceId) {
    // Show send invoice modal
    showSendInvoiceModal(invoiceId);
}

function showSendInvoiceModal(invoiceId) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center mb-4">إرسال الفاتورة</h3>
                <form id="sendInvoiceForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="customerEmail" name="email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               placeholder="البريد الإلكتروني للعميل">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">رسالة إضافية (اختيارية)</label>
                        <textarea id="customMessage" name="message" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="رسالة مخصصة للعميل..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse">
                        <button type="button" onclick="closeSendModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            إلغاء
                        </button>
                        <button type="button" onclick="submitSendInvoice(${invoiceId})"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-paper-plane ml-2"></i>
                            إرسال
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Focus on email input
    setTimeout(() => {
        document.getElementById('customerEmail').focus();
    }, 100);
}

function closeSendModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
    if (modal) {
        modal.remove();
    }
}

function submitSendInvoice(invoiceId) {
    const email = document.getElementById('customerEmail').value;
    const message = document.getElementById('customMessage').value;

    if (!email) {
        alert('يرجى إدخال البريد الإلكتروني');
        return;
    }

    // Show loading
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الإرسال...';
    submitBtn.disabled = true;

    // Send AJAX request
    fetch(`/sales/invoices/${invoiceId}/send`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            email: email,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert('تم إرسال الفاتورة بنجاح!');
            closeSendModal();
            // Refresh the page to update status
            location.reload();
        } else {
            throw new Error(data.error || 'حدث خطأ أثناء الإرسال');
        }
    })
    .catch(error => {
        alert('خطأ: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function recordPayment(invoiceId) {
    // Redirect to payment recording page for this invoice
    window.location.href = `/sales/payments/create/${invoiceId}`;
}

function deleteInvoice(invoiceId) {
    if (confirm('هل أنت متأكد من حذف هذه الفاتورة؟')) {
        // Implement delete functionality
        fetch(`/sales/invoices/${invoiceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء الحذف');
            }
        });
    }
}

// Auto-apply filters on Enter key
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endsection
