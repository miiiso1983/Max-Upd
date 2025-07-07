@extends('layouts.app')

@section('title', 'إضافة حساب جديد - MaxCon ERP')
@section('page-title', 'إضافة حساب جديد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إضافة حساب جديد</h1>
            <p class="text-gray-600">إنشاء حساب جديد في دليل الحسابات</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('accounting.accounts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">معلومات الحساب</h3>
        </div>
        
        <form id="createAccountForm" class="p-6 space-y-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Account Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        رمز الحساب
                        <span class="text-gray-500">(اختياري)</span>
                    </label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="مثال: 1001">
                    <p class="text-xs text-gray-500 mt-1">سيتم إنشاء رمز تلقائياً إذا ترك فارغاً</p>
                </div>

                <!-- Account Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        نوع الحساب <span class="text-red-500">*</span>
                    </label>
                    <select id="type" 
                            name="type" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر نوع الحساب</option>
                        @foreach($types_ar as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Account Name (English) -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        اسم الحساب (إنجليزي) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Account Name">
                </div>

                <!-- Account Name (Arabic) -->
                <div>
                    <label for="name_ar" class="block text-sm font-medium text-gray-700 mb-2">
                        اسم الحساب (عربي)
                    </label>
                    <input type="text" 
                           id="name_ar" 
                           name="name_ar" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="اسم الحساب">
                </div>

                <!-- Subtype -->
                <div>
                    <label for="subtype" class="block text-sm font-medium text-gray-700 mb-2">
                        النوع الفرعي
                    </label>
                    <select id="subtype" 
                            name="subtype" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر النوع الفرعي</option>
                        @foreach($subtypes_ar as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Parent Account -->
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        الحساب الأب
                    </label>
                    <select id="parent_id"
                            name="parent_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">حساب رئيسي</option>
                        @foreach($parent_accounts as $account)
                            <option value="{{ $account->id }}" {{ ($selected_parent_id ?? '') == $account->id ? 'selected' : '' }}>
                                {{ $account->code ? $account->code . ' - ' : '' }}{{ $account->name_ar ?: $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Opening Balance -->
                <div>
                    <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">
                        الرصيد الافتتاحي
                    </label>
                    <input type="number" 
                           id="opening_balance" 
                           name="opening_balance" 
                           step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00">
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                        العملة
                    </label>
                    <select id="currency" 
                            name="currency" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="IQD">دينار عراقي (IQD)</option>
                        <option value="USD">دولار أمريكي (USD)</option>
                        <option value="EUR">يورو (EUR)</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        الحالة
                    </label>
                    <select id="status" 
                            name="status" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>

                <!-- Tax Account -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="tax_account" 
                           name="tax_account" 
                           value="1"
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tax_account" class="mr-2 text-sm text-gray-700">
                        حساب ضريبي
                    </label>
                </div>
            </div>

            <!-- Description -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        الوصف (إنجليزي)
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Account description"></textarea>
                </div>

                <div>
                    <label for="description_ar" class="block text-sm font-medium text-gray-700 mb-2">
                        الوصف (عربي)
                    </label>
                    <textarea id="description_ar" 
                              name="description_ar" 
                              rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="وصف الحساب"></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-3 space-x-reverse pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save ml-2"></i>
                    حفظ الحساب
                </button>
                <a href="{{ route('accounting.accounts.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
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
document.getElementById('createAccountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الحفظ...';
    
    fetch('{{ route("accounting.accounts.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // Show success message
            alert('تم إنشاء الحساب بنجاح');
            // Redirect to accounts list
            window.location.href = '{{ route("accounting.accounts.index") }}';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء إنشاء الحساب');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save ml-2"></i>حفظ الحساب';
    });
});
</script>
@endpush
