@extends('layouts.app')

@section('title', 'سجل حركات الحساب - MaxCon ERP')
@section('page-title', 'سجل حركات الحساب')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                سجل حركات الحساب: {{ $account->name_ar ?: $account->name }}
            </h1>
            <p class="text-gray-600">جميع المعاملات المسجلة على هذا الحساب</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('accounting.accounts.report', $account) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-chart-line ml-2"></i>
                تقرير الحساب
            </a>
            <a href="{{ route('accounting.accounts.show', $account) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للحساب
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">فلترة المعاملات</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" 
                       id="start_date" 
                       name="start_date" 
                       value="{{ $filters['start_date'] ?? '' }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" 
                       id="end_date" 
                       name="end_date" 
                       value="{{ $filters['end_date'] ?? '' }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">نوع الحركة</label>
                <select id="type" 
                        name="type" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحركات</option>
                    <option value="debit" {{ ($filters['type'] ?? '') === 'debit' ? 'selected' : '' }}>مدين</option>
                    <option value="credit" {{ ($filters['type'] ?? '') === 'credit' ? 'selected' : '' }}>دائن</option>
                </select>
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
        <!-- Total Transactions -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-list text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المعاملات</p>
                    <p class="text-lg font-bold text-blue-600">{{ $transactions->total() }}</p>
                </div>
            </div>
        </div>

        <!-- Current Balance -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-balance-scale text-green-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">الرصيد الحالي</p>
                    <p class="text-lg font-bold {{ $account->calculateBalance() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($account->calculateBalance(), 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Account Type -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tag text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">نوع الحساب</p>
                    <p class="text-lg font-bold text-purple-600">
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
            </div>
        </div>

        <!-- Account Code -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-code text-orange-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">رمز الحساب</p>
                    <p class="text-lg font-bold text-orange-600 font-mono">{{ $account->code ?: 'غير محدد' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                المعاملات ({{ $transactions->count() }} من {{ $transactions->total() }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم المعاملة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $entry)
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($entry->type === 'debit')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    مدين
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    دائن
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $entry->type === 'debit' ? 'text-green-600' : 'text-orange-600' }}">
                            {{ number_format($entry->amount, 2) }} {{ $account->currency }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($entry->transaction)
                                @switch($entry->transaction->status)
                                    @case('posted')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            مرحل
                                        </span>
                                        @break
                                    @case('draft')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            مسودة
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            ملغي
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $entry->transaction->status }}
                                        </span>
                                @endswitch
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    غير محدد
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($entry->transaction)
                                <a href="{{ route('accounting.transactions.show', $entry->transaction) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    عرض المعاملة
                                </a>
                            @else
                                <span class="text-gray-400">غير متاح</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">لا توجد معاملات</p>
                                <p class="text-sm">لم يتم العثور على معاملات لهذا الحساب</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
