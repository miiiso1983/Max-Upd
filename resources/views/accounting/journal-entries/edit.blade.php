@extends('layouts.app')

@section('title', 'تعديل القيد - MaxCon ERP')
@section('page-title', 'تعديل القيد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تعديل القيد: {{ $transaction->transaction_number }}</h1>
            <p class="text-gray-600">تعديل قيد محاسبي في دفتر اليومية</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('accounting.transactions.show', $transaction) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-eye ml-2"></i>
                عرض القيد
            </a>
            <a href="{{ route('accounting.transactions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">تعديل معلومات القيد</h3>
        </div>
        
        <form id="editTransactionForm" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Transaction Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        نوع القيد <span class="text-red-500">*</span>
                    </label>
                    <select id="type" 
                            name="type" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر نوع القيد</option>
                        @foreach($types_ar as $key => $value)
                            <option value="{{ $key }}" {{ $transaction->type === $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">
                        تاريخ القيد <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="transaction_date" 
                           name="transaction_date" 
                           value="{{ $transaction->transaction_date->format('Y-m-d') }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                        العملة
                    </label>
                    <select id="currency" 
                            name="currency" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ ($transaction->currency ?: 'IQD') === $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        الوصف (إنجليزي) <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" 
                              name="description" 
                              required
                              rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Transaction description">{{ $transaction->description }}</textarea>
                </div>

                <div>
                    <label for="description_ar" class="block text-sm font-medium text-gray-700 mb-2">
                        الوصف (عربي)
                    </label>
                    <textarea id="description_ar" 
                              name="description_ar" 
                              rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="وصف القيد">{{ $transaction->description_ar }}</textarea>
                </div>
            </div>

            <!-- Journal Entries -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">قيود اليومية</h4>
                    <button type="button" 
                            onclick="addJournalEntry()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة قيد
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحساب</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مدين</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">دائن</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="journal-entries-table" class="bg-white divide-y divide-gray-200">
                            <!-- Existing journal entries will be loaded here -->
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right font-medium text-gray-900">الإجمالي:</td>
                                <td class="px-6 py-3 text-right font-bold text-green-600" id="total-debits">0.00</td>
                                <td class="px-6 py-3 text-right font-bold text-orange-600" id="total-credits">0.00</td>
                                <td class="px-6 py-3"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right font-medium text-gray-900">الفرق:</td>
                                <td colspan="2" class="px-6 py-3 text-right font-bold" id="balance-difference">0.00</td>
                                <td class="px-6 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
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
                          placeholder="ملاحظات إضافية">{{ $transaction->notes }}</textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-3 space-x-reverse pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save ml-2"></i>
                    حفظ التعديلات
                </button>
                <a href="{{ route('accounting.transactions.show', $transaction) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Data for JavaScript -->
<div id="accounts-data" data-accounts="{{ json_encode($accounts ?? []) }}" data-entries="{{ json_encode($transaction->journalEntries ?? []) }}" style="display: none;"></div>

@endsection

@push('scripts')
<script>
let entryCounter = 0;
const accountsData = document.getElementById('accounts-data');
const accounts = accountsData ? JSON.parse(accountsData.dataset.accounts) : [];
const existingEntries = accountsData ? JSON.parse(accountsData.dataset.entries) : [];

// Load existing journal entries
document.addEventListener('DOMContentLoaded', function() {
    existingEntries.forEach(entry => {
        addJournalEntry(entry);
    });
    updateTotals();
});

function addJournalEntry(existingEntry = null) {
    entryCounter++;
    const tableBody = document.getElementById('journal-entries-table');
    
    const row = document.createElement('tr');
    row.id = `entry-${entryCounter}`;
    
    const debitAmount = existingEntry && existingEntry.type === 'debit' ? existingEntry.amount : '';
    const creditAmount = existingEntry && existingEntry.type === 'credit' ? existingEntry.amount : '';
    
    row.innerHTML = `
        <td class="px-6 py-4">
            <select name="journal_entries[${entryCounter}][account_id]" 
                    required
                    onchange="updateAccountInfo(this, ${entryCounter})"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">اختر الحساب</option>
                ${accounts.map(account => 
                    `<option value="${account.id}" ${existingEntry && existingEntry.account_id == account.id ? 'selected' : ''}>${account.code ? account.code + ' - ' : ''}${account.name_ar || account.name}</option>`
                ).join('')}
            </select>
        </td>
        <td class="px-6 py-4">
            <input type="text" 
                   name="journal_entries[${entryCounter}][description]" 
                   value="${existingEntry ? existingEntry.description : ''}"
                   required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="وصف القيد">
        </td>
        <td class="px-6 py-4">
            <input type="number" 
                   name="journal_entries[${entryCounter}][debit_amount]" 
                   value="${debitAmount}"
                   step="0.01"
                   min="0"
                   onchange="updateEntryType(this, ${entryCounter}, 'debit')"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="0.00">
        </td>
        <td class="px-6 py-4">
            <input type="number" 
                   name="journal_entries[${entryCounter}][credit_amount]" 
                   value="${creditAmount}"
                   step="0.01"
                   min="0"
                   onchange="updateEntryType(this, ${entryCounter}, 'credit')"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="0.00">
        </td>
        <td class="px-6 py-4">
            <button type="button" 
                    onclick="removeJournalEntry(${entryCounter})"
                    class="text-red-600 hover:text-red-900">
                <i class="fas fa-trash"></i>
            </button>
            <input type="hidden" name="journal_entries[${entryCounter}][type]" value="${existingEntry ? existingEntry.type : ''}">
            <input type="hidden" name="journal_entries[${entryCounter}][amount]" value="${existingEntry ? existingEntry.amount : '0'}">
        </td>
    `;
    
    tableBody.appendChild(row);
    updateTotals();
}

function removeJournalEntry(entryId) {
    const row = document.getElementById(`entry-${entryId}`);
    if (row) {
        row.remove();
        updateTotals();
    }
}

function updateEntryType(input, entryId, type) {
    const row = document.getElementById(`entry-${entryId}`);
    const debitInput = row.querySelector('input[name*="[debit_amount]"]');
    const creditInput = row.querySelector('input[name*="[credit_amount]"]');
    const typeInput = row.querySelector('input[name*="[type]"]');
    const amountInput = row.querySelector('input[name*="[amount]"]');
    
    if (type === 'debit' && input.value > 0) {
        creditInput.value = '';
        typeInput.value = 'debit';
        amountInput.value = input.value;
    } else if (type === 'credit' && input.value > 0) {
        debitInput.value = '';
        typeInput.value = 'credit';
        amountInput.value = input.value;
    }
    
    updateTotals();
}

function updateTotals() {
    let totalDebits = 0;
    let totalCredits = 0;
    
    document.querySelectorAll('input[name*="[debit_amount]"]').forEach(input => {
        if (input.value) {
            totalDebits += parseFloat(input.value);
        }
    });
    
    document.querySelectorAll('input[name*="[credit_amount]"]').forEach(input => {
        if (input.value) {
            totalCredits += parseFloat(input.value);
        }
    });
    
    document.getElementById('total-debits').textContent = totalDebits.toFixed(2);
    document.getElementById('total-credits').textContent = totalCredits.toFixed(2);
    
    const difference = totalDebits - totalCredits;
    const differenceElement = document.getElementById('balance-difference');
    differenceElement.textContent = difference.toFixed(2);
    
    if (Math.abs(difference) < 0.01) {
        differenceElement.className = 'px-6 py-3 text-right font-bold text-green-600';
    } else {
        differenceElement.className = 'px-6 py-3 text-right font-bold text-red-600';
    }
}

function updateAccountInfo(select, entryId) {
    // You can add logic here to auto-fill description based on account
}

document.getElementById('editTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate that we have at least 2 entries
    const entries = document.querySelectorAll('#journal-entries-table tr');
    if (entries.length < 2) {
        alert('يجب إضافة قيدين على الأقل');
        return;
    }
    
    // Validate balance
    const totalDebits = parseFloat(document.getElementById('total-debits').textContent);
    const totalCredits = parseFloat(document.getElementById('total-credits').textContent);
    
    if (Math.abs(totalDebits - totalCredits) > 0.01) {
        alert('القيد غير متوازن. يجب أن يكون إجمالي المدين مساوياً لإجمالي الدائن');
        return;
    }
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الحفظ...';
    
    fetch('{{ route("accounting.transactions.update", $transaction) }}', {
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
            alert('تم تحديث القيد بنجاح');
            window.location.href = '{{ route("accounting.transactions.show", $transaction) }}';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحديث القيد');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save ml-2"></i>حفظ التعديلات';
    });
});
</script>
@endpush
