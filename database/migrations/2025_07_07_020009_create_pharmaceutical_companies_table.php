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
        Schema::create('pharmaceutical_companies', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique(); // رقم التسجيل
            $table->string('name'); // اسم الشركة
            $table->string('name_ar')->nullable(); // الاسم بالعربي
            $table->string('name_en')->nullable(); // الاسم بالإنجليزي
            $table->string('trade_name')->nullable(); // الاسم التجاري
            $table->string('trade_name_ar')->nullable(); // الاسم التجاري بالعربي
            $table->text('description')->nullable(); // وصف الشركة
            $table->string('country_of_origin'); // بلد المنشأ
            $table->string('manufacturer_type'); // نوع المصنع (local, international)
            $table->enum('company_type', ['manufacturer', 'distributor', 'importer', 'exporter']); // نوع الشركة
            $table->string('license_number')->unique(); // رقم الترخيص
            $table->date('license_issue_date'); // تاريخ إصدار الترخيص
            $table->date('license_expiry_date'); // تاريخ انتهاء الترخيص
            $table->enum('license_status', ['active', 'expired', 'suspended', 'cancelled'])->default('active'); // حالة الترخيص
            $table->string('regulatory_authority'); // الجهة المنظمة
            $table->string('gmp_certificate')->nullable(); // شهادة GMP
            $table->date('gmp_expiry_date')->nullable(); // تاريخ انتهاء GMP
            $table->string('iso_certificate')->nullable(); // شهادة ISO
            $table->date('iso_expiry_date')->nullable(); // تاريخ انتهاء ISO
            $table->text('address'); // العنوان
            $table->string('city'); // المدينة
            $table->string('state')->nullable(); // الولاية/المحافظة
            $table->string('postal_code')->nullable(); // الرمز البريدي
            $table->string('phone'); // الهاتف
            $table->string('fax')->nullable(); // الفاكس
            $table->string('email'); // البريد الإلكتروني
            $table->string('website')->nullable(); // الموقع الإلكتروني
            $table->string('contact_person'); // الشخص المسؤول
            $table->string('contact_person_title')->nullable(); // منصب الشخص المسؤول
            $table->string('contact_phone')->nullable(); // هاتف الشخص المسؤول
            $table->string('contact_email')->nullable(); // بريد الشخص المسؤول
            $table->string('pharmacist_name')->nullable(); // اسم الصيدلاني المسؤول
            $table->string('pharmacist_license')->nullable(); // رقم ترخيص الصيدلاني
            $table->date('pharmacist_license_expiry')->nullable(); // تاريخ انتهاء ترخيص الصيدلاني
            $table->json('product_categories')->nullable(); // فئات المنتجات المسموحة
            $table->json('therapeutic_areas')->nullable(); // المجالات العلاجية
            $table->decimal('annual_turnover', 15, 2)->nullable(); // حجم الأعمال السنوي
            $table->integer('employee_count')->nullable(); // عدد الموظفين
            $table->text('notes')->nullable(); // ملاحظات
            $table->json('documents')->nullable(); // المستندات المرفقة
            $table->enum('status', ['active', 'inactive', 'under_review', 'suspended'])->default('active'); // حالة الشركة
            $table->date('last_inspection_date')->nullable(); // تاريخ آخر تفتيش
            $table->date('next_inspection_date')->nullable(); // تاريخ التفتيش القادم
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium'); // مستوى المخاطر
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['license_status', 'status']);
            $table->index('license_expiry_date');
            $table->index('gmp_expiry_date');
            $table->index('country_of_origin');
            $table->index('company_type');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_companies');
    }
};
