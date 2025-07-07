@extends('layouts.app')

@section('title', 'تفاصيل الموظف - ' . $employee->full_name_ar)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">تفاصيل الموظف</h1>
            <p class="text-gray-600 mt-1">عرض معلومات الموظف التفصيلية</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('hr.employees.edit', $employee) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-edit ml-2"></i>
                تعديل
            </a>
            <a href="{{ route('hr.employees.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Employee Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center">
                    @if($employee->profile_photo)
                        <img class="mx-auto h-32 w-32 rounded-full object-cover" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->full_name_ar }}">
                    @else
                        <div class="mx-auto h-32 w-32 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-gray-600"></i>
                        </div>
                    @endif
                    <h3 class="mt-4 text-xl font-bold text-gray-900">{{ $employee->full_name_ar }}</h3>
                    <p class="text-gray-600">{{ $employee->full_name }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $employee->employee_id }}</p>
                    
                    <!-- Status Badge -->
                    <div class="mt-4">
                        @if($employee->status === 'active')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle ml-1"></i>
                                نشط
                            </span>
                        @elseif($employee->status === 'inactive')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle ml-1"></i>
                                غير نشط
                            </span>
                        @elseif($employee->status === 'terminated')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                <i class="fas fa-user-times ml-1"></i>
                                منتهي الخدمة
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-6 border-t pt-6">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($employee->age) }}</p>
                            <p class="text-sm text-gray-600">العمر</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($employee->years_of_service, 1) }}</p>
                            <p class="text-sm text-gray-600">سنوات الخدمة</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mt-6 border-t pt-6">
                    <h4 class="font-semibold text-gray-900 mb-3">معلومات الاتصال</h4>
                    <div class="space-y-2">
                        @if($employee->email)
                            <div class="flex items-center text-sm">
                                <i class="fas fa-envelope text-gray-400 w-4"></i>
                                <span class="mr-2">{{ $employee->email }}</span>
                            </div>
                        @endif
                        @if($employee->phone)
                            <div class="flex items-center text-sm">
                                <i class="fas fa-phone text-gray-400 w-4"></i>
                                <span class="mr-2">{{ $employee->phone }}</span>
                            </div>
                        @endif
                        @if($employee->mobile)
                            <div class="flex items-center text-sm">
                                <i class="fas fa-mobile-alt text-gray-400 w-4"></i>
                                <span class="mr-2">{{ $employee->mobile }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الشخصية</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">تاريخ الميلاد</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->date_of_birth ? $employee->date_of_birth->format('Y/m/d') : 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">الجنس</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->gender === 'male' ? 'ذكر' : ($employee->gender === 'female' ? 'أنثى' : 'غير محدد') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">الحالة الاجتماعية</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @switch($employee->marital_status)
                                @case('single') أعزب @break
                                @case('married') متزوج @break
                                @case('divorced') مطلق @break
                                @case('widowed') أرمل @break
                                @default غير محدد
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">الجنسية</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->nationality ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">رقم الهوية الوطنية</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->national_id ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">رقم جواز السفر</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->passport_number ?? 'غير محدد' }}</p>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات التوظيف</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">القسم</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->department->name_ar ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">المنصب</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->position->title_ar ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">المدير المباشر</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->manager->full_name_ar ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">تاريخ التوظيف</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->hire_date ? $employee->hire_date->format('Y/m/d') : 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">نوع التوظيف</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @switch($employee->employment_type)
                                @case('full_time') دوام كامل @break
                                @case('part_time') دوام جزئي @break
                                @case('contract') عقد @break
                                @case('temporary') مؤقت @break
                                @default غير محدد
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">الراتب الأساسي</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($employee->basic_salary)
                                {{ number_format($employee->basic_salary) }} {{ $employee->currency ?? 'IQD' }}
                            @else
                                غير محدد
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات العنوان</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">العنوان</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->address ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">المدينة</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->city ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">المحافظة</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->governorate ?? 'غير محدد' }}</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            @if($employee->emergency_contact_name)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">جهة الاتصال في حالات الطوارئ</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">الاسم</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->emergency_contact_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">رقم الهاتف</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->emergency_contact_phone ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">صلة القرابة</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->emergency_contact_relationship ?? 'غير محدد' }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Attendance Summary -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ملخص الحضور</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ $attendanceSummary['total_days'] }}</p>
                        <p class="text-sm text-gray-600">إجمالي الأيام</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ $attendanceSummary['present_days'] }}</p>
                        <p class="text-sm text-gray-600">أيام الحضور</p>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <p class="text-2xl font-bold text-yellow-600">{{ $attendanceSummary['late_days'] }}</p>
                        <p class="text-sm text-gray-600">أيام التأخير</p>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <p class="text-2xl font-bold text-red-600">{{ $attendanceSummary['absent_days'] }}</p>
                        <p class="text-sm text-gray-600">أيام الغياب</p>
                    </div>
                </div>
            </div>

            <!-- Leave Balance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">رصيد الإجازات</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-600">{{ $leaveBalance['annual_leave_taken'] }}</p>
                        <p class="text-sm text-gray-600">إجازة سنوية مستخدمة</p>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <p class="text-2xl font-bold text-orange-600">{{ $leaveBalance['sick_leave_taken'] }}</p>
                        <p class="text-sm text-gray-600">إجازة مرضية مستخدمة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
