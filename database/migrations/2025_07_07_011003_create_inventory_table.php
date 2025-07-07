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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id'); // المخزن
            $table->unsignedBigInteger('product_id'); // المنتج
            $table->decimal('quantity', 15, 3)->default(0); // الكمية المتاحة
            $table->decimal('reserved_quantity', 15, 3)->default(0); // الكمية المحجوزة
            $table->decimal('available_quantity', 15, 3)->default(0); // الكمية المتاحة للبيع
            $table->decimal('cost_price', 15, 2)->default(0); // سعر التكلفة
            $table->decimal('average_cost', 15, 2)->default(0); // متوسط التكلفة
            $table->decimal('last_cost', 15, 2)->default(0); // آخر تكلفة
            $table->string('location')->nullable(); // موقع المنتج في المخزن
            $table->string('bin')->nullable(); // الرف
            $table->string('aisle')->nullable(); // الممر
            $table->string('shelf')->nullable(); // الرف
            $table->date('last_movement_date')->nullable(); // تاريخ آخر حركة
            $table->date('last_count_date')->nullable(); // تاريخ آخر جرد
            $table->decimal('last_count_quantity', 15, 3)->nullable(); // كمية آخر جرد
            $table->date('expiry_date')->nullable(); // تاريخ انتهاء الصلاحية
            $table->string('batch_number')->nullable(); // رقم الدفعة
            $table->string('serial_numbers')->nullable(); // الأرقام التسلسلية
            $table->json('attributes')->nullable(); // خصائص إضافية
            $table->text('notes')->nullable(); // ملاحظات
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['warehouse_id', 'product_id', 'batch_number'], 'inventory_unique');

            // Indexes
            $table->index(['warehouse_id', 'product_id']);
            $table->index('quantity');
            $table->index('available_quantity');
            $table->index('expiry_date');
            $table->index('last_movement_date');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
