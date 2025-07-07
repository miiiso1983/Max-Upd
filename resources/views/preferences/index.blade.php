@extends('layouts.app')

@section('title', 'التفضيلات - MaxCon ERP')
@section('page-title', 'التفضيلات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">التفضيلات</h1>
            <p class="text-gray-600">تخصيص إعدادات النظام والواجهة</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="exportPreferences()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download ml-2"></i>
                تصدير الإعدادات
            </button>
            <button onclick="resetAllPreferences()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-undo ml-2"></i>
                إعادة تعيين الكل
            </button>
        </div>
    </div>

    <!-- Preferences Tabs -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 space-x-reverse px-6">
                <button onclick="showTab('theme')" class="preference-tab active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    <i class="fas fa-palette ml-2"></i>
                    المظهر
                </button>
                <button onclick="showTab('layout')" class="preference-tab py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-layout ml-2"></i>
                    التخطيط
                </button>
                <button onclick="showTab('dashboard')" class="preference-tab py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-tachometer-alt ml-2"></i>
                    لوحة التحكم
                </button>
                <button onclick="showTab('language')" class="preference-tab py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-language ml-2"></i>
                    اللغة والمنطقة
                </button>
                <button onclick="showTab('general')" class="preference-tab py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-cog ml-2"></i>
                    عام
                </button>
            </nav>
        </div>

        <!-- Theme Preferences -->
        <div id="theme-tab" class="preference-content p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">إعدادات المظهر</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">وضع المظهر</label>
                    <select id="theme-mode" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="light">فاتح</option>
                        <option value="dark">داكن</option>
                        <option value="auto">تلقائي</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">لون المظهر</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setThemeColor('blue')" class="theme-color-btn w-full h-10 bg-blue-500 rounded-md border-2 border-transparent hover:border-gray-300" data-color="blue"></button>
                        <button onclick="setThemeColor('green')" class="theme-color-btn w-full h-10 bg-green-500 rounded-md border-2 border-transparent hover:border-gray-300" data-color="green"></button>
                        <button onclick="setThemeColor('purple')" class="theme-color-btn w-full h-10 bg-purple-500 rounded-md border-2 border-transparent hover:border-gray-300" data-color="purple"></button>
                        <button onclick="setThemeColor('red')" class="theme-color-btn w-full h-10 bg-red-500 rounded-md border-2 border-transparent hover:border-gray-300" data-color="red"></button>
                        <button onclick="setThemeColor('orange')" class="theme-color-btn w-full h-10 bg-orange-500 rounded-md border-2 border-transparent hover:border-gray-300" data-color="orange"></button>
                        <button onclick="setThemeColor('teal')" class="theme-color-btn w-full h-10 bg-teal-500 rounded-md border-2 border-transparent hover:border-gray-300" data-color="teal"></button>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">طي الشريط الجانبي</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sidebar-collapsed" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Layout Preferences -->
        <div id="layout-tab" class="preference-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">إعدادات التخطيط</h3>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">موضع الشريط الجانبي</label>
                    <select id="sidebar-position" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="right">يمين</option>
                        <option value="left">يسار</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">تثبيت الرأس</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="header-fixed" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">تثبيت التذييل</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="footer-fixed" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Dashboard Preferences -->
        <div id="dashboard-tab" class="preference-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">إعدادات لوحة التحكم</h3>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الودجات المعروضة</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard-widgets" value="sales" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="mr-2 text-sm text-gray-700">المبيعات</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard-widgets" value="inventory" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="mr-2 text-sm text-gray-700">المخزون</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard-widgets" value="notifications" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="mr-2 text-sm text-gray-700">الإشعارات</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard-widgets" value="reports" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="mr-2 text-sm text-gray-700">التقارير</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">فترة التحديث التلقائي (ثانية)</label>
                    <select id="refresh-interval" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="15">15 ثانية</option>
                        <option value="30">30 ثانية</option>
                        <option value="60">دقيقة واحدة</option>
                        <option value="300">5 دقائق</option>
                        <option value="0">بدون تحديث تلقائي</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">عرض رسالة الترحيب</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="show-welcome" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Language Preferences -->
        <div id="language-tab" class="preference-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">إعدادات اللغة والمنطقة</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اللغة</label>
                    <select id="language-locale" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ar">العربية</option>
                        <option value="en">English</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المنطقة الزمنية</label>
                    <select id="language-timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Asia/Baghdad">بغداد</option>
                        <option value="Asia/Riyadh">الرياض</option>
                        <option value="Asia/Dubai">دبي</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تنسيق التاريخ</label>
                    <select id="date-format" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Y-m-d">2024-01-15</option>
                        <option value="d/m/Y">15/01/2024</option>
                        <option value="d-m-Y">15-01-2024</option>
                        <option value="m/d/Y">01/15/2024</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- General Preferences -->
        <div id="general-tab" class="preference-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">الإعدادات العامة</h3>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">عدد العناصر في الصفحة</label>
                    <select id="items-per-page" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">الحفظ التلقائي</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="auto-save" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">عرض التلميحات</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="show-tooltips" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <div class="flex justify-end space-x-2 space-x-reverse">
                <button onclick="savePreferences()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ التفضيلات
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">استيراد التفضيلات</h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملف التفضيلات (JSON)</label>
                    <input type="file" id="import-file" accept=".json" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-2 space-x-reverse">
                    <button onclick="closeImportModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button onclick="importPreferences()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        استيراد
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPreferences = {};

document.addEventListener('DOMContentLoaded', function() {
    loadPreferences();
    setupEventListeners();
});

function loadPreferences() {
    fetch('/api/preferences')
        .then(response => response.json())
        .then(data => {
            currentPreferences = data.preferences;
            updateUI();
        })
        .catch(error => console.error('Error loading preferences:', error));
}

function updateUI() {
    // Theme preferences
    document.getElementById('theme-mode').value = currentPreferences['theme.mode'] || 'light';
    document.getElementById('sidebar-collapsed').checked = currentPreferences['theme.sidebar_collapsed'] || false;
    
    // Highlight selected color
    const selectedColor = currentPreferences['theme.color'] || 'blue';
    document.querySelectorAll('.theme-color-btn').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-gray-400');
        if (btn.dataset.color === selectedColor) {
            btn.classList.add('ring-2', 'ring-gray-400');
        }
    });
    
    // Layout preferences
    document.getElementById('sidebar-position').value = currentPreferences['layout.sidebar_position'] || 'right';
    document.getElementById('header-fixed').checked = currentPreferences['layout.header_fixed'] || true;
    document.getElementById('footer-fixed').checked = currentPreferences['layout.footer_fixed'] || false;
    
    // Dashboard preferences
    const widgets = currentPreferences['dashboard.widgets'] || ['sales', 'inventory', 'notifications'];
    document.querySelectorAll('input[name="dashboard-widgets"]').forEach(checkbox => {
        checkbox.checked = widgets.includes(checkbox.value);
    });
    document.getElementById('refresh-interval').value = currentPreferences['dashboard.refresh_interval'] || 30;
    document.getElementById('show-welcome').checked = currentPreferences['dashboard.show_welcome'] || true;
    
    // Language preferences
    document.getElementById('language-locale').value = currentPreferences['language.locale'] || 'ar';
    document.getElementById('language-timezone').value = currentPreferences['language.timezone'] || 'Asia/Baghdad';
    document.getElementById('date-format').value = currentPreferences['language.date_format'] || 'Y-m-d';
    
    // General preferences
    document.getElementById('items-per-page').value = currentPreferences['general.items_per_page'] || 20;
    document.getElementById('auto-save').checked = currentPreferences['general.auto_save'] || true;
    document.getElementById('show-tooltips').checked = currentPreferences['general.show_tooltips'] || true;
}

function setupEventListeners() {
    // Theme mode change
    document.getElementById('theme-mode').addEventListener('change', function() {
        updateSinglePreference('theme.mode', this.value);
    });
    
    // Sidebar collapsed toggle
    document.getElementById('sidebar-collapsed').addEventListener('change', function() {
        updateSinglePreference('theme.sidebar_collapsed', this.checked);
    });
}

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.preference-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Update tab buttons
    document.querySelectorAll('.preference-tab').forEach(btn => {
        btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    event.target.classList.add('active', 'border-blue-500', 'text-blue-600');
    event.target.classList.remove('border-transparent', 'text-gray-500');
}

function setThemeColor(color) {
    // Update UI
    document.querySelectorAll('.theme-color-btn').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-gray-400');
    });
    event.target.classList.add('ring-2', 'ring-gray-400');
    
    // Update preference
    updateSinglePreference('theme.color', color);
}

function updateSinglePreference(key, value) {
    fetch('/api/preferences/update-single', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ key, value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPreferences[key] = value;
            // Apply theme changes immediately
            if (key.startsWith('theme.')) {
                applyThemeChanges();
            }
        }
    })
    .catch(error => console.error('Error updating preference:', error));
}

function savePreferences() {
    const preferences = {};
    
    // Collect all preferences from form
    preferences['theme.mode'] = document.getElementById('theme-mode').value;
    preferences['theme.sidebar_collapsed'] = document.getElementById('sidebar-collapsed').checked;
    preferences['layout.sidebar_position'] = document.getElementById('sidebar-position').value;
    preferences['layout.header_fixed'] = document.getElementById('header-fixed').checked;
    preferences['layout.footer_fixed'] = document.getElementById('footer-fixed').checked;
    
    // Dashboard widgets
    const widgets = [];
    document.querySelectorAll('input[name="dashboard-widgets"]:checked').forEach(checkbox => {
        widgets.push(checkbox.value);
    });
    preferences['dashboard.widgets'] = widgets;
    preferences['dashboard.refresh_interval'] = parseInt(document.getElementById('refresh-interval').value);
    preferences['dashboard.show_welcome'] = document.getElementById('show-welcome').checked;
    
    preferences['language.locale'] = document.getElementById('language-locale').value;
    preferences['language.timezone'] = document.getElementById('language-timezone').value;
    preferences['language.date_format'] = document.getElementById('date-format').value;
    preferences['general.items_per_page'] = parseInt(document.getElementById('items-per-page').value);
    preferences['general.auto_save'] = document.getElementById('auto-save').checked;
    preferences['general.show_tooltips'] = document.getElementById('show-tooltips').checked;
    
    fetch('/api/preferences/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ preferences })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification(data.message, 'success');
            currentPreferences = { ...currentPreferences, ...preferences };
            applyThemeChanges();
        }
    })
    .catch(error => {
        console.error('Error saving preferences:', error);
        MaxCon.showNotification('حدث خطأ في حفظ التفضيلات', 'error');
    });
}

function resetAllPreferences() {
    if (confirm('هل أنت متأكد من إعادة تعيين جميع التفضيلات إلى القيم الافتراضية؟')) {
        fetch('/api/preferences/reset-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaxCon.showNotification(data.message, 'success');
                loadPreferences();
                applyThemeChanges();
            }
        })
        .catch(error => {
            console.error('Error resetting preferences:', error);
            MaxCon.showNotification('حدث خطأ في إعادة تعيين التفضيلات', 'error');
        });
    }
}

function exportPreferences() {
    fetch('/api/preferences/export')
        .then(response => response.json())
        .then(data => {
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'maxcon-preferences.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            MaxCon.showNotification('تم تصدير التفضيلات بنجاح', 'success');
        })
        .catch(error => {
            console.error('Error exporting preferences:', error);
            MaxCon.showNotification('حدث خطأ في تصدير التفضيلات', 'error');
        });
}

function applyThemeChanges() {
    // This would apply theme changes to the current page
    // In a real implementation, you might reload the page or update CSS variables
    const mode = currentPreferences['theme.mode'] || 'light';
    const color = currentPreferences['theme.color'] || 'blue';
    
    // Update CSS custom properties or classes
    document.documentElement.setAttribute('data-theme', mode);
    document.documentElement.setAttribute('data-color', color);
}
</script>
@endpush
