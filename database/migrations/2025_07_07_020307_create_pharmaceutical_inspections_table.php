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
        Schema::create('pharmaceutical_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique(); // رقم التفتيش
            $table->enum('inspection_type', ['routine', 'for_cause', 'pre_approval', 'surveillance', 'follow_up']); // نوع التفتيش
            $table->enum('inspected_entity_type', ['company', 'product', 'batch']); // نوع الكيان المفتش
            $table->unsignedBigInteger('pharmaceutical_company_id')->nullable(); // الشركة
            $table->unsignedBigInteger('pharmaceutical_product_id')->nullable(); // المنتج
            $table->unsignedBigInteger('pharmaceutical_batch_id')->nullable(); // الدفعة
            $table->string('regulatory_authority'); // الجهة المنظمة
            $table->string('inspection_team_lead'); // قائد فريق التفتيش
            $table->json('inspection_team_members')->nullable(); // أعضاء فريق التفتيش
            $table->date('inspection_date'); // تاريخ التفتيش
            $table->date('inspection_start_date')->nullable(); // تاريخ بدء التفتيش
            $table->date('inspection_end_date')->nullable(); // تاريخ انتهاء التفتيش
            $table->time('inspection_start_time')->nullable(); // وقت بدء التفتيش
            $table->time('inspection_end_time')->nullable(); // وقت انتهاء التفتيش
            $table->text('inspection_scope'); // نطاق التفتيش
            $table->text('inspection_objectives')->nullable(); // أهداف التفتيش
            $table->json('areas_inspected')->nullable(); // المناطق المفتشة
            $table->json('systems_inspected')->nullable(); // الأنظمة المفتشة
            $table->enum('inspection_status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('scheduled'); // حالة التفتيش
            $table->enum('inspection_result', ['satisfactory', 'minor_deficiencies', 'major_deficiencies', 'critical_deficiencies', 'non_compliant'])->nullable(); // نتيجة التفتيش
            $table->integer('total_observations')->default(0); // إجمالي الملاحظات
            $table->integer('critical_observations')->default(0); // الملاحظات الحرجة
            $table->integer('major_observations')->default(0); // الملاحظات الرئيسية
            $table->integer('minor_observations')->default(0); // الملاحظات الثانوية
            $table->text('key_findings')->nullable(); // النتائج الرئيسية
            $table->json('observations')->nullable(); // الملاحظات التفصيلية
            $table->json('non_conformities')->nullable(); // عدم المطابقات
            $table->json('corrective_actions_required')->nullable(); // الإجراءات التصحيحية المطلوبة
            $table->date('corrective_action_deadline')->nullable(); // موعد الإجراءات التصحيحية
            $table->enum('corrective_action_status', ['not_required', 'pending', 'in_progress', 'completed', 'overdue'])->default('not_required'); // حالة الإجراءات التصحيحية
            $table->text('company_response')->nullable(); // رد الشركة
            $table->date('company_response_date')->nullable(); // تاريخ رد الشركة
            $table->boolean('follow_up_required')->default(false); // يتطلب متابعة
            $table->date('follow_up_date')->nullable(); // تاريخ المتابعة
            $table->enum('follow_up_status', ['not_required', 'scheduled', 'completed'])->default('not_required'); // حالة المتابعة
            $table->text('regulatory_action')->nullable(); // الإجراء التنظيمي
            $table->enum('regulatory_action_type', ['none', 'warning_letter', 'suspension', 'revocation', 'fine', 'prosecution'])->nullable(); // نوع الإجراء التنظيمي
            $table->decimal('fine_amount', 12, 2)->nullable(); // مبلغ الغرامة
            $table->boolean('public_disclosure')->default(false); // إفصاح عام
            $table->date('public_disclosure_date')->nullable(); // تاريخ الإفصاح العام
            $table->string('inspection_report_number')->nullable(); // رقم تقرير التفتيش
            $table->date('report_issue_date')->nullable(); // تاريخ إصدار التقرير
            $table->json('documents')->nullable(); // المستندات المرفقة
            $table->text('notes')->nullable(); // ملاحظات
            $table->enum('status', ['active', 'closed', 'archived'])->default('active'); // حالة السجل
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['inspection_type', 'inspection_status'], 'idx_inspection_type_status');
            $table->index('pharmaceutical_company_id', 'idx_company_id');
            $table->index('pharmaceutical_product_id', 'idx_product_id');
            $table->index('pharmaceutical_batch_id', 'idx_batch_id');
            $table->index('inspection_date', 'idx_inspection_date');
            $table->index('inspection_result', 'idx_inspection_result');
            $table->index('follow_up_date', 'idx_follow_up_date');
            $table->index('created_by', 'idx_created_by');

            // Foreign keys
            $table->foreign('pharmaceutical_company_id')->references('id')->on('pharmaceutical_companies')->onDelete('cascade');
            $table->foreign('pharmaceutical_product_id')->references('id')->on('pharmaceutical_products')->onDelete('cascade');
            $table->foreign('pharmaceutical_batch_id')->references('id')->on('pharmaceutical_batches')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_inspections');
    }
};
