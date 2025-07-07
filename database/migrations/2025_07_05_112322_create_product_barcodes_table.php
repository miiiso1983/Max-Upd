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
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('barcode')->unique();
            $table->enum('barcode_type', ['ean13', 'ean8', 'upc', 'code128', 'code39', 'qr', 'datamatrix', 'custom'])->default('ean13');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('unit_of_measure')->nullable();
            $table->decimal('quantity_per_unit', 10, 4)->default(1);
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['product_id']);
            $table->index(['barcode_type']);
            $table->index(['is_primary']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');
    }
};
