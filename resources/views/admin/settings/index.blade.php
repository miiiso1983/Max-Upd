@extends('layouts.admin')

@section('title', 'إعدادات النظام')
@section('page-title', 'إعدادات النظام')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-cogs text-blue-600 ml-3"></i>
                    إعدادات النظام
                </h1>
                <p class="text-gray-600 mt-1">إدارة وتكوين إعدادات النظام العامة</p>
            </div>
            <button onclick="saveSettings()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-save ml-2"></i>
                حفظ الإعدادات
            </button>
        </div>
    </div>
    <!-- Settings Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- General Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-cog text-blue-600 ml-3"></i>
                    الإعدادات العامة
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم النظام</label>
                    <input type="text" value="MaxCon ERP" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">وصف النظام</label>
                    <textarea rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">نظام إدارة موارد المؤسسة المتكامل</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اللغة الافتراضية</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="ar">العربية</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-shield-alt text-green-600 ml-3"></i>
                    إعدادات الأمان
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">المصادقة الثنائية</label>
                        <p class="text-xs text-gray-500">تفعيل المصادقة الثنائية للمستخدمين</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">تسجيل العمليات</label>
                        <p class="text-xs text-gray-500">تسجيل جميع عمليات المستخدمين</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">مدة انتهاء الجلسة (دقيقة)</label>
                    <input type="number" value="120" min="30" max="1440"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Backup Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-database text-purple-600 ml-3"></i>
                    إعدادات النسخ الاحتياطي
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">النسخ الاحتياطي التلقائي</label>
                        <p class="text-xs text-gray-500">إنشاء نسخة احتياطية يومياً</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">وقت النسخ الاحتياطي</label>
                    <input type="time" value="02:00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <button type="button" 
                            class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        <i class="fas fa-download ml-2"></i>
                        إنشاء نسخة احتياطية الآن
                    </button>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 ml-3"></i>
                    معلومات النظام
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">إصدار النظام:</span>
                    <span class="text-sm font-medium text-gray-900">1.0.0</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">إصدار Laravel:</span>
                    <span class="text-sm font-medium text-gray-900">{{ app()->version() }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">إصدار PHP:</span>
                    <span class="text-sm font-medium text-gray-900">{{ PHP_VERSION }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">قاعدة البيانات:</span>
                    <span class="text-sm font-medium text-gray-900">MySQL</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">آخر تحديث:</span>
                    <span class="text-sm font-medium text-gray-900">{{ now()->format('Y-m-d H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function saveSettings() {
    // Add save settings functionality here
    alert('تم حفظ الإعدادات بنجاح!');
}
</script>
@endsection
