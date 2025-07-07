@extends('layouts.app')

@section('title', 'قيود اليومية - MaxCon ERP')
@section('page-title', 'قيود اليومية')

@push('styles')
<style>
/* Journal Entries Page Hover Effects */
.transaction-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.transaction-card:hover .transaction-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.transaction-card:hover .transaction-description {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.transaction-card:hover .total-amount {
    color: #6f42c1 !important;
    font-weight: bold;
    transition: all 0.3s ease;
}

.status-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.type-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.journal-entry:hover {
    background: rgba(111, 66, 193, 0.05) !important;
    transition: background 0.3s ease;
}

.journal-entry:hover .account-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.journal-entry:hover .amount {
    color: #6f42c1 !important;
    font-weight: bold;
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

.action-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
}

.balance-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-left: 8px;
}

.balance-indicator.balanced {
    background-color: #10b981;
}

.balance-indicator.unbalanced {
    background-color: #ef4444;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">قيود اليومية</h1>
                <p class="text-gray-600">إدارة ومتابعة القيود المحاسبية والمعاملات المالية</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('accounting.transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    قيد جديد
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="stats-card bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">إجمالي القيود</p>
                        <p class="stats-number text-2xl font-bold text-blue-900">{{ $transactions->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">القيود المرحلة</p>
                        <p class="stats-number text-2xl font-bold text-green-900">
                            {{ $transactions->where('status', 'posted')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600">المسودات</p>
                        <p class="stats-number text-2xl font-bold text-yellow-900">
                            {{ $transactions->where('status', 'draft')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600">المعكوسة</p>
                        <p class="stats-number text-2xl font-bold text-red-900">
                            {{ $transactions->where('status', 'reversed')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-undo text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">إجمالي المبلغ</p>
                        <p class="stats-number text-2xl font-bold text-purple-900">
                            {{ number_format($transactions->sum('total_amount'), 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="رقم القيد أو الوصف..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع القيد</label>
                <select id="type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الأنواع</option>
                    @foreach($filters['types_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" id="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" id="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
        </div>
    </div>

    <!-- Transactions List -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                @forelse($transactions as $transaction)
                <div class="transaction-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 space-x-reverse mb-2">
                                <h3 class="transaction-number text-lg font-semibold text-gray-900">
                                    {{ $transaction->transaction_number }}
                                </h3>
                                <span class="type-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($transaction->type)
                                        @case('journal') bg-blue-100 text-blue-800 @break
                                        @case('sales') bg-green-100 text-green-800 @break
                                        @case('purchase') bg-purple-100 text-purple-800 @break
                                        @case('payment') bg-red-100 text-red-800 @break
                                        @case('receipt') bg-yellow-100 text-yellow-800 @break
                                        @case('adjustment') bg-gray-100 text-gray-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['types_ar'][$transaction->type] ?? $transaction->type }}
                                </span>
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($transaction->status)
                                        @case('draft') bg-yellow-100 text-yellow-800 @break
                                        @case('posted') bg-green-100 text-green-800 @break
                                        @case('reversed') bg-red-100 text-red-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['statuses_ar'][$transaction->status] ?? $transaction->status }}
                                </span>
                                <span class="balance-indicator {{ $transaction->is_balanced ? 'balanced' : 'unbalanced' }}" 
                                      title="{{ $transaction->is_balanced ? 'متوازن' : 'غير متوازن' }}"></span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-3">
                                <div>
                                    <span class="font-medium">التاريخ:</span>
                                    <span>{{ $transaction->transaction_date->format('Y/m/d') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">المبلغ الإجمالي:</span>
                                    <span class="total-amount font-semibold">{{ number_format($transaction->total_amount, 0) }} د.ع</span>
                                </div>
                                <div>
                                    <span class="font-medium">إجمالي المدين:</span>
                                    <span class="font-semibold text-green-600">{{ number_format($transaction->total_debits, 0) }} د.ع</span>
                                </div>
                                <div>
                                    <span class="font-medium">إجمالي الدائن:</span>
                                    <span class="font-semibold text-red-600">{{ number_format($transaction->total_credits, 0) }} د.ع</span>
                                </div>
                            </div>

                            <div class="transaction-description text-sm text-gray-700 mb-3">
                                <span class="font-medium">الوصف:</span>
                                <span>{{ $transaction->description_ar ?? $transaction->description }}</span>
                            </div>

                            <!-- Journal Entries -->
                            <div class="bg-white rounded border border-gray-200 overflow-hidden">
                                <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                    <h4 class="font-medium text-gray-800">تفاصيل القيد</h4>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    @foreach($transaction->journalEntries as $entry)
                                    <div class="journal-entry px-4 py-3 flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 space-x-reverse">
                                                <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">
                                                    {{ $entry->account->code }}
                                                </span>
                                                <span class="account-name font-medium">
                                                    {{ $entry->account->name_ar ?? $entry->account->name }}
                                                </span>
                                                @if($entry->description)
                                                <span class="text-sm text-gray-500">- {{ $entry->description }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4 space-x-reverse">
                                            @if($entry->type === 'debit')
                                            <div class="text-right">
                                                <span class="amount font-semibold text-green-600">{{ number_format($entry->amount, 0) }} د.ع</span>
                                                <div class="text-xs text-gray-500">مدين</div>
                                            </div>
                                            <div class="w-20"></div>
                                            @else
                                            <div class="w-20"></div>
                                            <div class="text-right">
                                                <span class="amount font-semibold text-red-600">{{ number_format($entry->amount, 0) }} د.ع</span>
                                                <div class="text-xs text-gray-500">دائن</div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 space-x-reverse mr-4">
                            <a href="{{ route('accounting.transactions.show', $transaction) }}" 
                               class="action-btn bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600">
                                <i class="fas fa-eye"></i>
                                عرض
                            </a>
                            
                            @if($transaction->status === 'draft')
                            <a href="{{ route('accounting.transactions.edit', $transaction) }}" 
                               class="action-btn bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                            @endif

                            <div class="relative">
                                <button class="action-btn bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600" 
                                        onclick="toggleDropdown('dropdown-{{ $transaction->id }}')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-{{ $transaction->id }}" class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <div class="py-1">
                                        @if($transaction->status === 'draft')
                                        <a href="#" class="block px-4 py-2 text-sm text-green-600 hover:bg-gray-100">
                                            <i class="fas fa-check ml-2"></i>
                                            ترحيل القيد
                                        </a>
                                        @endif

                                        @if($transaction->status === 'posted')
                                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-undo ml-2"></i>
                                            عكس القيد
                                        </a>
                                        @endif

                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-print ml-2"></i>
                                            طباعة القيد
                                        </a>

                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-copy ml-2"></i>
                                            نسخ القيد
                                        </a>

                                        @if($transaction->status === 'draft')
                                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-trash ml-2"></i>
                                            حذف القيد
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
                    <i class="fas fa-book text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد قيود</h3>
                    <p class="text-gray-500 mb-4">لم يتم العثور على أي قيود محاسبية</p>
                    <a href="{{ route('accounting.transactions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        إنشاء قيد جديد
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->links() }}
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
    filterTransactions();
});

document.getElementById('type').addEventListener('change', function() {
    filterTransactions();
});

document.getElementById('status').addEventListener('change', function() {
    filterTransactions();
});

document.getElementById('start_date').addEventListener('change', function() {
    filterTransactions();
});

document.getElementById('end_date').addEventListener('change', function() {
    filterTransactions();
});

function filterTransactions() {
    const search = document.getElementById('search').value;
    const type = document.getElementById('type').value;
    const status = document.getElementById('status').value;
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (type) params.append('type', type);
    if (status) params.append('status', status);
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
@endsection
