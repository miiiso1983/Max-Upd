@extends('layouts.app')

@section('title', 'المدفوعات - MaxCon ERP')
@section('page-title', 'المدفوعات')

@push('styles')
<style>
/* Payments Page Hover Effects */
.payment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.payment-card:hover .payment-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.payment-card:hover .customer-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.payment-card:hover .amount {
    color: #6f42c1 !important;
    font-weight: bold;
    transition: all 0.3s ease;
}

/* Searchable dropdown in filters */
.filter-dropdown .searchable-dropdown {
    width: 100%;
}

.filter-dropdown .dropdown-button {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: white;
}

.filter-dropdown .dropdown-button:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}

.status-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.method-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.filter-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
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
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">المدفوعات</h1>
                <p class="text-gray-600">إدارة ومتابعة المدفوعات والتحصيلات</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('sales.payments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    دفعة جديدة
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="stats-card bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">إجمالي المدفوعات</p>
                        <p class="stats-number text-2xl font-bold text-green-900">
                            {{ number_format($allPayments->sum('amount'), 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">المدفوعات المكتملة</p>
                        <p class="stats-number text-2xl font-bold text-blue-900">
                            {{ $allPayments->where('status', 'completed')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600">المدفوعات المعلقة</p>
                        <p class="stats-number text-2xl font-bold text-yellow-900">
                            {{ $allPayments->where('status', 'pending')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">متوسط الدفعة</p>
                        <p class="stats-number text-2xl font-bold text-purple-900">
                            {{ $allPayments->count() > 0 ? number_format($allPayments->avg('amount'), 0) : 0 }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="رقم الدفعة أو اسم العميل..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الحالات</option>
                    @foreach($filters['statuses_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع</label>
                <select id="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الطرق</option>
                    @foreach($filters['payment_methods_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">العميل</label>
                <x-searchable-dropdown
                    name="customer"
                    placeholder="جميع العملاء"
                    search-placeholder="ابحث عن عميل..."
                    :options="collect($filters['customers'])->map(function($customer) {
                        return [
                            'value' => $customer->id,
                            'text' => $customer->name_ar ?: $customer->name
                        ];
                    })->prepend(['value' => '', 'text' => 'جميع العملاء'])->toArray()"
                    value="{{ request('customer_id', '') }}"
                    class="filter-dropdown"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الفترة</label>
                <select id="date_range" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الفترات</option>
                    <option value="today">اليوم</option>
                    <option value="week">هذا الأسبوع</option>
                    <option value="month">هذا الشهر</option>
                    <option value="quarter">هذا الربع</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Payments List -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                @forelse($payments as $payment)
                <div class="payment-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 space-x-reverse mb-2">
                                <h3 class="payment-number text-lg font-semibold text-gray-900">
                                    {{ $payment->payment_number ?? 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                                </h3>
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($payment->status)
                                        @case('pending') bg-yellow-100 text-yellow-800 @break
                                        @case('completed') bg-green-100 text-green-800 @break
                                        @case('failed') bg-red-100 text-red-800 @break
                                        @case('cancelled') bg-gray-100 text-gray-800 @break
                                        @default bg-blue-100 text-blue-800
                                    @endswitch
                                ">
                                    {{ $filters['statuses_ar'][$payment->status] ?? $payment->status }}
                                </span>
                                <span class="method-badge px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $filters['payment_methods_ar'][$payment->payment_method] ?? $payment->payment_method }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">العميل:</span>
                                    <span class="customer-name">{{ $payment->customer->name_ar ?? $payment->customer->name ?? 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">الفاتورة:</span>
                                    <span>{{ $payment->invoice->invoice_number ?? 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">تاريخ الدفع:</span>
                                    <span>{{ $payment->payment_date ? $payment->payment_date->format('Y/m/d') : 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">المبلغ:</span>
                                    <span class="amount font-semibold text-green-600">{{ number_format($payment->amount, 0) }} د.ع</span>
                                </div>
                            </div>

                            @if($payment->reference_number)
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="font-medium">رقم المرجع:</span>
                                <span>{{ $payment->reference_number }}</span>
                            </div>
                            @endif

                            @if($payment->notes)
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="font-medium">ملاحظات:</span>
                                <span>{{ Str::limit($payment->notes, 100) }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 space-x-reverse">
                            <a href="{{ route('sales.payments.show', $payment) }}" 
                               class="action-btn bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600">
                                <i class="fas fa-eye"></i>
                                عرض
                            </a>
                            
                            @if($payment->status === 'pending')
                            <a href="{{ route('sales.payments.edit', $payment) }}" 
                               class="action-btn bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                            @endif

                            <div class="relative">
                                <button class="action-btn bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600" 
                                        onclick="toggleDropdown('dropdown-{{ $payment->id }}')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-{{ $payment->id }}" class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <div class="py-1">
                                        <a href="{{ route('sales.payments.print', $payment) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-print ml-2"></i>
                                            طباعة إيصال
                                        </a>
                                        <a href="{{ route('sales.payments.pdf', $payment) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-download ml-2"></i>
                                            تحميل PDF
                                        </a>
                                        @if($payment->customer->phone || $payment->customer->mobile)
                                        <a href="#" onclick="sendWhatsApp({{ $payment->id }}, '{{ addslashes($payment->customer->phone ?: $payment->customer->mobile) }}', '{{ addslashes($payment->customer->name_ar ?: $payment->customer->name) }}')"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fab fa-whatsapp ml-2 text-green-600"></i>
                                            إرسال عبر واتساب
                                        </a>
                                        @endif
                                        @if($payment->status === 'pending')
                                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-times ml-2"></i>
                                            إلغاء الدفعة
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
                    <i class="fas fa-credit-card text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد مدفوعات</h3>
                    <p class="text-gray-500 mb-4">لم يتم العثور على أي مدفوعات</p>
                    <a href="{{ route('sales.payments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        تسجيل دفعة جديدة
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $payments->links() }}
        </div>
        @endif
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
    filterPayments();
});

document.getElementById('status').addEventListener('change', function() {
    filterPayments();
});

document.getElementById('payment_method').addEventListener('change', function() {
    filterPayments();
});

// Listen for customer dropdown changes
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for the new searchable dropdown
    const customerDropdown = document.querySelector('[data-name="customer"]');
    if (customerDropdown) {
        customerDropdown.addEventListener('dropdown-changed', function(e) {
            filterPayments();
        });

        // Set initial value if exists in URL
        const urlParams = new URLSearchParams(window.location.search);
        const customerId = urlParams.get('customer_id');
        if (customerId) {
            const hiddenInput = document.querySelector('input[name="customer"]');
            if (hiddenInput) {
                hiddenInput.value = customerId;
                // Trigger the dropdown to update its display
                const event = new CustomEvent('value-changed', { detail: { value: customerId } });
                customerDropdown.dispatchEvent(event);
            }
        }
    }
});

document.getElementById('date_range').addEventListener('change', function() {
    filterPayments();
});

function filterPayments() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('status').value;
    const paymentMethod = document.getElementById('payment_method').value;
    const customer = getDropdownValue('customer'); // Use helper function for searchable dropdown
    const dateRange = document.getElementById('date_range').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (paymentMethod) params.append('payment_method', paymentMethod);
    if (customer) params.append('customer_id', customer);
    if (dateRange) params.append('date_range', dateRange);
    
    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

// Helper function to get dropdown value
function getDropdownValue(name) {
    const hiddenInput = document.querySelector(`input[name="${name}"]`);
    return hiddenInput ? hiddenInput.value : '';
}

// WhatsApp sending function
function sendWhatsApp(paymentId, phone, customerName) {
    // Show loading state
    const loadingModal = document.createElement('div');
    loadingModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingModal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700">جاري تحضير الرسالة...</span>
            </div>
        </div>
    `;
    document.body.appendChild(loadingModal);

    // Generate WhatsApp message
    fetch(`/sales/payments/${paymentId}/whatsapp-message`)
        .then(response => response.json())
        .then(data => {
            document.body.removeChild(loadingModal);

            if (data.success) {
                // Clean phone number (remove spaces, dashes, etc.)
                const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');

                // Ensure phone starts with country code
                let whatsappPhone = cleanPhone;
                if (!whatsappPhone.startsWith('+')) {
                    // Assume Iraq country code if not provided
                    if (whatsappPhone.startsWith('07')) {
                        whatsappPhone = '+964' + whatsappPhone.substring(1);
                    } else if (!whatsappPhone.startsWith('964')) {
                        whatsappPhone = '+964' + whatsappPhone;
                    } else {
                        whatsappPhone = '+' + whatsappPhone;
                    }
                }

                // Create WhatsApp URL
                const whatsappUrl = `https://wa.me/${whatsappPhone.replace('+', '')}?text=${encodeURIComponent(data.message)}`;

                // Show confirmation modal
                showWhatsAppModal(whatsappUrl, customerName, whatsappPhone);
            } else {
                alert('حدث خطأ في تحضير الرسالة: ' + data.message);
            }
        })
        .catch(error => {
            document.body.removeChild(loadingModal);
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال بالخادم');
        });
}

function showWhatsAppModal(whatsappUrl, customerName, phone) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <div class="flex items-center mb-4">
                <i class="fab fa-whatsapp text-green-600 text-2xl ml-3"></i>
                <h3 class="text-lg font-semibold text-gray-900">إرسال إيصال عبر واتساب</h3>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 mb-2">سيتم إرسال إيصال الدفعة إلى:</p>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-900">${customerName}</p>
                    <p class="text-gray-600 font-mono">${phone}</p>
                </div>
            </div>
            <div class="flex space-x-3 space-x-reverse">
                <button onclick="closeModal(this)"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    إلغاء
                </button>
                <button onclick="openWhatsApp('${whatsappUrl}'); closeModal(this);"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center">
                    <i class="fab fa-whatsapp ml-2"></i>
                    فتح واتساب
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function openWhatsApp(url) {
    window.open(url, '_blank');
}

function closeModal(button) {
    const modal = button.closest('.fixed');
    if (modal) {
        document.body.removeChild(modal);
    }
}
</script>
@endsection
