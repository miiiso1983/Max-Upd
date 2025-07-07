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
        Schema::create('purchase_order_receivings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('received_quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->datetime('received_date');
            $table->string('location')->nullable();
            $table->enum('quality_check_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('quality_notes')->nullable();
            $table->unsignedBigInteger('received_by');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'product_id']);
            $table->index(['received_date', 'quality_check_status'], 'po_receivings_date_quality_idx');
            $table->index('batch_number');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receivings');
    }
};
