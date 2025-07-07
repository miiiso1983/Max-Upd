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
        Schema::create('pharmaceutical_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmaceutical_batch_id'); // الدفعة
            $table->string('test_type'); // نوع الفحص
            $table->string('test_name'); // اسم الفحص
            $table->string('test_name_ar')->nullable(); // اسم الفحص بالعربي
            $table->text('test_description')->nullable(); // وصف الفحص
            $table->string('test_method'); // طريقة الفحص
            $table->string('test_standard')->nullable(); // المعيار المرجعي
            $table->string('acceptance_criteria'); // معايير القبول
            $table->string('test_parameter'); // معامل الفحص
            $table->string('unit_of_measurement')->nullable(); // وحدة القياس
            $table->decimal('expected_min_value', 10, 4)->nullable(); // القيمة الدنيا المتوقعة
            $table->decimal('expected_max_value', 10, 4)->nullable(); // القيمة العليا المتوقعة
            $table->string('expected_result')->nullable(); // النتيجة المتوقعة
            $table->decimal('actual_value', 10, 4)->nullable(); // القيمة الفعلية
            $table->string('actual_result')->nullable(); // النتيجة الفعلية
            $table->enum('test_result', ['pass', 'fail', 'pending', 'retest', 'out_of_specification'])->default('pending'); // نتيجة الفحص
            $table->text('deviation_reason')->nullable(); // سبب الانحراف
            $table->text('corrective_action')->nullable(); // الإجراء التصحيحي
            $table->date('test_date'); // تاريخ الفحص
            $table->time('test_time')->nullable(); // وقت الفحص
            $table->string('tested_by'); // الفاحص
            $table->string('reviewed_by')->nullable(); // المراجع
            $table->string('approved_by')->nullable(); // المعتمد
            $table->date('review_date')->nullable(); // تاريخ المراجعة
            $table->date('approval_date')->nullable(); // تاريخ الاعتماد
            $table->string('laboratory'); // المختبر
            $table->string('instrument_used')->nullable(); // الجهاز المستخدم
            $table->string('instrument_id')->nullable(); // رقم الجهاز
            $table->date('instrument_calibration_date')->nullable(); // تاريخ معايرة الجهاز
            $table->string('reagent_lot')->nullable(); // رقم دفعة الكاشف
            $table->date('reagent_expiry')->nullable(); // تاريخ انتهاء الكاشف
            $table->text('environmental_conditions')->nullable(); // الظروف البيئية
            $table->decimal('temperature', 5, 2)->nullable(); // درجة الحرارة
            $table->decimal('humidity', 5, 2)->nullable(); // الرطوبة
            $table->integer('sample_size')->nullable(); // حجم العينة
            $table->string('sample_preparation')->nullable(); // تحضير العينة
            $table->text('test_procedure')->nullable(); // إجراء الفحص
            $table->text('observations')->nullable(); // الملاحظات
            $table->json('raw_data')->nullable(); // البيانات الخام
            $table->json('calculations')->nullable(); // الحسابات
            $table->string('certificate_number')->nullable(); // رقم الشهادة
            $table->boolean('is_stability_test')->default(false); // فحص ثبات
            $table->string('stability_condition')->nullable(); // ظروف الثبات
            $table->integer('stability_time_point')->nullable(); // نقطة زمنية للثبات
            $table->string('stability_time_unit')->nullable(); // وحدة الزمن للثبات
            $table->boolean('is_retest')->default(false); // إعادة فحص
            $table->unsignedBigInteger('original_test_id')->nullable(); // الفحص الأصلي
            $table->text('retest_reason')->nullable(); // سبب إعادة الفحص
            $table->boolean('requires_investigation')->default(false); // يتطلب تحقيق
            $table->text('investigation_notes')->nullable(); // ملاحظات التحقيق
            $table->enum('investigation_status', ['not_required', 'pending', 'ongoing', 'completed'])->default('not_required'); // حالة التحقيق
            $table->json('attachments')->nullable(); // المرفقات
            $table->text('notes')->nullable(); // ملاحظات
            $table->enum('status', ['active', 'cancelled', 'superseded'])->default('active'); // حالة السجل
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('pharmaceutical_batch_id');
            $table->index(['test_type', 'test_result']);
            $table->index('test_date');
            $table->index('tested_by');
            $table->index('laboratory');
            $table->index('is_stability_test');
            $table->index('original_test_id');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('pharmaceutical_batch_id')->references('id')->on('pharmaceutical_batches')->onDelete('cascade');
            $table->foreign('original_test_id')->references('id')->on('pharmaceutical_tests')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_tests');
    }
};
