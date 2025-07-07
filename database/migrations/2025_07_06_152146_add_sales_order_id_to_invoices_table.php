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
        Schema::table('invoices', function (Blueprint $table) {
            // Add sales_order_id column
            $table->unsignedBigInteger('sales_order_id')->nullable()->after('supplier_id');

            // Add index for performance
            $table->index('sales_order_id');

            // Add foreign key constraint if sales_orders table exists
            if (Schema::hasTable('sales_orders')) {
                $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key first if it exists
            if (Schema::hasTable('sales_orders')) {
                $table->dropForeign(['sales_order_id']);
            }

            // Drop index
            $table->dropIndex(['sales_order_id']);

            // Drop column
            $table->dropColumn('sales_order_id');
        });
    }
};
