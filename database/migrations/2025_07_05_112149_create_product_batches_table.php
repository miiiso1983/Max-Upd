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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('batch_number');
            $table->string('lot_number')->nullable();
            $table->decimal('quantity_received', 10, 2);
            $table->decimal('quantity_remaining', 10, 2);
            $table->decimal('quantity_reserved', 10, 2)->default(0);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('received_date');
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('purchase_order_id')->nullable();
            $table->string('storage_location')->nullable();
            $table->text('storage_conditions')->nullable();
            $table->decimal('temperature_min', 5, 1)->nullable();
            $table->decimal('temperature_max', 5, 1)->nullable();
            $table->decimal('humidity_min', 5, 1)->nullable();
            $table->decimal('humidity_max', 5, 1)->nullable();
            $table->boolean('is_quarantined')->default(false);
            $table->text('quarantine_reason')->nullable();
            $table->enum('quality_status', ['pending', 'approved', 'rejected', 'on_hold'])->default('pending');
            $table->text('quality_notes')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['batch_number']);
            $table->index(['expiry_date']);
            $table->index(['quality_status']);
            $table->index(['is_quarantined']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
