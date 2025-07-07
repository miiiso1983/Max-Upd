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
        Schema::create('pharmaceutical_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmaceutical_product_id'); // المنتج الدوائي
            $table->string('batch_number')->unique(); // رقم الدفعة
            $table->string('lot_number')->nullable(); // رقم اللوط
            $table->date('manufacturing_date'); // تاريخ التصنيع
            $table->date('expiry_date'); // تاريخ انتهاء الصلاحية
            $table->integer('quantity_manufactured'); // الكمية المصنعة
            $table->integer('quantity_released')->default(0); // الكمية المطروحة
            $table->integer('quantity_recalled')->default(0); // الكمية المسحوبة
            $table->integer('quantity_destroyed')->default(0); // الكمية المتلفة
            $table->string('manufacturing_site'); // موقع التصنيع
            $table->string('packaging_site')->nullable(); // موقع التعبئة
            $table->enum('batch_status', ['in_production', 'testing', 'released', 'quarantine', 'rejected', 'recalled', 'destroyed'])->default('in_production'); // حالة الدفعة
            $table->text('batch_record')->nullable(); // سجل الدفعة
            $table->json('raw_materials')->nullable(); // المواد الخام المستخدمة
            $table->json('packaging_materials')->nullable(); // مواد التعبئة المستخدمة
            $table->string('production_line')->nullable(); // خط الإنتاج
            $table->string('shift')->nullable(); // الوردية
            $table->string('supervisor')->nullable(); // المشرف
            $table->text('production_notes')->nullable(); // ملاحظات الإنتاج
            $table->decimal('yield_percentage', 5, 2)->nullable(); // نسبة المردود
            $table->boolean('requires_testing')->default(true); // يتطلب فحص
            $table->enum('testing_status', ['pending', 'in_progress', 'passed', 'failed', 'conditional'])->default('pending'); // حالة الفحص
            $table->date('testing_start_date')->nullable(); // تاريخ بدء الفحص
            $table->date('testing_completion_date')->nullable(); // تاريخ انتهاء الفحص
            $table->string('testing_laboratory')->nullable(); // المختبر المسؤول
            $table->string('testing_technician')->nullable(); // فني المختبر
            $table->text('testing_notes')->nullable(); // ملاحظات الفحص
            $table->json('test_results')->nullable(); // نتائج الفحوصات
            $table->boolean('stability_testing_required')->default(false); // يتطلب فحص ثبات
            $table->date('stability_testing_start')->nullable(); // تاريخ بدء فحص الثبات
            $table->date('stability_testing_end')->nullable(); // تاريخ انتهاء فحص الثبات
            $table->enum('stability_status', ['not_started', 'ongoing', 'completed', 'failed'])->default('not_started'); // حالة فحص الثبات
            $table->date('release_date')->nullable(); // تاريخ الإطلاق
            $table->string('released_by')->nullable(); // المطلق من قبل
            $table->text('release_notes')->nullable(); // ملاحظات الإطلاق
            $table->string('certificate_of_analysis')->nullable(); // شهادة التحليل
            $table->boolean('recall_issued')->default(false); // تم إصدار سحب
            $table->date('recall_date')->nullable(); // تاريخ السحب
            $table->text('recall_reason')->nullable(); // سبب السحب
            $table->enum('recall_level', ['consumer', 'retail', 'wholesale'])->nullable(); // مستوى السحب
            $table->boolean('regulatory_notification_sent')->default(false); // تم إرسال إشعار للجهة المنظمة
            $table->date('regulatory_notification_date')->nullable(); // تاريخ الإشعار
            $table->json('documents')->nullable(); // المستندات المرفقة
            $table->text('notes')->nullable(); // ملاحظات عامة
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active'); // حالة السجل
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('pharmaceutical_product_id');
            $table->index(['batch_status', 'testing_status']);
            $table->index('manufacturing_date');
            $table->index('expiry_date');
            $table->index('release_date');
            $table->index('recall_date');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('pharmaceutical_product_id')->references('id')->on('pharmaceutical_products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_batches');
    }
};
