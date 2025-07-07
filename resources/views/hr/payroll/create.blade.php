@extends('layouts.app')

@section('title', 'إنشاء كشف راتب جديد - MaxCon ERP')
@section('page-title', 'إنشاء كشف راتب جديد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إنشاء كشف راتب جديد</h1>
            <p class="text-gray-600">إنشاء كشف راتب شهري للموظف</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('hr.payroll.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">معلومات كشف الراتب</h3>
        </div>
        
        @if($errors->any())
            <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                    <h4 class="font-semibold text-red-800">يرجى تصحيح الأخطاء التالية:</h4>
                </div>
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('hr.payroll.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Employee and Period Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Employee -->
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                        الموظف <span class="text-red-500">*</span>
                    </label>
                    <select id="employee_id" 
                            name="employee_id" 
                            required
                            onchange="loadEmployeeData(this.value)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر الموظف</option>
                        @forelse($employees as $employee)
                            <option value="{{ $employee->id }}"
                                    {{ old('employee_id') == $employee->id ? 'selected' : '' }}
                                    data-salary="{{ $employee->basic_salary ?: 0 }}"
                                    data-department="{{ optional($employee->department)->name_ar ?: 'غير محدد' }}"
                                    data-position="{{ optional($employee->position)->title_ar ?: 'غير محدد' }}">
                                {{ $employee->first_name_ar ?: $employee->first_name }} {{ $employee->last_name_ar ?: $employee->last_name }}
                                ({{ $employee->employee_id }})
                            </option>
                        @empty
                            <option value="" disabled>لا توجد موظفين متاحين</option>
                        @endforelse
                    </select>
                </div>

                <!-- Pay Period Start -->
                <div>
                    <label for="pay_period_start" class="block text-sm font-medium text-gray-700 mb-2">
                        بداية فترة الراتب <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="pay_period_start" 
                           name="pay_period_start" 
                           value="{{ old('pay_period_start', $pay_period_start) }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Pay Period End -->
                <div>
                    <label for="pay_period_end" class="block text-sm font-medium text-gray-700 mb-2">
                        نهاية فترة الراتب <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="pay_period_end" 
                           name="pay_period_end" 
                           value="{{ old('pay_period_end', $pay_period_end) }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Employee Info Display -->
            <div id="employee-info" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 mb-2">معلومات الموظف:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-blue-700">القسم:</span>
                        <span id="employee-department" class="text-blue-600"></span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-700">المنصب:</span>
                        <span id="employee-position" class="text-blue-600"></span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-700">الراتب الأساسي:</span>
                        <span id="employee-salary" class="text-blue-600"></span> دينار عراقي
                    </div>
                </div>
            </div>

            <!-- Salary Components -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Earnings -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-green-700 border-b border-green-200 pb-2">الاستحقاقات</h4>
                    
                    <!-- Basic Salary -->
                    <div>
                        <label for="basic_salary" class="block text-sm font-medium text-gray-700 mb-2">
                            الراتب الأساسي <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="basic_salary"
                               name="basic_salary"
                               step="0.01"
                               min="0"
                               value="{{ old('basic_salary') }}"
                               required
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Allowances -->
                    <div>
                        <label for="allowances" class="block text-sm font-medium text-gray-700 mb-2">
                            البدلات
                        </label>
                        <input type="number" 
                               id="allowances" 
                               name="allowances" 
                               step="0.01"
                               min="0"
                               value="0"
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Bonuses -->
                    <div>
                        <label for="bonuses" class="block text-sm font-medium text-gray-700 mb-2">
                            المكافآت
                        </label>
                        <input type="number" 
                               id="bonuses" 
                               name="bonuses" 
                               step="0.01"
                               min="0"
                               value="0"
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Overtime -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                ساعات إضافية
                            </label>
                            <input type="number" 
                                   id="overtime_hours" 
                                   name="overtime_hours" 
                                   step="0.5"
                                   min="0"
                                   value="0"
                                   onchange="calculateTotals()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="overtime_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                أجر الساعة الإضافية
                            </label>
                            <input type="number" 
                                   id="overtime_rate" 
                                   name="overtime_rate" 
                                   step="0.01"
                                   min="0"
                                   value="0"
                                   onchange="calculateTotals()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-red-700 border-b border-red-200 pb-2">الاستقطاعات</h4>
                    
                    <!-- Tax Deduction -->
                    <div>
                        <label for="tax_deduction" class="block text-sm font-medium text-gray-700 mb-2">
                            ضريبة الدخل
                        </label>
                        <input type="number" 
                               id="tax_deduction" 
                               name="tax_deduction" 
                               step="0.01"
                               min="0"
                               value="0"
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="calculateTax()" class="mt-1 text-xs text-blue-600 hover:text-blue-800">
                            حساب تلقائي
                        </button>
                    </div>

                    <!-- Social Security -->
                    <div>
                        <label for="social_security_deduction" class="block text-sm font-medium text-gray-700 mb-2">
                            الضمان الاجتماعي
                        </label>
                        <input type="number" 
                               id="social_security_deduction" 
                               name="social_security_deduction" 
                               step="0.01"
                               min="0"
                               value="0"
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="calculateSocialSecurity()" class="mt-1 text-xs text-blue-600 hover:text-blue-800">
                            حساب تلقائي (5%)
                        </button>
                    </div>

                    <!-- Health Insurance -->
                    <div>
                        <label for="health_insurance_deduction" class="block text-sm font-medium text-gray-700 mb-2">
                            التأمين الصحي
                        </label>
                        <input type="number" 
                               id="health_insurance_deduction" 
                               name="health_insurance_deduction" 
                               step="0.01"
                               min="0"
                               value="0"
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="calculateHealthInsurance()" class="mt-1 text-xs text-blue-600 hover:text-blue-800">
                            حساب تلقائي (2%)
                        </button>
                    </div>

                    <!-- Other Deductions -->
                    <div>
                        <label for="other_deductions" class="block text-sm font-medium text-gray-700 mb-2">
                            استقطاعات أخرى
                        </label>
                        <input type="number" 
                               id="other_deductions" 
                               name="other_deductions" 
                               step="0.01"
                               min="0"
                               value="0"
                               onchange="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">ملخص كشف الراتب</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">إجمالي الاستحقاقات</p>
                        <p id="total-earnings" class="text-2xl font-bold text-green-600">0.00</p>
                        <p class="text-xs text-gray-500">دينار عراقي</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">إجمالي الاستقطاعات</p>
                        <p id="total-deductions" class="text-2xl font-bold text-red-600">0.00</p>
                        <p class="text-xs text-gray-500">دينار عراقي</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">صافي الراتب</p>
                        <p id="net-salary" class="text-3xl font-bold text-blue-600">0.00</p>
                        <p class="text-xs text-gray-500">دينار عراقي</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    ملاحظات
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="ملاحظات إضافية حول كشف الراتب"></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-3 space-x-reverse pt-6 border-t border-gray-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save ml-2"></i>
                    حفظ كشف الراتب
                </button>
                <a href="{{ route('hr.payroll.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadEmployeeData(employeeId) {
    const select = document.getElementById('employee_id');
    const option = select.options[select.selectedIndex];

    if (employeeId && option && option.value) {
        const salary = parseFloat(option.dataset.salary) || 0;
        const department = option.dataset.department || 'غير محدد';
        const position = option.dataset.position || 'غير محدد';

        // Show employee info
        document.getElementById('employee-info').classList.remove('hidden');
        document.getElementById('employee-department').textContent = department;
        document.getElementById('employee-position').textContent = position;
        document.getElementById('employee-salary').textContent = salary.toLocaleString();

        // Set basic salary
        document.getElementById('basic_salary').value = salary;

        // Calculate overtime rate (assuming 160 working hours per month)
        if (salary > 0) {
            const overtimeRate = salary / 160;
            document.getElementById('overtime_rate').value = overtimeRate.toFixed(2);
        } else {
            document.getElementById('overtime_rate').value = '0';
        }

        calculateTotals();
    } else {
        document.getElementById('employee-info').classList.add('hidden');
        document.getElementById('basic_salary').value = '0';
        document.getElementById('overtime_rate').value = '0';
        calculateTotals();
    }
}

function calculateTotals() {
    const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
    const allowances = parseFloat(document.getElementById('allowances').value) || 0;
    const bonuses = parseFloat(document.getElementById('bonuses').value) || 0;
    const overtimeHours = parseFloat(document.getElementById('overtime_hours').value) || 0;
    const overtimeRate = parseFloat(document.getElementById('overtime_rate').value) || 0;
    
    const taxDeduction = parseFloat(document.getElementById('tax_deduction').value) || 0;
    const socialSecurity = parseFloat(document.getElementById('social_security_deduction').value) || 0;
    const healthInsurance = parseFloat(document.getElementById('health_insurance_deduction').value) || 0;
    const otherDeductions = parseFloat(document.getElementById('other_deductions').value) || 0;
    
    const overtimePay = overtimeHours * overtimeRate;
    const totalEarnings = basicSalary + allowances + bonuses + overtimePay;
    const totalDeductions = taxDeduction + socialSecurity + healthInsurance + otherDeductions;
    const netSalary = totalEarnings - totalDeductions;
    
    document.getElementById('total-earnings').textContent = totalEarnings.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('total-deductions').textContent = totalDeductions.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('net-salary').textContent = netSalary.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function calculateTax() {
    const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
    const allowances = parseFloat(document.getElementById('allowances').value) || 0;
    const bonuses = parseFloat(document.getElementById('bonuses').value) || 0;
    const overtimeHours = parseFloat(document.getElementById('overtime_hours').value) || 0;
    const overtimeRate = parseFloat(document.getElementById('overtime_rate').value) || 0;
    
    const grossSalary = basicSalary + allowances + bonuses + (overtimeHours * overtimeRate);
    
    let tax = 0;
    if (grossSalary <= 250000) {
        tax = 0;
    } else if (grossSalary <= 500000) {
        tax = (grossSalary - 250000) * 0.03;
    } else if (grossSalary <= 1000000) {
        tax = 7500 + (grossSalary - 500000) * 0.05;
    } else {
        tax = 32500 + (grossSalary - 1000000) * 0.15;
    }
    
    document.getElementById('tax_deduction').value = tax.toFixed(2);
    calculateTotals();
}

function calculateSocialSecurity() {
    const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
    const socialSecurity = basicSalary * 0.05;
    document.getElementById('social_security_deduction').value = socialSecurity.toFixed(2);
    calculateTotals();
}

function calculateHealthInsurance() {
    const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
    const healthInsurance = basicSalary * 0.02;
    document.getElementById('health_insurance_deduction').value = healthInsurance.toFixed(2);
    calculateTotals();
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
@endpush
