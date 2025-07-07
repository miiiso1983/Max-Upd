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
        Schema::create('pharmaceutical_products', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique(); // رقم التسجيل
            $table->string('trade_name'); // الاسم التجاري
            $table->string('trade_name_ar')->nullable(); // الاسم التجاري بالعربي
            $table->string('generic_name'); // الاسم العلمي
            $table->string('generic_name_ar')->nullable(); // الاسم العلمي بالعربي
            $table->string('active_ingredient'); // المادة الفعالة
            $table->string('active_ingredient_ar')->nullable(); // المادة الفعالة بالعربي
            $table->string('strength'); // التركيز/القوة
            $table->string('dosage_form'); // الشكل الصيدلاني
            $table->string('dosage_form_ar')->nullable(); // الشكل الصيدلاني بالعربي
            $table->string('route_of_administration'); // طريقة الإعطاء
            $table->string('route_of_administration_ar')->nullable(); // طريقة الإعطاء بالعربي
            $table->string('pack_size'); // حجم العبوة
            $table->string('pack_type'); // نوع العبوة
            $table->unsignedBigInteger('pharmaceutical_company_id'); // الشركة المصنعة
            $table->string('manufacturer_name'); // اسم المصنع
            $table->string('country_of_origin'); // بلد المنشأ
            $table->string('therapeutic_class'); // الفئة العلاجية
            $table->string('therapeutic_class_ar')->nullable(); // الفئة العلاجية بالعربي
            $table->string('atc_code')->nullable(); // كود ATC
            $table->text('indication')->nullable(); // دواعي الاستعمال
            $table->text('indication_ar')->nullable(); // دواعي الاستعمال بالعربي
            $table->text('contraindication')->nullable(); // موانع الاستعمال
            $table->text('contraindication_ar')->nullable(); // موانع الاستعمال بالعربي
            $table->text('side_effects')->nullable(); // الآثار الجانبية
            $table->text('side_effects_ar')->nullable(); // الآثار الجانبية بالعربي
            $table->text('dosage_instructions')->nullable(); // تعليمات الجرعة
            $table->text('dosage_instructions_ar')->nullable(); // تعليمات الجرعة بالعربي
            $table->text('storage_conditions'); // شروط التخزين
            $table->text('storage_conditions_ar')->nullable(); // شروط التخزين بالعربي
            $table->integer('shelf_life_months'); // مدة الصلاحية بالأشهر
            $table->string('license_number')->unique(); // رقم الترخيص
            $table->date('license_issue_date'); // تاريخ إصدار الترخيص
            $table->date('license_expiry_date'); // تاريخ انتهاء الترخيص
            $table->enum('license_status', ['active', 'expired', 'suspended', 'cancelled'])->default('active'); // حالة الترخيص
            $table->string('regulatory_authority'); // الجهة المنظمة
            $table->enum('prescription_status', ['prescription', 'otc', 'controlled']); // حالة الوصفة
            $table->string('controlled_substance_schedule')->nullable(); // جدولة المواد المخدرة
            $table->boolean('requires_cold_chain')->default(false); // يتطلب سلسلة تبريد
            $table->decimal('min_temperature', 5, 2)->nullable(); // أدنى درجة حرارة
            $table->decimal('max_temperature', 5, 2)->nullable(); // أعلى درجة حرارة
            $table->boolean('light_sensitive')->default(false); // حساس للضوء
            $table->boolean('moisture_sensitive')->default(false); // حساس للرطوبة
            $table->decimal('price_ceiling', 10, 2)->nullable(); // السعر الأقصى المحدد
            $table->decimal('wholesale_price', 10, 2)->nullable(); // سعر الجملة
            $table->decimal('retail_price', 10, 2)->nullable(); // سعر التجزئة
            $table->string('barcode')->nullable(); // الباركود
            $table->string('ndc_number')->nullable(); // رقم NDC
            $table->boolean('is_generic')->default(false); // دواء جنيس
            $table->string('reference_product')->nullable(); // المنتج المرجعي
            $table->boolean('bioequivalence_required')->default(false); // يتطلب دراسة تكافؤ حيوي
            $table->date('bioequivalence_expiry')->nullable(); // تاريخ انتهاء دراسة التكافؤ الحيوي
            $table->enum('market_status', ['marketed', 'not_marketed', 'discontinued', 'withdrawn'])->default('not_marketed'); // حالة التسويق
            $table->date('market_launch_date')->nullable(); // تاريخ طرح في السوق
            $table->text('withdrawal_reason')->nullable(); // سبب السحب من السوق
            $table->json('documents')->nullable(); // المستندات المرفقة
            $table->text('notes')->nullable(); // ملاحظات
            $table->enum('status', ['active', 'inactive', 'under_review', 'suspended'])->default('under_review'); // حالة المنتج
            $table->date('last_inspection_date')->nullable(); // تاريخ آخر تفتيش
            $table->date('next_inspection_date')->nullable(); // تاريخ التفتيش القادم
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium'); // مستوى المخاطر
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['license_status', 'status']);
            $table->index('license_expiry_date');
            $table->index('pharmaceutical_company_id');
            $table->index('therapeutic_class');
            $table->index('prescription_status');
            $table->index('market_status');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('pharmaceutical_company_id')->references('id')->on('pharmaceutical_companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_products');
    }
};
