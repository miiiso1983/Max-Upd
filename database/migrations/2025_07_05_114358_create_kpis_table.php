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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('category', ['sales', 'financial', 'inventory', 'customer', 'operational', 'hr', 'quality'])->default('operational');
            $table->enum('metric_type', ['count', 'sum', 'average', 'percentage', 'ratio', 'rate'])->default('count');
            $table->string('calculation_method')->nullable();
            $table->string('data_source')->nullable();
            $table->json('query')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('previous_value', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('unit_ar')->nullable();
            $table->enum('format', ['number', 'currency', 'percentage', 'decimal', 'integer'])->default('number');
            $table->enum('trend_direction', ['up', 'down', 'stable'])->default('stable');
            $table->enum('status', ['excellent', 'good', 'warning', 'critical'])->default('good');
            $table->decimal('threshold_green', 15, 2)->nullable();
            $table->decimal('threshold_yellow', 15, 2)->nullable();
            $table->decimal('threshold_red', 15, 2)->nullable();
            $table->enum('frequency', ['real_time', 'hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('daily');
            $table->timestamp('last_calculated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['status']);
            $table->index(['last_calculated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
