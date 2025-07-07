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
        Schema::create('product_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم العلامة التجارية
            $table->string('name_ar')->nullable(); // الاسم بالعربي
            $table->string('code')->unique(); // كود العلامة التجارية
            $table->text('description')->nullable(); // وصف العلامة التجارية
            $table->text('description_ar')->nullable(); // الوصف بالعربي
            $table->string('logo')->nullable(); // شعار العلامة التجارية
            $table->string('website')->nullable(); // موقع الويب
            $table->string('email')->nullable(); // البريد الإلكتروني
            $table->string('phone')->nullable(); // الهاتف
            $table->text('address')->nullable(); // العنوان
            $table->string('country')->nullable(); // البلد
            $table->enum('status', ['active', 'inactive'])->default('active'); // حالة العلامة التجارية
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->json('attributes')->nullable(); // خصائص إضافية
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('sort_order');
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
        Schema::dropIfExists('product_brands');
    }
};
