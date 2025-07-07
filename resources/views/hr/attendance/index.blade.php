@extends('layouts.app')

@section('title', 'إدارة الحضور والغياب')

@push('styles')
<style>
.searchable-dropdown {
    position: relative;
    width: 100%;
}

.searchable-dropdown .dropdown-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 42px;
}

.searchable-dropdown .dropdown-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px #3b82f6;
    border-color: #3b82f6;
}

.searchable-dropdown .dropdown-input .placeholder {
    color: #9ca3af;
}

.searchable-dropdown .dropdown-input .selected-text {
    color: #111827;
}

.searchable-dropdown .dropdown-arrow {
    transition: transform 0.2s;
}

.searchable-dropdown.open .dropdown-arrow {
    transform: rotate(180deg);
}

.searchable-dropdown .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    max-height: 300px;
    overflow: hidden;
    display: none;
}

.searchable-dropdown.open .dropdown-menu {
    display: block;
}

.searchable-dropdown .search-input {
    width: 100%;
    padding: 8px 12px;
    border: none;
    border-bottom: 1px solid #e5e7eb;
    outline: none;
    font-size: 14px;
}

.searchable-dropdown .search-input:focus {
    border-bottom-color: #3b82f6;
}

.searchable-dropdown .dropdown-options {
    max-height: 200px;
    overflow-y: auto;
}

.searchable-dropdown .dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s;
}

.searchable-dropdown .dropdown-option:hover {
    background-color: #f3f4f6;
}

.searchable-dropdown .dropdown-option.selected {
    background-color: #dbeafe;
    color: #1e40af;
}

.searchable-dropdown .dropdown-option:last-child {
    border-bottom: none;
}

.searchable-dropdown .no-options {
    padding: 12px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
}
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إدارة الحضور والغياب</h1>
            <p class="text-gray-600 mt-1">متابعة حضور وغياب الموظفين</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button onclick="exportAttendance()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-download ml-2"></i>
                تصدير Excel
            </button>
            <button onclick="showCheckInModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-clock ml-2"></i>
                تسجيل حضور
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">حاضر اليوم</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['present_today'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">متأخر اليوم</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['late_today'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-user-times text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">غائب اليوم</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['absent_today'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-percentage text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">نسبة الحضور</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-business-time text-xl"></i>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-600">متوسط ساعات العمل</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_work_hours'] ?? 0, 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" id="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" id="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Employee Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الموظف</label>
                <div class="searchable-dropdown" data-name="employee_filter" data-value="{{ request('employee_id') }}">
                    <div class="dropdown-input" onclick="toggleDropdown(this)">
                        <span class="selected-text placeholder">جميع الموظفين</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="dropdown-menu">
                        <input type="text" class="search-input" placeholder="ابحث عن موظف..." onkeyup="filterOptions(this)">
                        <div class="dropdown-options">
                            <div class="dropdown-option" data-value="" onclick="selectOption(this)">جميع الموظفين</div>
                            @foreach($employees as $employee)
                                <div class="dropdown-option" data-value="{{ $employee->id }}" onclick="selectOption(this)">
                                    {{ $employee->full_name_ar }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <div class="searchable-dropdown" data-name="status_filter" data-value="{{ request('status') }}">
                    <div class="dropdown-input" onclick="toggleDropdown(this)">
                        <span class="selected-text placeholder">جميع الحالات</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="dropdown-menu">
                        <input type="text" class="search-input" placeholder="ابحث عن حالة..." onkeyup="filterOptions(this)">
                        <div class="dropdown-options">
                            <div class="dropdown-option" data-value="" onclick="selectOption(this)">جميع الحالات</div>
                            <div class="dropdown-option" data-value="present" onclick="selectOption(this)">حاضر</div>
                            <div class="dropdown-option" data-value="late" onclick="selectOption(this)">متأخر</div>
                            <div class="dropdown-option" data-value="absent" onclick="selectOption(this)">غائب</div>
                            <div class="dropdown-option" data-value="half_day" onclick="selectOption(this)">نصف يوم</div>
                        </div>
                    </div>
                </div>
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

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت الدخول</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت الخروج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ساعات العمل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ملاحظات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendance as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($record->employee->profile_photo)
                                    <img class="h-8 w-8 rounded-full object-cover ml-2" src="{{ asset('storage/' . $record->employee->profile_photo) }}" alt="{{ $record->employee->full_name_ar }}">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center ml-2">
                                        <i class="fas fa-user text-gray-600 text-xs"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $record->employee->full_name_ar }}</div>
                                    <div class="text-xs text-gray-500">{{ $record->employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->date->format('Y/m/d') }}
                            <div class="text-xs text-gray-500">{{ $record->date->format('l') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->check_in_time)
                                <span class="font-mono">{{ $record->check_in_time->format('H:i') }}</span>
                                @if($record->check_in_time->format('H:i') > '09:00')
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-1" title="متأخر"></i>
                                @endif
                            @else
                                <span class="text-gray-400">لم يسجل</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->check_out_time)
                                <span class="font-mono">{{ $record->check_out_time->format('H:i') }}</span>
                            @else
                                <span class="text-gray-400">لم يسجل</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->total_hours)
                                <span class="font-mono">{{ number_format($record->total_hours, 1) }} ساعة</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($record->status)
                                @case('present')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle ml-1"></i>
                                        حاضر
                                    </span>
                                    @break
                                @case('late')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock ml-1"></i>
                                        متأخر
                                    </span>
                                    @break
                                @case('absent')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle ml-1"></i>
                                        غائب
                                    </span>
                                    @break
                                @case('half_day')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-user-clock ml-1"></i>
                                        نصف يوم
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $record->status }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($record->notes)
                                <span title="{{ $record->notes }}">{{ Str::limit($record->notes, 30) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <button data-record-id="{{ $record->id }}"
                                        onclick="editAttendance(this.dataset.recordId)"
                                        class="text-yellow-600 hover:text-yellow-900 transition duration-200" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if(!$record->check_out_time && $record->status !== 'absent')
                                    <button data-record-id="{{ $record->id }}"
                                            onclick="checkOut(this.dataset.recordId)"
                                            class="text-blue-600 hover:text-blue-900 transition duration-200" title="تسجيل خروج">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                @endif
                                <button data-record-id="{{ $record->id }}"
                                        onclick="deleteAttendance(this.dataset.recordId)"
                                        class="text-red-600 hover:text-red-900 transition duration-200" title="حذف">
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
            {{ $attendance->links() }}
        </div>
    </div>
</div>

<!-- Check-in Modal -->
<div id="checkInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">تسجيل حضور</h3>
            <form id="checkInForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الموظف</label>
                    <x-searchable-dropdown
                        name="checkInEmployee"
                        placeholder="اختر الموظف"
                        search-placeholder="ابحث عن موظف..."
                        :options="$employees->map(function($employee) {
                            return [
                                'value' => $employee->id,
                                'text' => $employee->full_name_ar
                            ];
                        })->toArray()"
                        required
                    />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">وقت الدخول</label>
                    <input type="time" id="checkInTime" value="{{ now()->format('H:i') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <textarea id="checkInNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-2 space-x-reverse">
                    <button type="button" onclick="hideCheckInModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        إلغاء
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        تسجيل الحضور
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Searchable Dropdown Functions
function toggleDropdown(element) {
    const dropdown = element.closest('.searchable-dropdown');
    const isOpen = dropdown.classList.contains('open');

    // Close all other dropdowns
    document.querySelectorAll('.searchable-dropdown.open').forEach(dd => {
        dd.classList.remove('open');
    });

    // Toggle current dropdown
    if (!isOpen) {
        dropdown.classList.add('open');
        const searchInput = dropdown.querySelector('.search-input');
        setTimeout(() => searchInput.focus(), 100);
    }
}

function selectOption(element) {
    const dropdown = element.closest('.searchable-dropdown');
    const selectedText = dropdown.querySelector('.selected-text');
    const value = element.getAttribute('data-value');
    const text = element.textContent;

    // Update display
    selectedText.textContent = text;
    selectedText.classList.remove('placeholder');
    if (!value) {
        selectedText.classList.add('placeholder');
    }

    // Store value
    dropdown.setAttribute('data-value', value);

    // Update selected state
    dropdown.querySelectorAll('.dropdown-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    element.classList.add('selected');

    // Close dropdown
    dropdown.classList.remove('open');

    // Clear search
    const searchInput = dropdown.querySelector('.search-input');
    searchInput.value = '';
    filterOptions(searchInput);
}

function filterOptions(searchInput) {
    const searchTerm = searchInput.value.toLowerCase();
    const dropdown = searchInput.closest('.searchable-dropdown');
    const options = dropdown.querySelectorAll('.dropdown-option');
    let hasVisibleOptions = false;

    options.forEach(option => {
        const text = option.textContent.toLowerCase();
        const isVisible = text.includes(searchTerm);
        option.style.display = isVisible ? 'block' : 'none';
        if (isVisible) hasVisibleOptions = true;
    });

    // Show/hide no options message
    let noOptionsMsg = dropdown.querySelector('.no-options');
    if (!hasVisibleOptions && searchTerm) {
        if (!noOptionsMsg) {
            noOptionsMsg = document.createElement('div');
            noOptionsMsg.className = 'no-options';
            noOptionsMsg.textContent = 'لا توجد نتائج';
            dropdown.querySelector('.dropdown-options').appendChild(noOptionsMsg);
        }
        noOptionsMsg.style.display = 'block';
    } else if (noOptionsMsg) {
        noOptionsMsg.style.display = 'none';
    }
}

function getDropdownValue(name) {
    const dropdown = document.querySelector(`[data-name="${name}"]`);
    return dropdown ? dropdown.getAttribute('data-value') : '';
}

// Initialize dropdowns with selected values
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.searchable-dropdown').forEach(dropdown => {
        const value = dropdown.getAttribute('data-value');
        if (value) {
            const option = dropdown.querySelector(`[data-value="${value}"]`);
            if (option) {
                selectOption(option);
            }
        }
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.searchable-dropdown')) {
        document.querySelectorAll('.searchable-dropdown.open').forEach(dd => {
            dd.classList.remove('open');
        });
    }
});

function applyFilters() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    const employee = getDropdownValue('employee_filter');
    const status = getDropdownValue('status_filter');
    
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (employee) params.append('employee_id', employee);
    if (status) params.append('status', status);
    
    window.location.href = '{{ route("hr.attendance.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("hr.attendance.index") }}';
}

function showCheckInModal() {
    document.getElementById('checkInModal').classList.remove('hidden');
}

function hideCheckInModal() {
    document.getElementById('checkInModal').classList.add('hidden');
}

function exportAttendance() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("hr.attendance.index") }}?' + params.toString();
}

// Handle check-in form submission
document.getElementById('checkInForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const employeeId = getDropdownValue('checkInEmployee');

    if (!employeeId) {
        alert('يرجى اختيار الموظف');
        return;
    }

    const formData = {
        employee_id: employeeId,
        check_in_time: document.getElementById('checkInTime').value,
        notes: document.getElementById('checkInNotes').value,
        _token: '{{ csrf_token() }}'
    };

    fetch('{{ route("hr.attendance.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideCheckInModal();
            // Reset form and dropdown
            document.getElementById('checkInForm').reset();
            setDropdownValue('checkInEmployee', '');
            location.reload();
        } else {
            alert('حدث خطأ أثناء تسجيل الحضور');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تسجيل الحضور');
    });
});

// Delete attendance record
function deleteAttendance(attendanceId) {
    if (confirm('هل أنت متأكد من حذف سجل الحضور هذا؟\nلا يمكن التراجع عن هذا الإجراء.')) {
        fetch(`/hr/attendance/${attendanceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                if (typeof MaxCon !== 'undefined') {
                    MaxCon.showNotification(data.message || 'تم حذف سجل الحضور بنجاح', 'success');
                } else {
                    alert(data.message || 'تم حذف سجل الحضور بنجاح');
                }

                // Reload the page to update the list
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                if (typeof MaxCon !== 'undefined') {
                    MaxCon.showNotification(data.message || 'حدث خطأ أثناء حذف سجل الحضور', 'error');
                } else {
                    alert(data.message || 'حدث خطأ أثناء حذف سجل الحضور');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof MaxCon !== 'undefined') {
                MaxCon.showNotification('حدث خطأ في الشبكة', 'error');
            } else {
                alert('حدث خطأ في الشبكة');
            }
        });
    }
}
</script>
@endsection
