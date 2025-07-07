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
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->onDelete('cascade');
            $table->decimal('system_quantity', 10, 2);
            $table->decimal('counted_quantity', 10, 2)->default(0);
            $table->decimal('difference', 10, 2)->default(0);
            $table->decimal('variance_percentage', 5, 2)->default(0);
            $table->enum('count_type', ['full', 'cycle', 'spot', 'batch'])->default('cycle');
            $table->enum('status', ['pending', 'counted', 'verified', 'adjusted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('counted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['status']);
            $table->index(['count_type']);
            $table->index(['counted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};
