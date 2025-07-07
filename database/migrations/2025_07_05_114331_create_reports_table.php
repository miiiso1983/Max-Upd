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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('type', ['tabular', 'summary', 'analytical', 'dashboard', 'export'])->default('tabular');
            $table->enum('category', ['sales', 'inventory', 'financial', 'hr', 'crm', 'operational', 'compliance'])->default('operational');
            $table->string('data_source')->nullable();
            $table->json('query')->nullable();
            $table->json('parameters')->nullable();
            $table->json('columns')->nullable();
            $table->json('filters')->nullable();
            $table->json('sorting')->nullable();
            $table->json('grouping')->nullable();
            $table->json('aggregations')->nullable();
            $table->enum('format', ['pdf', 'excel', 'csv', 'html', 'json'])->default('html');
            $table->string('template')->nullable();
            $table->json('schedule')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'category']);
            $table->index(['is_public', 'is_active']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
