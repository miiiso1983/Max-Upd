@extends('layouts.app')

@section('title', 'تفاصيل الحساب - MaxCon ERP')
@section('page-title', 'تفاصيل الحساب')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $account->name_ar ?: $account->name }}
                @if($account->code)
                    <span class="text-gray-500 font-mono">({{ $account->code }})</span>
                @endif
            </h1>
            <p class="text-gray-600">{{ $account->hierarchy_path_ar ?: $account->hierarchy_path }}</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            @if(!$account->is_system_account)
                <a href="{{ route('accounting.accounts.edit', $account) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
            @endif
            <a href="{{ route('accounting.accounts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Account Status and Balance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                    <p class="text-lg font-bold {{ $account->calculated_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($account->calculated_balance, 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Opening Balance -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">الرصيد الافتتاحي</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ number_format($account->opening_balance, 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Debits -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-up text-orange-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المدين</p>
                    <p class="text-lg font-bold text-orange-600">
                        {{ number_format($stats['total_debits'], 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Credits -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-down text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي الدائن</p>
                    <p class="text-lg font-bold text-purple-600">
                        {{ number_format($stats['total_credits'], 2) }} {{ $account->currency }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات الحساب</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">النوع الفرعي</label>
                            <p class="text-sm text-gray-900">
                                @if($account->subtype)
                                    @switch($account->subtype)
                                        @case('current_asset')
                                            أصول متداولة
                                            @break
                                        @case('non_current_asset')
                                            أصول غير متداولة
                                            @break
                                        @case('current_liability')
                                            خصوم متداولة
                                            @break
                                        @case('non_current_liability')
                                            خصوم غير متداولة
                                            @break
                                        @case('owners_equity')
                                            حقوق المالكين
                                            @break
                                        @case('operating_revenue')
                                            إيرادات تشغيلية
                                            @break
                                        @case('non_operating_revenue')
                                            إيرادات غير تشغيلية
                                            @break
                                        @case('operating_expense')
                                            مصروفات تشغيلية
                                            @break
                                        @case('non_operating_expense')
                                            مصروفات غير تشغيلية
                                            @break
                                        @default
                                            {{ $account->subtype }}
                                    @endswitch
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                            <p class="text-sm">
                                @if($account->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        نشط
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        غير نشط
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">العملة</label>
                            <p class="text-sm text-gray-900">{{ $account->currency }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">حساب ضريبي</label>
                            <p class="text-sm text-gray-900">
                                @if($account->tax_account)
                                    <span class="text-green-600">نعم</span>
                                @else
                                    <span class="text-gray-600">لا</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($account->description || $account->description_ar)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                        @if($account->description_ar)
                            <p class="text-sm text-gray-900 mb-2">{{ $account->description_ar }}</p>
                        @endif
                        @if($account->description)
                            <p class="text-sm text-gray-600">{{ $account->description }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Child Accounts -->
            @if($account->children && $account->children->count() > 0)
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">الحسابات الفرعية</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرمز</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">اسم الحساب</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرصيد</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($account->children as $child)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                    {{ $child->code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $child->name_ar ?: $child->name }}</div>
                                    @if($child->name_ar && $child->name !== $child->name_ar)
                                        <div class="text-sm text-gray-500">{{ $child->name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $child->subtype ? ucfirst(str_replace('_', ' ', $child->subtype)) : ucfirst($child->type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($child->current_balance, 2) }} {{ $child->currency }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($child->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            نشط
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            غير نشط
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('accounting.accounts.show', $child) }}" class="text-blue-600 hover:text-blue-900">
                                        عرض
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Recent Transactions -->
            @if($account->journalEntries && $account->journalEntries->count() > 0)
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">آخر المعاملات</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مدين</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">دائن</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($account->journalEntries->take(10) as $entry)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->transaction ? $entry->transaction->transaction_date->format('Y-m-d') : 'N/A' }}
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Account Hierarchy -->
            @if($account->parent)
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">الحساب الأب</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sitemap text-blue-600"></i>
                            </div>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $account->parent->name_ar ?: $account->parent->name }}
                            </p>
                            <p class="text-sm text-gray-500">{{ $account->parent->code }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('accounting.accounts.show', $account->parent) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            عرض الحساب الأب
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Account Statistics -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">إحصائيات الحساب</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">عدد المعاملات:</span>
                        <span class="text-sm font-medium">{{ $stats['entry_count'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">آخر معاملة:</span>
                        <span class="text-sm font-medium">
                            {{ $stats['last_transaction_date'] ? $stats['last_transaction_date']->format('Y-m-d') : 'لا توجد معاملات' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">نوع الرصيد:</span>
                        <span class="text-sm font-medium">
                            @if($account->isDebitNormal())
                                مدين طبيعي
                            @else
                                دائن طبيعي
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">حساب نظام:</span>
                        <span class="text-sm font-medium">
                            @if($account->is_system_account)
                                <span class="text-orange-600">نعم</span>
                            @else
                                <span class="text-green-600">لا</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات النظام</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تم الإنشاء بواسطة</label>
                        <p class="text-sm text-gray-900">{{ $account->creator->name ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإنشاء</label>
                        <p class="text-sm text-gray-900">{{ $account->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">آخر تحديث</label>
                        <p class="text-sm text-gray-900">{{ $account->updated_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
