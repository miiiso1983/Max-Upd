<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regulatory_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique(); // رقم الوثيقة
            $table->string('title'); // عنوان الوثيقة
            $table->string('title_ar')->nullable(); // العنوان بالعربية
            $table->text('description')->nullable(); // وصف الوثيقة
            $table->text('description_ar')->nullable(); // الوصف بالعربية

            // نوع الكيان المرتبط بالوثيقة
            $table->enum('entity_type', ['company', 'product', 'batch', 'test', 'inspection']);
            $table->unsignedBigInteger('entity_id'); // معرف الكيان

            // معلومات الملف
            $table->string('file_name'); // اسم الملف الأصلي
            $table->string('file_path'); // مسار الملف
            $table->string('file_size'); // حجم الملف
            $table->string('mime_type'); // نوع الملف
            $table->string('file_hash')->nullable(); // hash للتحقق من سلامة الملف

            // تصنيف الوثيقة
            $table->enum('document_type', [
                'license', 'certificate', 'report', 'specification',
                'sop', 'protocol', 'validation', 'registration',
                'inspection_report', 'test_report', 'other'
            ]);
            $table->string('document_category')->nullable(); // فئة فرعية

            // معلومات الوثيقة
            $table->date('document_date')->nullable(); // تاريخ الوثيقة
            $table->date('expiry_date')->nullable(); // تاريخ انتهاء الصلاحية
            $table->enum('status', ['active', 'expired', 'superseded', 'archived'])->default('active');

            // معلومات الإصدار
            $table->string('version', 10)->default('1.0'); // إصدار الوثيقة
            $table->unsignedBigInteger('supersedes_id')->nullable(); // الوثيقة التي تحل محلها
            $table->foreign('supersedes_id')->references('id')->on('regulatory_documents')->onDelete('set null');

            // معلومات الأمان والوصول
            $table->enum('confidentiality_level', ['public', 'internal', 'confidential', 'restricted'])->default('internal');
            $table->json('access_permissions')->nullable(); // صلاحيات الوصول

            // معلومات التدقيق
            $table->unsignedBigInteger('uploaded_by'); // من رفع الملف
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('approved_by')->nullable(); // من وافق على الملف
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable(); // تاريخ الموافقة

            // معلومات إضافية
            $table->json('metadata')->nullable(); // بيانات إضافية
            $table->text('notes')->nullable(); // ملاحظات
            $table->boolean('is_required')->default(false); // هل الوثيقة مطلوبة
            $table->boolean('is_public')->default(false); // هل الوثيقة عامة

            $table->timestamps();

            // فهارس
            $table->index(['entity_type', 'entity_id']);
            $table->index(['document_type', 'status']);
            $table->index(['expiry_date']);
            $table->index(['uploaded_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulatory_documents');
    }
};
