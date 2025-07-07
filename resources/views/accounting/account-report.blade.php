@extends('layouts.app')

@section('title', 'تقرير الحساب - MaxCon ERP')
@section('page-title', 'تقرير الحساب')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                تقرير الحساب: {{ $account->name_ar ?: $account->name }}
            </h1>
            <p class="text-gray-600">
                من {{ \Carbon\Carbon::parse($period['start_date'])->format('Y-m-d') }} 
                إلى {{ \Carbon\Carbon::parse($period['end_date'])->format('Y-m-d') }}
            </p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="printReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>
            <a href="{{ route('accounting.accounts.show', $account) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للحساب
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">فلترة التقرير</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" 
                       id="start_date" 
                       name="start_date" 
                       value="{{ request('start_date', \Carbon\Carbon::parse($period['start_date'])->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" 
                       id="end_date" 
                       name="end_date" 
                       value="{{ request('end_date', \Carbon\Carbon::parse($period['end_date'])->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-filter ml-2"></i>
                    تطبيق الفلتر
                </button>
            </div>
        </form>
    </div>

    <!-- Account Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Opening Balance -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-play text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">الرصيد الافتتاحي</p>
                    <p class="text-lg font-bold {{ $balances['opening_balance'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        {{ number_format($balances['opening_balance'], 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Period Debits -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-up text-green-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المدين</p>
                    <p class="text-lg font-bold text-green-600">
                        {{ number_format($balances['period_debits'], 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Period Credits -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-down text-orange-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي الدائن</p>
                    <p class="text-lg font-bold text-orange-600">
                        {{ number_format($balances['period_credits'], 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Closing Balance -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-stop text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">الرصيد الختامي</p>
                    <p class="text-lg font-bold {{ $balances['closing_balance'] >= 0 ? 'text-purple-600' : 'text-red-600' }}">
                        {{ number_format($balances['closing_balance'], 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">معلومات الحساب</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رمز الحساب</label>
                    <p class="text-sm text-gray-900 font-mono">{{ $account->code ?: 'غير محدد' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع الحساب</label>
                    <p class="text-sm text-gray-900">
                        @switch($account->type)
                            @case('asset')
                                أصول
                                @break
                            @case('liability')
                                خصوم
                                @break
                            @case('equity')
                                حقوق الملكية
                                @break
                            @case('revenue')
                                إيرادات
                                @break
                            @case('expense')
                                مصروفات
                                @break
                            @default
                                {{ $account->type }}
                        @endswitch
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المسار الهرمي</label>
                    <p class="text-sm text-gray-900">{{ $account->hierarchy_path_ar ?: $account->hierarchy_path }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                حركات الحساب خلال الفترة ({{ $transactions->count() }} حركة)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم المعاملة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مدين</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">دائن</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $runningBalance = $balances['opening_balance'];
                    @endphp
                    
                    <!-- Opening Balance Row -->
                    <tr class="bg-blue-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($period['start_date'])->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">الرصيد الافتتاحي</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($runningBalance, 2) }}
                        </td>
                    </tr>

                    @foreach($transactions as $entry)
                        @php
                            if ($entry->type === 'debit') {
                                $runningBalance += $entry->amount;
                            } else {
                                $runningBalance -= $entry->amount;
                            }
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $entry->transaction ? $entry->transaction->transaction_date->format('Y-m-d') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                {{ $entry->transaction ? $entry->transaction->reference_number : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $entry->description ?: ($entry->transaction ? $entry->transaction->description : 'N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($entry->type === 'debit')
                                    {{ number_format($entry->amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($entry->type === 'credit')
                                    {{ number_format($entry->amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($runningBalance, 2) }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Closing Balance Row -->
                    <tr class="bg-purple-50 font-medium">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($period['end_date'])->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">الرصيد الختامي</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $balances['closing_balance'] >= 0 ? 'text-purple-600' : 'text-red-600' }}">
                            {{ number_format($balances['closing_balance'], 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">ملخص الفترة</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">الرصيد الافتتاحي:</span>
                        <span class="text-sm font-medium">{{ number_format($balances['opening_balance'], 2) }} {{ $account->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">إجمالي المدين:</span>
                        <span class="text-sm font-medium text-green-600">{{ number_format($balances['period_debits'], 2) }} {{ $account->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">إجمالي الدائن:</span>
                        <span class="text-sm font-medium text-orange-600">{{ number_format($balances['period_credits'], 2) }} {{ $account->currency }}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">صافي التغيير:</span>
                        <span class="text-sm font-medium {{ $balances['net_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($balances['net_change'], 2) }} {{ $account->currency }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t pt-3">
                        <span class="text-base font-semibold text-gray-900">الرصيد الختامي:</span>
                        <span class="text-base font-bold {{ $balances['closing_balance'] >= 0 ? 'text-purple-600' : 'text-red-600' }}">
                            {{ number_format($balances['closing_balance'], 2) }} {{ $account->currency }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printReport() {
    window.print();
}
</script>
@endpush
