@extends('layouts.app')

@section('title', 'العملاء - MaxCon ERP')
@section('page-title', 'إدارة العملاء')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إدارة العملاء</h1>
            <p class="text-gray-600">إدارة قاعدة بيانات العملاء ومتابعة المعاملات</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.customers.import') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-file-excel ml-2"></i>
                استيراد من Excel
            </a>
            <a href="{{ route('sales.customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus ml-2"></i>
                عميل جديد
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('sales.customers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البحث</label>
                <input type="text" name="search" value="{{ $request->get('search') }}" 
                       placeholder="اسم العميل، الكود، الهاتف..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع العميل</label>
                <x-searchable-dropdown
                    name="type"
                    placeholder="جميع الأنواع"
                    search-placeholder="ابحث في أنواع العملاء..."
                    :options="collect($filters['types'])->map(function($type, $key) use ($filters) {
                        return [
                            'value' => $key,
                            'text' => ($filters['types_ar'][$key] ?? $type)
                        ];
                    })->prepend(['value' => '', 'text' => 'جميع الأنواع'])->values()->toArray()"
                    value="{{ $request->get('type') }}"
                />
            </div>

            <!-- Governorate Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المحافظة</label>
                <x-searchable-dropdown
                    name="governorate"
                    placeholder="جميع المحافظات"
                    search-placeholder="ابحث في المحافظات..."
                    :options="collect($filters['governorates'])->map(fn($governorate) => [
                        'value' => $governorate,
                        'text' => $governorate
                    ])->prepend(['value' => '', 'text' => 'جميع المحافظات'])->values()->toArray()"
                    value="{{ $request->get('governorate') }}"
                />
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ $request->get('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ $request->get('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <!-- Search Button -->
            <div class="md:col-span-4 flex justify-end space-x-2 space-x-reverse">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search ml-1"></i>
                    بحث
                </button>
                <a href="{{ route('sales.customers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                    <i class="fas fa-times ml-1"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي العملاء</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $customers->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">عملاء نشطون</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $customers->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">عملاء جدد هذا الشهر</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $customers->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">عملاء لديهم مستحقات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $customers->where('outstanding_balance', '>', 0)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة العملاء</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العميل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المحافظة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي المبيعات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستحقات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $customer->code }}</div>
                                    <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $customer->type === 'pharmacy' ? 'bg-green-100 text-green-800' : ($customer->type === 'hospital' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $filters['types_ar'][$customer->type] ?? $customer->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $customer->governorate }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($customer->total_sales ?? 0, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->outstanding_balance > 0)
                                <span class="text-sm font-medium text-red-600">{{ number_format($customer->outstanding_balance, 0) }} د.ع</span>
                            @else
                                <span class="text-sm text-gray-500">لا توجد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">نشط</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">غير نشط</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('sales.customers.show', $customer) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.customers.edit', $customer) }}" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('sales.customers.orders', $customer) }}" class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                                <a href="{{ route('sales.customers.invoices', $customer) }}" class="text-orange-600 hover:text-orange-900">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p class="text-lg">لا توجد عملاء</p>
                                <p class="text-sm">ابدأ بإضافة عميل جديد</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
