@extends('layouts.app')

@section('title', 'استيراد العملاء من Excel')
@section('page-title', 'استيراد العملاء')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">استيراد العملاء من Excel</h1>
            <p class="text-gray-600 mt-1">رفع ملف Excel لإضافة عملاء متعددين دفعة واحدة</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.customers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للعملاء
            </a>
            <a href="{{ route('sales.customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                إضافة عميل يدوياً
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle ml-2"></i>
                <span>{{ session('success') }}</span>
            </div>
            
            @if(session('stats'))
                <div class="mt-3 text-sm">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-green-50 p-2 rounded">
                            <span class="font-semibold text-green-800">تم الاستيراد:</span>
                            <span class="text-green-600">{{ session('stats')['imported'] }}</span>
                        </div>
                        <div class="bg-yellow-50 p-2 rounded">
                            <span class="font-semibold text-yellow-800">مكرر:</span>
                            <span class="text-yellow-600">{{ session('stats')['duplicates'] }}</span>
                        </div>
                        <div class="bg-red-50 p-2 rounded">
                            <span class="font-semibold text-red-800">أخطاء:</span>
                            <span class="text-red-600">{{ session('stats')['errors'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle ml-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('errors') && count(session('errors')) > 0)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-triangle ml-2"></i>
                <span class="font-semibold">الأخطاء التالية تحتاج إلى إصلاح:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 max-h-40 overflow-y-auto">
                @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Download Template Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start space-x-4 space-x-reverse">
            <div class="flex-shrink-0">
                <i class="fas fa-download text-blue-600 text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">تحميل قالب Excel</h3>
                <p class="text-blue-700 mb-4">
                    قم بتحميل قالب Excel الجاهز والذي يحتوي على جميع الأعمدة المطلوبة مع بيانات نموذجية وقوائم منسدلة للتسهيل عليك.
                </p>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="{{ route('sales.customers.import.template') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                        <i class="fas fa-file-excel ml-2"></i>
                        تحميل قالب العملاء
                    </a>
                    <span class="text-sm text-blue-600">
                        <i class="fas fa-info-circle ml-1"></i>
                        يحتوي على 21 عمود مع بيانات نموذجية
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-yellow-900 mb-3">
            <i class="fas fa-lightbulb ml-2"></i>
            تعليمات مهمة
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-yellow-800">
            <div>
                <h4 class="font-semibold mb-2">الحقول الإجبارية (مميزة بالأصفر):</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>اسم العميل</li>
                    <li>نوع العميل (فرد، صيدلية، عيادة، مستشفى، موزع، حكومي)</li>
                    <li>الحالة (نشط، غير نشط)</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-2">نصائح للاستيراد الناجح:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>استخدم القوائم المنسدلة للحقول المحددة</li>
                    <li>تأكد من صحة البريد الإلكتروني</li>
                    <li>الأرقام يجب أن تكون أرقام فقط</li>
                    <li>لا تحذف أو تعدل أسماء الأعمدة</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-upload ml-2"></i>
            رفع ملف Excel
        </h3>
        
        <form action="{{ route('sales.customers.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    اختر ملف Excel
                </label>
                <div class="flex items-center justify-center w-full">
                    <label for="excel_file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                            <p class="mb-2 text-sm text-gray-500">
                                <span class="font-semibold">اضغط لرفع الملف</span> أو اسحب الملف هنا
                            </p>
                            <p class="text-xs text-gray-500">Excel files only (MAX. 10MB)</p>
                        </div>
                        <input id="excel_file" name="excel_file" type="file" class="hidden" accept=".xlsx,.xls" required />
                    </label>
                </div>
                @error('excel_file')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3 space-x-reverse">
                <button type="button" onclick="document.getElementById('excel_file').value=''" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    إلغاء
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                    <i class="fas fa-upload ml-2"></i>
                    استيراد العملاء
                </button>
            </div>
        </form>
    </div>

    <!-- File Format Info -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">
            <i class="fas fa-table ml-2"></i>
            تفاصيل أعمدة الملف
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-800">المعلومات الأساسية:</h4>
                <ul class="text-gray-600 space-y-1">
                    <li>• اسم العميل (مطلوب)</li>
                    <li>• الاسم بالعربية</li>
                    <li>• نوع العميل (مطلوب)</li>
                    <li>• رمز العميل</li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-800">معلومات الاتصال:</h4>
                <ul class="text-gray-600 space-y-1">
                    <li>• البريد الإلكتروني</li>
                    <li>• رقم الهاتف</li>
                    <li>• رقم الموبايل</li>
                    <li>• العنوان والمدينة</li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-800">المعلومات التجارية:</h4>
                <ul class="text-gray-600 space-y-1">
                    <li>• الرقم الضريبي</li>
                    <li>• رقم الترخيص</li>
                    <li>• الحد الائتماني</li>
                    <li>• شروط الدفع</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// File upload preview
document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const label = e.target.parentElement;
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        
        label.innerHTML = `
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <i class="fas fa-file-excel text-green-500 text-3xl mb-2"></i>
                <p class="mb-2 text-sm text-gray-700">
                    <span class="font-semibold">${fileName}</span>
                </p>
                <p class="text-xs text-gray-500">${fileSize} MB</p>
            </div>
        `;
    }
});
</script>
@endsection
