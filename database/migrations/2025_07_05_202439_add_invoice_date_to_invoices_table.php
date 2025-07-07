<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add invoice_date column as alias to issue_date
            $table->date('invoice_date')->nullable()->after('supplier_id');
        });

        // Copy data from issue_date to invoice_date
        DB::statement('UPDATE invoices SET invoice_date = issue_date WHERE invoice_date IS NULL');

        // Make invoice_date not nullable
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('invoice_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_date');
        });
    }
};
