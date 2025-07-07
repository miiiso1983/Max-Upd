@extends('layouts.app')

@section('title', 'تفاصيل القيد - MaxCon ERP')
@section('page-title', 'تفاصيل القيد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                قيد رقم: {{ $transaction->transaction_number }}
            </h1>
            <p class="text-gray-600">{{ $transaction->description_ar ?: $transaction->description }}</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            @if($transaction->canBeEdited())
                <a href="{{ route('accounting.transactions.edit', $transaction) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
            @endif
            
            @if($transaction->canBePosted())
                <button onclick="postTransaction()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-check ml-2"></i>
                    ترحيل
                </button>
            @endif
            
            @if($transaction->canBeReversed())
                <button onclick="reverseTransaction()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-undo ml-2"></i>
                    عكس القيد
                </button>
            @endif
            
            <button onclick="printTransaction()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>
            
            <a href="{{ route('accounting.transactions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Transaction Status and Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Status -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 {{ $transaction->status === 'posted' ? 'bg-green-100' : ($transaction->status === 'draft' ? 'bg-yellow-100' : 'bg-red-100') }} rounded-full flex items-center justify-center">
                        <i class="fas {{ $transaction->status === 'posted' ? 'fa-check text-green-600' : ($transaction->status === 'draft' ? 'fa-clock text-yellow-600' : 'fa-times text-red-600') }}"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">الحالة</p>
                    <p class="text-lg font-bold {{ $transaction->status === 'posted' ? 'text-green-600' : ($transaction->status === 'draft' ? 'text-yellow-600' : 'text-red-600') }}">
                        @switch($transaction->status)
                            @case('posted')
                                مرحل
                                @break
                            @case('draft')
                                مسودة
                                @break
                            @case('cancelled')
                                ملغي
                                @break
                            @default
                                {{ $transaction->status }}
                        @endswitch
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المبلغ</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ number_format($transaction->total_amount, 2) }} {{ $transaction->currency ?: 'IQD' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Transaction Date -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">تاريخ القيد</p>
                    <p class="text-lg font-bold text-purple-600">
                        {{ $transaction->transaction_date->format('Y-m-d') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Balance Check -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 {{ $transaction->is_balanced ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                        <i class="fas {{ $transaction->is_balanced ? 'fa-balance-scale text-green-600' : 'fa-exclamation-triangle text-red-600' }}"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">التوازن</p>
                    <p class="text-lg font-bold {{ $transaction->is_balanced ? 'text-green-600' : 'text-red-600' }}">
                        {{ $transaction->is_balanced ? 'متوازن' : 'غير متوازن' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Details -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">تفاصيل القيد</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم القيد</label>
                    <p class="text-sm text-gray-900 font-mono">{{ $transaction->transaction_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع القيد</label>
                    <p class="text-sm text-gray-900">
                        @switch($transaction->type)
                            @case('journal_entry')
                                قيد يومية
                                @break
                            @case('opening_balance')
                                رصيد افتتاحي
                                @break
                            @case('closing_entry')
                                قيد إقفال
                                @break
                            @case('adjusting_entry')
                                قيد تسوية
                                @break
                            @default
                                {{ $transaction->type }}
                        @endswitch
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإنشاء</label>
                    <p class="text-sm text-gray-900">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">أنشأ بواسطة</label>
                    <p class="text-sm text-gray-900">{{ $transaction->creator->name ?? 'غير محدد' }}</p>
                </div>
                @if($transaction->posted_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الترحيل</label>
                    <p class="text-sm text-gray-900">{{ $transaction->posted_at->format('Y-m-d H:i:s') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رحل بواسطة</label>
                    <p class="text-sm text-gray-900">{{ $transaction->poster->name ?? 'غير محدد' }}</p>
                </div>
                @endif
            </div>

            @if($transaction->description || $transaction->description_ar)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                @if($transaction->description_ar)
                    <p class="text-sm text-gray-900 mb-2">{{ $transaction->description_ar }}</p>
                @endif
                @if($transaction->description)
                    <p class="text-sm text-gray-600">{{ $transaction->description }}</p>
                @endif
            </div>
            @endif

            @if($transaction->notes)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                <p class="text-sm text-gray-900">{{ $transaction->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Journal Entries -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قيود اليومية</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحساب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مدين</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">دائن</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transaction->journalEntries as $entry)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $entry->account->name_ar ?: $entry->account->name }}
                            </div>
                            @if($entry->account->code)
                                <div class="text-sm text-gray-500 font-mono">{{ $entry->account->code }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $entry->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($entry->type === 'debit')
                                <span class="font-medium text-green-600">{{ number_format($entry->amount, 2) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($entry->type === 'credit')
                                <span class="font-medium text-orange-600">{{ number_format($entry->amount, 2) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-3 text-right font-medium text-gray-900">الإجمالي:</td>
                        <td class="px-6 py-3 text-right font-bold text-green-600">
                            {{ number_format($transaction->total_debits, 2) }}
                        </td>
                        <td class="px-6 py-3 text-right font-bold text-orange-600">
                            {{ number_format($transaction->total_credits, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function postTransaction() {
    if (confirm('هل أنت متأكد من ترحيل هذا القيد؟ لن يمكن تعديله بعد الترحيل.')) {
        fetch(`{{ route('accounting.transactions.show', $transaction) }}/post`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('تم ترحيل القيد بنجاح');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء ترحيل القيد');
        });
    }
}

function reverseTransaction() {
    const reason = prompt('يرجى إدخال سبب عكس القيد:');
    if (reason) {
        fetch(`{{ route('accounting.transactions.show', $transaction) }}/reverse`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('تم عكس القيد بنجاح');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء عكس القيد');
        });
    }
}

function printTransaction() {
    window.print();
}
</script>
@endpush
