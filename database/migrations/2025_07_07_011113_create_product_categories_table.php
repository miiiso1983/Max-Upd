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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الفئة
            $table->string('name_ar')->nullable(); // الاسم بالعربي
            $table->string('code')->unique(); // كود الفئة
            $table->text('description')->nullable(); // وصف الفئة
            $table->text('description_ar')->nullable(); // الوصف بالعربي
            $table->unsignedBigInteger('parent_id')->nullable(); // الفئة الأب
            $table->string('image')->nullable(); // صورة الفئة
            $table->string('icon')->nullable(); // أيقونة الفئة
            $table->string('color')->nullable(); // لون الفئة
            $table->enum('status', ['active', 'inactive'])->default('active'); // حالة الفئة
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->json('attributes')->nullable(); // خصائص إضافية
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('parent_id');
            $table->index('status');
            $table->index('sort_order');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
