@extends('layouts.app')

@section('title', 'إضافة مخزن جديد - MaxCon ERP')
@section('page-title', 'إضافة مخزن جديد')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">إضافة مخزن جديد</h1>
                <p class="text-green-100">إنشاء مخزن جديد في النظام</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-warehouse"></i>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form action="{{ route('inventory.warehouses.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">اسم المخزن *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="name_ar" class="block text-sm font-medium text-gray-700 mb-2">الاسم بالعربية</label>
                        <input type="text" id="name_ar" name="name_ar" value="{{ old('name_ar') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('name_ar') border-red-500 @enderror">
                        @error('name_ar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">كود المخزن *</label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('code') border-red-500 @enderror">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">مدير المخزن</label>
                        <select id="manager_id" name="manager_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('manager_id') border-red-500 @enderror">
                            <option value="">اختر مدير المخزن</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('manager_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">وصف المخزن</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mt-6">
                    <label for="description_ar" class="block text-sm font-medium text-gray-700 mb-2">الوصف بالعربية</label>
                    <textarea id="description_ar" name="description_ar" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('description_ar') border-red-500 @enderror">{{ old('description_ar') }}</textarea>
                    @error('description_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Location Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الموقع</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">المدينة</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('city') border-red-500 @enderror">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="governorate" class="block text-sm font-medium text-gray-700 mb-2">المحافظة</label>
                        <input type="text" id="governorate" name="governorate" value="{{ old('governorate') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('governorate') border-red-500 @enderror">
                        @error('governorate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">العنوان التفصيلي</label>
                    <textarea id="address" name="address" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Capacity and Settings -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">السعة والإعدادات</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">السعة (متر مكعب)</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('capacity') border-red-500 @enderror">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6 space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_active" class="mr-2 block text-sm text-gray-900">مخزن نشط</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_main" name="is_main" value="1" {{ old('is_main') ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_main" class="mr-2 block text-sm text-gray-900">مخزن رئيسي</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="temperature_controlled" name="temperature_controlled" value="1" {{ old('temperature_controlled') ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="temperature_controlled" class="mr-2 block text-sm text-gray-900">مخزن مبرد</label>
                    </div>
                </div>
                
                <div id="temperature-settings" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6" style="display: none;">
                    <div>
                        <label for="min_temperature" class="block text-sm font-medium text-gray-700 mb-2">أقل درجة حرارة (°C)</label>
                        <input type="number" id="min_temperature" name="min_temperature" value="{{ old('min_temperature') }}" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('min_temperature') border-red-500 @enderror">
                        @error('min_temperature')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="max_temperature" class="block text-sm font-medium text-gray-700 mb-2">أعلى درجة حرارة (°C)</label>
                        <input type="number" id="max_temperature" name="max_temperature" value="{{ old('max_temperature') }}" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('max_temperature') border-red-500 @enderror">
                        @error('max_temperature')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6">
                <a href="{{ route('inventory.warehouses.index') }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    إلغاء
                </a>
                
                <button type="submit" 
                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-save ml-2"></i>
                    حفظ المخزن
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const temperatureControlled = document.getElementById('temperature_controlled');
    const temperatureSettings = document.getElementById('temperature-settings');
    
    function toggleTemperatureSettings() {
        if (temperatureControlled.checked) {
            temperatureSettings.style.display = 'grid';
        } else {
            temperatureSettings.style.display = 'none';
        }
    }
    
    temperatureControlled.addEventListener('change', toggleTemperatureSettings);
    toggleTemperatureSettings(); // Initial check
});
</script>
@endpush
@endsection
