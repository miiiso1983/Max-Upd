{{-- Document Upload Component --}}
@props(['entityType', 'entityId', 'entityName' => ''])

<div class="bg-white rounded-lg p-6 card-shadow" id="document-upload-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-file-pdf text-red-600 ml-2"></i>
            الوثائق والملفات
        </h3>
        <button onclick="openUploadModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-upload ml-2"></i>
            رفع ملف جديد
        </button>
    </div>

    <!-- Documents List -->
    <div id="documents-list" class="space-y-3">
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-file-pdf text-4xl mb-3"></i>
            <p>جاري تحميل الوثائق...</p>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">رفع وثيقة جديدة</h3>
                    <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="upload-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="entity_type" value="{{ $entityType }}">
                    <input type="hidden" name="entity_id" value="{{ $entityId }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- File Upload -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-pdf text-red-600 ml-1"></i>
                                ملف PDF *
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                                <input type="file" name="file" id="file-input" accept=".pdf" required 
                                       class="hidden" onchange="handleFileSelect(this)">
                                <label for="file-input" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-600">اضغط لاختيار ملف PDF أو اسحبه هنا</p>
                                    <p class="text-sm text-gray-500 mt-1">الحد الأقصى: 10 ميجابايت</p>
                                </label>
                                <div id="file-info" class="hidden mt-3 p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-red-600 ml-2"></i>
                                        <span id="file-name" class="text-sm font-medium"></span>
                                        <span id="file-size" class="text-xs text-gray-500 mr-2"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">عنوان الوثيقة *</label>
                            <input type="text" name="title" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Document Title (Arabic) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">العنوان بالعربية</label>
                            <input type="text" name="title_ar" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Document Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نوع الوثيقة *</label>
                            <select name="document_type" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر نوع الوثيقة</option>
                                <option value="license">ترخيص</option>
                                <option value="certificate">شهادة</option>
                                <option value="report">تقرير</option>
                                <option value="specification">مواصفات</option>
                                <option value="sop">إجراء تشغيل معياري</option>
                                <option value="protocol">بروتوكول</option>
                                <option value="validation">تحقق</option>
                                <option value="registration">تسجيل</option>
                                <option value="inspection_report">تقرير تفتيش</option>
                                <option value="test_report">تقرير فحص</option>
                                <option value="other">أخرى</option>
                            </select>
                        </div>

                        <!-- Document Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الفئة الفرعية</label>
                            <input type="text" name="document_category" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Document Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الوثيقة</label>
                            <input type="date" name="document_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ انتهاء الصلاحية</label>
                            <input type="date" name="expiry_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Confidentiality Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">مستوى السرية</label>
                            <select name="confidentiality_level" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="internal">داخلي</option>
                                <option value="public">عام</option>
                                <option value="confidential">سري</option>
                                <option value="restricted">مقيد</option>
                            </select>
                        </div>

                        <!-- Required Document -->
                        <div class="flex items-center">
                            <input type="checkbox" name="is_required" id="is_required" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_required" class="mr-2 block text-sm text-gray-900">
                                وثيقة مطلوبة
                            </label>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">وصف الوثيقة</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Description (Arabic) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف بالعربية</label>
                            <textarea name="description_ar" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                            <textarea name="notes" rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="upload-progress" class="hidden mt-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 ml-3"></div>
                                <span class="text-blue-800">جاري رفع الملف...</span>
                            </div>
                            <div class="mt-2 bg-blue-200 rounded-full h-2">
                                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeUploadModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button type="submit" id="upload-btn"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <i class="fas fa-upload ml-2"></i>
                            رفع الملف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Data for JavaScript -->
<div id="document-upload-data"
     data-entity-type="{{ $entityType }}"
     data-entity-id="{{ $entityId ?? 0 }}"
     data-entity-name="{{ $entityName }}"
     data-get-documents-url="{{ route('regulatory-affairs.documents.get-documents') }}"
     data-upload-url="{{ route('regulatory-affairs.documents.upload') }}"
     style="display: none;"></div>

@push('scripts')
<script>
// Global variables
const documentData = document.getElementById('document-upload-data');
const entityType = documentData.dataset.entityType;
const entityId = documentData.dataset.entityId;
const entityName = documentData.dataset.entityName;
const getDocumentsUrl = documentData.dataset.getDocumentsUrl;
const uploadUrl = documentData.dataset.uploadUrl;

// Load documents on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDocuments();
});

// Open upload modal
function openUploadModal() {
    document.getElementById('upload-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Close upload modal
function closeUploadModal() {
    document.getElementById('upload-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('upload-form').reset();
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('upload-progress').classList.add('hidden');
}

// Handle file selection
function handleFileSelect(input) {
    const file = input.files[0];
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    
    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = `(${formatFileSize(file.size)})`;
        fileInfo.classList.remove('hidden');
        
        // Auto-fill title if empty
        const titleInput = document.querySelector('input[name="title"]');
        if (!titleInput.value) {
            titleInput.value = file.name.replace(/\.[^/.]+$/, "");
        }
    } else {
        fileInfo.classList.add('hidden');
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}

// Load documents
function loadDocuments() {
    const documentsUrl = getDocumentsUrl + '?entity_type=' + entityType + '&entity_id=' + entityId;
    fetch(documentsUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDocuments(data.documents);
            } else {
                showError('خطأ في تحميل الوثائق');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('خطأ في الاتصال بالخادم');
        });
}

// Display documents
function displayDocuments(documents) {
    const container = document.getElementById('documents-list');
    
    if (documents.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-file-pdf text-4xl mb-3"></i>
                <p>لا توجد وثائق مرفوعة</p>
                <p class="text-sm">اضغط على "رفع ملف جديد" لإضافة وثيقة</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = documents.map(doc => `
        <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-pdf text-red-600 text-xl"></i>
            </div>
            <div class="mr-4 flex-1">
                <h4 class="font-medium text-gray-900">${doc.title}</h4>
                <div class="flex items-center space-x-4 space-x-reverse text-sm text-gray-500 mt-1">
                    <span><i class="fas fa-tag ml-1"></i>${doc.document_type}</span>
                    <span><i class="fas fa-file ml-1"></i>${doc.file_size}</span>
                    <span><i class="fas fa-calendar ml-1"></i>${doc.upload_date}</span>
                    ${doc.is_expired ? '<span class="text-red-600"><i class="fas fa-exclamation-triangle ml-1"></i>منتهي</span>' : ''}
                    ${doc.is_expiring_soon && !doc.is_expired ? '<span class="text-orange-600"><i class="fas fa-clock ml-1"></i>ينتهي قريباً</span>' : ''}
                </div>
                ${doc.description ? `<p class="text-sm text-gray-600 mt-1">${doc.description}</p>` : ''}
            </div>
            <div class="flex items-center space-x-2 space-x-reverse">
                <a href="${doc.view_url}" target="_blank" 
                   class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50" 
                   title="عرض الملف">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="${doc.download_url}" 
                   class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50" 
                   title="تحميل الملف">
                    <i class="fas fa-download"></i>
                </a>
                <button onclick="deleteDocument(${doc.id})" 
                        class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50" 
                        title="حذف الملف">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Upload form submission
document.getElementById('upload-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const uploadBtn = document.getElementById('upload-btn');
    const uploadProgress = document.getElementById('upload-progress');
    
    // Show progress
    uploadBtn.disabled = true;
    uploadProgress.classList.remove('hidden');
    
    fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            closeUploadModal();
            loadDocuments(); // Reload documents list
        } else {
            showError(data.message || 'خطأ في رفع الملف');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('خطأ في الاتصال بالخادم');
    })
    .finally(() => {
        uploadBtn.disabled = false;
        uploadProgress.classList.add('hidden');
    });
});

// Delete document
function deleteDocument(documentId) {
    if (!confirm('هل أنت متأكد من حذف هذه الوثيقة؟')) {
        return;
    }

    fetch(`/regulatory-affairs/documents/${documentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            loadDocuments(); // Reload documents list
        } else {
            showError(data.message || 'خطأ في حذف الملف');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('خطأ في الاتصال بالخادم');
    });
}

// Show success message
function showSuccess(message) {
    // You can implement your preferred notification system here
    alert(message);
}

// Show error message
function showError(message) {
    // You can implement your preferred notification system here
    alert(message);
}
</script>
@endpush
