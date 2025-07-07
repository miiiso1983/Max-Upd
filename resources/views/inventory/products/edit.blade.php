@extends('layouts.app')

@section('page-title', 'تعديل المنتج')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تعديل المنتج: {{ $product->name }}</h1>
            <p class="text-gray-600 mt-1">تعديل بيانات المنتج</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('inventory.products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
            <a href="{{ route('inventory.products.show', $product) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-eye ml-2"></i>
                عرض المنتج
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('inventory.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم المنتج *</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الاسم بالعربية</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar', $product->name_ar) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name_ar') border-red-500 @enderror">
                            @error('name_ar')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رمز المنتج *</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('sku') border-red-500 @enderror">
                            @error('sku')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الباركود</label>
                            <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('barcode') border-red-500 @enderror">
                            @error('barcode')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الفئة</label>
                            <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                                <option value="">اختر الفئة</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name_ar ?: $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الشركة المصنعة</label>
                            <select name="manufacturer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('manufacturer_id') border-red-500 @enderror">
                                <option value="">اختر الشركة المصنعة</option>
                                @foreach($manufacturers as $manufacturer)
                                    <option value="{{ $manufacturer->id }}" {{ old('manufacturer_id', $product->manufacturer_id) == $manufacturer->id ? 'selected' : '' }}>
                                        {{ $manufacturer->name_ar ?: $manufacturer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manufacturer_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">وحدة القياس *</label>
                            <x-searchable-dropdown
                                name="unit_of_measure"
                                placeholder="اختر وحدة القياس"
                                search-placeholder="ابحث في وحدات القياس..."
                                :options="collect($units)->map(function($unit, $key) {
                                    return [
                                        'value' => $key,
                                        'text' => $unit
                                    ];
                                })->values()->toArray()"
                                value="{{ old('unit_of_measure', $product->unit_of_measure) }}"
                                required
                                class="{{ $errors->has('unit_of_measure') ? 'error' : '' }}"
                            />
                            @error('unit_of_measure')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                            <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('is_active') border-red-500 @enderror">
                                <option value="1" {{ old('is_active', $product->is_active) ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ !old('is_active', $product->is_active) ? 'selected' : '' }}>غير نشط</option>
                            </select>
                            @error('is_active')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">الوصف</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                            <textarea name="description" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف بالعربية</label>
                            <textarea name="description_ar" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('description_ar') border-red-500 @enderror">{{ old('description_ar', $product->description_ar) }}</textarea>
                            @error('description_ar')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">الأسعار</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">سعر الشراء *</label>
                            <input type="number" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" 
                                   min="0" step="0.01" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('purchase_price') border-red-500 @enderror">
                            @error('purchase_price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">سعر البيع *</label>
                            <input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" 
                                   min="0" step="0.01" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('selling_price') border-red-500 @enderror">
                            @error('selling_price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Stock Levels -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">مستويات المخزون</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحد الأدنى للمخزون</label>
                            <input type="number" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level) }}" 
                                   min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('min_stock_level') border-red-500 @enderror">
                            @error('min_stock_level')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحد الأقصى للمخزون</label>
                            <input type="number" name="max_stock_level" value="{{ old('max_stock_level', $product->max_stock_level) }}" 
                                   min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('max_stock_level') border-red-500 @enderror">
                            @error('max_stock_level')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نقطة إعادة الطلب</label>
                            <input type="number" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" 
                                   min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('reorder_level') border-red-500 @enderror">
                            @error('reorder_level')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Properties -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">خصائص المنتج</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_prescription_required" value="1" 
                                       {{ old('is_prescription_required', $product->is_prescription_required) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="mr-2 block text-sm text-gray-900">يتطلب وصفة طبية</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_controlled_substance" value="1" 
                                       {{ old('is_controlled_substance', $product->is_controlled_substance) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="mr-2 block text-sm text-gray-900">مادة خاضعة للرقابة</label>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="expiry_tracking" value="1" 
                                       {{ old('expiry_tracking', $product->expiry_tracking) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="mr-2 block text-sm text-gray-900">تتبع تاريخ الانتهاء</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="batch_tracking" value="1" 
                                       {{ old('batch_tracking', $product->batch_tracking) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="mr-2 block text-sm text-gray-900">تتبع الدفعات</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ملاحظات</h3>
                    <div>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="أي ملاحظات إضافية حول المنتج...">{{ old('notes', $product->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('inventory.products.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save ml-2"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
