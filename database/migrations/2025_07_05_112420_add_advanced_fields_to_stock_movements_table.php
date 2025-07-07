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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('warehouse_id')->constrained('product_batches')->onDelete('set null');
            $table->string('movement_type')->nullable()->after('type');
            $table->string('location_from')->nullable()->after('to_warehouse_id');
            $table->string('location_to')->nullable()->after('location_from');
            $table->string('barcode_scanned')->nullable()->after('location_to');
            $table->text('notes_ar')->nullable()->after('notes');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');

            $table->index(['batch_id']);
            $table->index(['movement_type']);
            $table->index(['barcode_scanned']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'batch_id',
                'movement_type',
                'location_from',
                'location_to',
                'barcode_scanned',
                'notes_ar',
                'updated_by'
            ]);
        });
    }
};
