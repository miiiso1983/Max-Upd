@extends('layouts.app')

@section('title', 'إدارة الرواتب')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إدارة الرواتب</h1>
            <p class="text-gray-600 mt-1">إدارة رواتب الموظفين والمكافآت</p>
            <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle ml-1"></i>
                    <strong>للإنشاء:</strong> استخدم الزر الأزرق "إنشاء كشف راتب" لإنشاء كشف راتب لموظف واحد
                </p>
            </div>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="exportPayroll()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-download ml-2"></i>
                تصدير Excel
            </button>
            <button onclick="generateBulkPayroll()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200" title="إنشاء كشوف رواتب لجميع الموظفين (قيد التطوير)">
                <i class="fas fa-calculator ml-2"></i>
                إنشاء رواتب جماعي
            </button>
            <a href="{{ route('hr.payroll.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 shadow-lg border-2 border-blue-400" title="إنشاء كشف راتب لموظف واحد">
                <i class="fas fa-plus ml-2"></i>
                إنشاء كشف راتب
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي الرواتب</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_salaries'] ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">عدد الموظفين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_employees'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">متوسط الراتب</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_salary'] ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">الشهر الحالي</p>
                    <p class="text-2xl font-bold text-gray-900">{{ now()->format('m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Month Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الشهر</label>
                <x-searchable-dropdown
                    name="month_filter"
                    placeholder="اختر الشهر"
                    search-placeholder="ابحث في الشهور..."
                    :options="collect(range(1, 12))->map(function($i) {
                        return [
                            'value' => $i,
                            'text' => \Carbon\Carbon::create()->month($i)->format('F')
                        ];
                    })->toArray()"
                    value="{{ request('month', now()->month) }}"
                />
            </div>

            <!-- Year Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">السنة</label>
                <select id="year_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($year = now()->year - 2; $year <= now()->year + 1; $year++)
                        <option value="{{ $year }}" {{ request('year', now()->year) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">القسم</label>
                <select id="department_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الأقسام</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select id="status_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>في الانتظار</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمد</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>مدفوع</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-4 space-x-2 space-x-reverse">
            <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-search ml-2"></i>
                تطبيق الفلاتر
            </button>
            <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-times ml-2"></i>
                مسح الفلاتر
            </button>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفترة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الراتب الأساسي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">البدلات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الخصومات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الصافي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payrolls as $payroll)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($payroll->employee->profile_photo)
                                    <img class="h-8 w-8 rounded-full object-cover ml-2" src="{{ asset('storage/' . $payroll->employee->profile_photo) }}" alt="{{ $payroll->employee->full_name_ar }}">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center ml-2">
                                        <i class="fas fa-user text-gray-600 text-xs"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->full_name_ar }}</div>
                                    <div class="text-xs text-gray-500">{{ $payroll->employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payroll->month }}/{{ $payroll->year }}
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::create($payroll->year, $payroll->month)->format('F Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-mono">{{ number_format($payroll->basic_salary) }} د.ع</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-mono text-green-600">+{{ number_format($payroll->total_allowances) }} د.ع</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-mono text-red-600">-{{ number_format($payroll->total_deductions) }} د.ع</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-mono font-bold text-lg">{{ number_format($payroll->net_salary) }} د.ع</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($payroll->status)
                                @case('pending')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock ml-1"></i>
                                        في الانتظار
                                    </span>
                                    @break
                                @case('approved')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-check ml-1"></i>
                                        معتمد
                                    </span>
                                    @break
                                @case('paid')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle ml-1"></i>
                                        مدفوع
                                    </span>
                                    @break
                                @case('cancelled')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle ml-1"></i>
                                        ملغي
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $payroll->status }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('hr.payroll.show', $payroll) }}" class="text-blue-600 hover:text-blue-900 transition duration-200" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($payroll->status === 'pending')
                                    <a href="{{ route('hr.payroll.edit', $payroll) }}" class="text-yellow-600 hover:text-yellow-900 transition duration-200" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button data-payroll-id="{{ $payroll->id }}" onclick="approvePayroll(this.dataset.payrollId)" class="text-green-600 hover:text-green-900 transition duration-200" title="اعتماد">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @if($payroll->status === 'approved')
                                    <button data-payroll-id="{{ $payroll->id }}" onclick="markAsPaid(this.dataset.payrollId)" class="text-purple-600 hover:text-purple-900 transition duration-200" title="تسجيل كمدفوع">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                @endif
                                <button data-payroll-id="{{ $payroll->id }}" onclick="printPayslip(this.dataset.payrollId)" class="text-indigo-600 hover:text-indigo-900 transition duration-200" title="طباعة كشف الراتب">
                                    <i class="fas fa-print"></i>
                                </button>
                                @if($payroll->status === 'pending')
                                    <button data-payroll-id="{{ $payroll->id }}" onclick="deletePayroll(this.dataset.payrollId)" class="text-red-600 hover:text-red-900 transition duration-200" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-money-bill-wave text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg">لا توجد سجلات رواتب</p>
                            <p class="text-sm">ابدأ بإنشاء كشف راتب جديد</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payrolls->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function applyFilters() {
    const month = document.getElementById('month_filter').value;
    const year = document.getElementById('year_filter').value;
    const department = document.getElementById('department_filter').value;
    const status = document.getElementById('status_filter').value;
    
    const params = new URLSearchParams();
    if (month) params.append('month', month);
    if (year) params.append('year', year);
    if (department) params.append('department_id', department);
    if (status) params.append('status', status);
    
    window.location.href = '{{ route("hr.payroll.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("hr.payroll.index") }}';
}

function generateBulkPayroll() {
    // Show bulk payroll modal
    document.getElementById('bulkPayrollModal').classList.remove('hidden');
}

function approvePayroll(id) {
    if (confirm('هل تريد اعتماد هذا الراتب؟')) {
        // Implementation for approving payroll
        alert('سيتم تطوير هذه الميزة قريباً');
    }
}

function markAsPaid(id) {
    if (confirm('هل تريد تسجيل هذا الراتب كمدفوع؟')) {
        // Implementation for marking as paid
        alert('سيتم تطوير هذه الميزة قريباً');
    }
}

function printPayslip(id) {
    window.open(`/hr/payroll/${id}/payslip`, '_blank');
}

function deletePayroll(id) {
    if (confirm('هل أنت متأكد من حذف هذا الراتب؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/hr/payroll/${id}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function exportPayroll() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("hr.payroll.index") }}?' + params.toString();
}

// Bulk Payroll Functions
function closeBulkPayrollModal() {
    document.getElementById('bulkPayrollModal').classList.add('hidden');
    document.getElementById('employeePreview').classList.add('hidden');
}

function previewBulkPayroll() {
    const departmentId = document.getElementById('bulk_department_id').value;
    const payPeriodStart = document.getElementById('bulk_pay_period_start').value;
    const payPeriodEnd = document.getElementById('bulk_pay_period_end').value;
    const skipExisting = document.getElementById('skip_existing').checked;

    if (!payPeriodStart || !payPeriodEnd) {
        alert('يرجى تحديد فترة الراتب');
        return;
    }

    // Show loading
    document.getElementById('employeeList').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>';
    document.getElementById('employeePreview').classList.remove('hidden');

    // Fetch employees
    fetch('{{ route("hr.payroll.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            department_id: departmentId,
            pay_period_start: payPeriodStart,
            pay_period_end: payPeriodEnd,
            skip_existing: skipExisting
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayEmployeePreview(data.employees);
        } else {
            alert('خطأ: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء جلب البيانات');
    });
}

function displayEmployeePreview(employees) {
    const employeeList = document.getElementById('employeeList');
    const employeeCount = document.getElementById('employeeCount');

    if (employees.length === 0) {
        employeeList.innerHTML = '<p class="text-gray-500 text-center py-4">لا توجد موظفين متاحين للفترة المحددة</p>';
        employeeCount.textContent = '';
        return;
    }

    let html = '<div class="space-y-2">';
    employees.forEach(employee => {
        html += `
            <div class="flex justify-between items-center p-2 bg-white rounded border">
                <div>
                    <span class="font-medium">${employee.name}</span>
                    <span class="text-sm text-gray-500">(${employee.employee_id})</span>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">${employee.department}</span>
                    <span class="text-green-600 font-medium mr-2">${employee.basic_salary.toLocaleString()} د.ع</span>
                </div>
            </div>
        `;
    });
    html += '</div>';

    employeeList.innerHTML = html;
    employeeCount.textContent = `إجمالي الموظفين: ${employees.length}`;
}

function processBulkPayroll() {
    const form = document.getElementById('bulkPayrollForm');
    const formData = new FormData(form);

    if (!form.pay_period_start.value || !form.pay_period_end.value) {
        alert('يرجى تحديد فترة الراتب');
        return;
    }

    if (!confirm('هل أنت متأكد من إنشاء كشوف الرواتب للموظفين المحددين؟')) {
        return;
    }

    // Close modal and show progress
    closeBulkPayrollModal();
    document.getElementById('progressModal').classList.remove('hidden');

    // Process bulk payroll
    fetch('{{ route("hr.payroll.bulk-generate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateProgress(100, 'تم إنشاء الرواتب بنجاح!');
            setTimeout(() => {
                document.getElementById('progressModal').classList.add('hidden');
                location.reload();
            }, 2000);
        } else {
            document.getElementById('progressModal').classList.add('hidden');
            alert('خطأ: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('progressModal').classList.add('hidden');
        alert('حدث خطأ أثناء إنشاء الرواتب');
    });
}

function updateProgress(percentage, text) {
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = text;
}
</script>

<!-- Bulk Payroll Generation Modal -->
<div id="bulkPayrollModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">إنشاء رواتب جماعي</h3>
                <button onclick="closeBulkPayrollModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="bulkPayrollForm" class="mt-4 space-y-4">
                @csrf

                <!-- Pay Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bulk_pay_period_start" class="block text-sm font-medium text-gray-700 mb-2">
                            بداية فترة الراتب <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="bulk_pay_period_start"
                               name="pay_period_start"
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="bulk_pay_period_end" class="block text-sm font-medium text-gray-700 mb-2">
                            نهاية فترة الراتب <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="bulk_pay_period_end"
                               name="pay_period_end"
                               value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Department Filter -->
                <div>
                    <label for="bulk_department_id" class="block text-sm font-medium text-gray-700 mb-2">
                        القسم (اختياري)
                    </label>
                    <select id="bulk_department_id"
                            name="department_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">جميع الأقسام</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name_ar ?: $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Options -->
                <div class="space-y-3">
                    <h4 class="font-medium text-gray-900">خيارات الإنشاء:</h4>

                    <div class="flex items-center">
                        <input type="checkbox"
                               id="auto_calculate_tax"
                               name="auto_calculate_tax"
                               checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto_calculate_tax" class="mr-2 block text-sm text-gray-900">
                            حساب الضرائب تلقائياً
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               id="auto_calculate_social_security"
                               name="auto_calculate_social_security"
                               checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto_calculate_social_security" class="mr-2 block text-sm text-gray-900">
                            حساب الضمان الاجتماعي تلقائياً (5%)
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               id="auto_calculate_health_insurance"
                               name="auto_calculate_health_insurance"
                               checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto_calculate_health_insurance" class="mr-2 block text-sm text-gray-900">
                            حساب التأمين الصحي تلقائياً (2%)
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               id="skip_existing"
                               name="skip_existing"
                               checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="skip_existing" class="mr-2 block text-sm text-gray-900">
                            تجاهل الموظفين الذين لديهم كشف راتب للفترة نفسها
                        </label>
                    </div>
                </div>

                <!-- Preview Section -->
                <div id="employeePreview" class="hidden">
                    <h4 class="font-medium text-gray-900 mb-2">الموظفين المختارين:</h4>
                    <div id="employeeList" class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                        <!-- Employee list will be populated here -->
                    </div>
                    <p id="employeeCount" class="text-sm text-gray-600 mt-2"></p>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-4 border-t mt-6">
                <button onclick="previewBulkPayroll()"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-eye ml-2"></i>
                    معاينة الموظفين
                </button>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="closeBulkPayrollModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                        إلغاء
                    </button>
                    <button onclick="processBulkPayroll()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-calculator ml-2"></i>
                        إنشاء الرواتب
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div id="progressModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-1/3 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">جاري إنشاء الرواتب...</h3>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="progressText" class="text-sm text-gray-600">جاري التحضير...</p>
            <div class="mt-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            </div>
        </div>
    </div>
</div>

@endsection
