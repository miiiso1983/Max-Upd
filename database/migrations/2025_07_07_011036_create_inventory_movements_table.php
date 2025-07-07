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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // رقم المرجع
            $table->unsignedBigInteger('warehouse_id'); // المخزن
            $table->unsignedBigInteger('product_id'); // المنتج
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment', 'return', 'damage', 'loss']); // نوع الحركة
            $table->enum('source_type', ['purchase', 'sale', 'transfer', 'adjustment', 'return', 'production', 'manual']); // مصدر الحركة
            $table->unsignedBigInteger('source_id')->nullable(); // معرف المصدر (فاتورة، طلب، إلخ)
            $table->decimal('quantity', 15, 3); // الكمية
            $table->decimal('unit_cost', 15, 2)->default(0); // تكلفة الوحدة
            $table->decimal('total_cost', 15, 2)->default(0); // إجمالي التكلفة
            $table->decimal('quantity_before', 15, 3)->default(0); // الكمية قبل الحركة
            $table->decimal('quantity_after', 15, 3)->default(0); // الكمية بعد الحركة
            $table->unsignedBigInteger('from_warehouse_id')->nullable(); // المخزن المصدر (للنقل)
            $table->unsignedBigInteger('to_warehouse_id')->nullable(); // المخزن الهدف (للنقل)
            $table->date('movement_date'); // تاريخ الحركة
            $table->time('movement_time')->nullable(); // وقت الحركة
            $table->string('batch_number')->nullable(); // رقم الدفعة
            $table->date('expiry_date')->nullable(); // تاريخ انتهاء الصلاحية
            $table->string('serial_numbers')->nullable(); // الأرقام التسلسلية
            $table->text('reason')->nullable(); // سبب الحركة
            $table->text('notes')->nullable(); // ملاحظات
            $table->json('attributes')->nullable(); // خصائص إضافية
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed'); // حالة الحركة
            $table->unsignedBigInteger('approved_by')->nullable(); // معتمد من
            $table->timestamp('approved_at')->nullable(); // تاريخ الاعتماد
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['warehouse_id', 'product_id']);
            $table->index(['type', 'source_type']);
            $table->index('movement_date');
            $table->index('status');
            $table->index('source_id');
            $table->index('from_warehouse_id');
            $table->index('to_warehouse_id');
            $table->index('created_by');

            // Foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
