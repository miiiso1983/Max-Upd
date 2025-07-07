@extends('layouts.app')

@section('title', 'إدارة الموظفين')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إدارة الموظفين</h1>
            <p class="text-gray-600 mt-1">إدارة بيانات الموظفين والمعلومات الشخصية</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="exportEmployees()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-download ml-2"></i>
                تصدير Excel
            </button>
            <button onclick="openImportModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-upload ml-2"></i>
                رفع ملف Excel
            </button>
            <button onclick="downloadTemplate()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-file-excel ml-2"></i>
                تحميل النموذج
            </button>
            <a href="{{ route('hr.employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-plus ml-2"></i>
                إضافة موظف جديد
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="البحث بالاسم، الرقم الوظيفي، أو البريد الإلكتروني" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">القسم</label>
                <select id="department_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الأقسام</option>
                    @foreach($filters['departments'] as $department)
                        <option value="{{ $department->id }}">{{ $department->name_ar }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select id="status_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    @foreach($filters['statuses_ar'] as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Employment Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التوظيف</label>
                <select id="employment_type_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الأنواع</option>
                    @foreach($filters['employment_types_ar'] as $key => $type)
                        <option value="{{ $key }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-search ml-2"></i>
                تطبيق الفلاتر
            </button>
            <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 mr-2">
                <i class="fas fa-times ml-2"></i>
                مسح الفلاتر
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">إجمالي الموظفين</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-employees">{{ $employees->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">الموظفين النشطين</p>
                    <p class="text-2xl font-bold text-gray-900" id="active-employees">{{ $employees->where('status', 'active')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">عدد الأقسام</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $filters['departments']->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-user-plus text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">موظفين جدد هذا الشهر</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $employees->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرقم الوظيفي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">القسم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنصب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ التوظيف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="employees-table-body">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($employee->profile_photo)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->full_name_ar }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="mr-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->full_name_ar }}</div>
                                    <div class="text-sm text-gray-500">{{ $employee->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $employee->employee_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $employee->department->name_ar ?? 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $employee->position->title_ar ?? 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $employee->hire_date ? $employee->hire_date->format('Y/m/d') : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($employee->status === 'active')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    نشط
                                </span>
                            @elseif($employee->status === 'inactive')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    غير نشط
                                </span>
                            @elseif($employee->status === 'terminated')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    منتهي الخدمة
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ $filters['statuses_ar'][$employee->status] ?? $employee->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('hr.employees.show', $employee) }}" class="text-blue-600 hover:text-blue-900 transition duration-200">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('hr.employees.edit', $employee) }}" class="text-yellow-600 hover:text-yellow-900 transition duration-200">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-employee-id="{{ $employee->id }}" onclick="deleteEmployee(this.dataset.employeeId)" class="text-red-600 hover:text-red-900 transition duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $employees->links() }}
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">رفع ملف الموظفين</h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="py-6">
                <form id="importForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Instructions -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">تعليمات الرفع:</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls)</li>
                            <li>• يجب أن تحتوي الورقة الأولى على بيانات الموظفين</li>
                            <li>• الصف الأول يجب أن يحتوي على عناوين الأعمدة</li>
                            <li>• يمكنك تحميل النموذج للحصول على التنسيق الصحيح</li>
                            <li>• الحقول المطلوبة: الاسم الأول، الاسم الأخير، البريد الإلكتروني، تاريخ التوظيف</li>
                        </ul>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-6">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                            اختر ملف Excel <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-excel text-4xl text-green-500 mb-3"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>اختر ملف</span>
                                        <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls" class="sr-only" required>
                                    </label>
                                    <p class="pr-1">أو اسحب الملف هنا</p>
                                </div>
                                <p class="text-xs text-gray-500">Excel files only (.xlsx, .xls)</p>
                            </div>
                        </div>
                        <div id="file-info" class="mt-2 text-sm text-gray-600 hidden"></div>
                    </div>

                    <!-- Options -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">خيارات الاستيراد:</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="update_existing" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-700">تحديث الموظفين الموجودين (بناءً على البريد الإلكتروني)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="send_welcome_email" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-700">إرسال بريد ترحيبي للموظفين الجدد</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="validate_only" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-700">التحقق من البيانات فقط (بدون حفظ)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="upload-progress" class="mb-4 hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">جاري الرفع...</span>
                            <span id="progress-percentage" class="text-sm text-gray-500">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Results -->
                    <div id="import-results" class="hidden"></div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end pt-4 border-t space-x-3 space-x-reverse">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    إلغاء
                </button>
                <button type="button" onclick="downloadTemplate()" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors">
                    <i class="fas fa-download ml-1"></i>
                    تحميل النموذج
                </button>
                <button type="button" onclick="submitImport()" id="import-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-upload ml-1"></i>
                    رفع الملف
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('search').value;
    const department = document.getElementById('department_filter').value;
    const status = document.getElementById('status_filter').value;
    const employmentType = document.getElementById('employment_type_filter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (department) params.append('department_id', department);
    if (status) params.append('status', status);
    if (employmentType) params.append('employment_type', employmentType);
    
    window.location.href = '{{ route("hr.employees.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("hr.employees.index") }}';
}

function deleteEmployee(id) {
    if (confirm('هل أنت متأكد من حذف هذا الموظف؟\n\nتحذير: هذا الإجراء لا يمكن التراجع عنه.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/hr/employees/${id}`;

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}

function exportEmployees() {
    window.location.href = '{{ route("hr.employees.index") }}?export=excel';
}

// Import Modal Functions
function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
    document.body.style.overflow = 'auto';

    // Reset form
    document.getElementById('importForm').reset();
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('upload-progress').classList.add('hidden');
    document.getElementById('import-results').classList.add('hidden');
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-percentage').textContent = '0%';
}

function downloadTemplate() {
    window.location.href = '{{ route("hr.employees.template") }}';
}

function submitImport() {
    const fileInput = document.getElementById('excel_file');
    const file = fileInput.files[0];

    if (!file) {
        alert('يرجى اختيار ملف Excel أولاً');
        return;
    }

    // Validate file type
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel'
    ];

    if (!allowedTypes.includes(file.type)) {
        alert('يرجى اختيار ملف Excel صحيح (.xlsx أو .xls)');
        return;
    }

    const formData = new FormData(document.getElementById('importForm'));
    const importBtn = document.getElementById('import-btn');
    const progressContainer = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    const resultsContainer = document.getElementById('import-results');

    // Show progress bar
    progressContainer.classList.remove('hidden');
    resultsContainer.classList.add('hidden');

    // Disable import button
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i> جاري الرفع...';

    // Create XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percentComplete + '%';
            progressPercentage.textContent = percentComplete + '%';
        }
    });

    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                showImportResults(response);
            } catch (e) {
                showImportError('خطأ في معالجة الاستجابة من الخادم');
            }
        } else {
            try {
                const response = JSON.parse(xhr.responseText);
                showImportError(response.message || 'حدث خطأ أثناء رفع الملف');
            } catch (e) {
                showImportError('حدث خطأ أثناء رفع الملف');
            }
        }

        // Re-enable import button
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-upload ml-1"></i> رفع الملف';
    });

    xhr.addEventListener('error', function() {
        showImportError('حدث خطأ في الاتصال بالخادم');
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-upload ml-1"></i> رفع الملف';
    });

    xhr.open('POST', '{{ route("hr.employees.import") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    xhr.send(formData);
}

function showImportResults(response) {
    const resultsContainer = document.getElementById('import-results');
    let html = '';

    if (response.success) {
        html = `
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-check-circle text-green-500 ml-2"></i>
                    <h4 class="font-semibold text-green-800">تم الرفع بنجاح!</h4>
                </div>
                <div class="text-sm text-green-700">
                    <p>• تم إضافة ${response.created || 0} موظف جديد</p>
                    <p>• تم تحديث ${response.updated || 0} موظف موجود</p>
                    ${response.skipped ? `<p>• تم تجاهل ${response.skipped} سجل</p>` : ''}
                </div>
            </div>
        `;

        // Refresh page after 2 seconds
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    } else {
        html = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                    <h4 class="font-semibold text-red-800">فشل في الرفع</h4>
                </div>
                <div class="text-sm text-red-700">
                    <p>${response.message}</p>
                    ${response.errors ? `
                        <div class="mt-2">
                            <p class="font-medium">الأخطاء:</p>
                            <ul class="list-disc list-inside">
                                ${response.errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    resultsContainer.innerHTML = html;
    resultsContainer.classList.remove('hidden');
}

function showImportError(message) {
    const resultsContainer = document.getElementById('import-results');
    resultsContainer.innerHTML = `
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                <p class="text-red-700">${message}</p>
            </div>
        </div>
    `;
    resultsContainer.classList.remove('hidden');
}

// File input change handler
document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('file-info');

    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        fileInfo.innerHTML = `
            <div class="flex items-center text-green-600">
                <i class="fas fa-file-excel ml-1"></i>
                <span>${file.name} (${fileSize} MB)</span>
            </div>
        `;
        fileInfo.classList.remove('hidden');
    } else {
        fileInfo.classList.add('hidden');
    }
});

// Auto-apply filters on input change
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(applyFilters, 500);
});
</script>
@endsection
