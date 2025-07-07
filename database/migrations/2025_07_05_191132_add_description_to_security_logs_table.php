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
        Schema::table('security_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('security_logs', 'description')) {
                $table->text('description')->nullable()->after('event');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_logs', function (Blueprint $table) {
            if (Schema::hasColumn('security_logs', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
