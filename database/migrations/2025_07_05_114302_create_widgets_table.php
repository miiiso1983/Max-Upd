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
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('dashboards')->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->enum('type', ['kpi', 'chart_line', 'chart_bar', 'chart_pie', 'chart_donut', 'chart_area', 'table', 'list', 'gauge', 'progress', 'map', 'calendar', 'timeline', 'funnel', 'heatmap', 'treemap', 'scatter', 'radar'])->default('kpi');
            $table->enum('data_source', ['sales', 'inventory', 'financial', 'hr', 'crm', 'custom'])->default('custom');
            $table->json('query')->nullable();
            $table->json('config')->nullable();
            $table->json('position')->nullable();
            $table->json('size')->nullable();
            $table->integer('refresh_interval')->default(300); // 5 minutes
            $table->boolean('auto_refresh')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['dashboard_id', 'is_active']);
            $table->index(['type']);
            $table->index(['data_source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
