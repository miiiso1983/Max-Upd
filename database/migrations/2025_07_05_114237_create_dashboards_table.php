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
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('type', ['executive', 'operational', 'financial', 'sales', 'inventory', 'hr', 'custom'])->default('custom');
            $table->json('layout')->nullable();
            $table->json('widgets')->nullable();
            $table->json('filters')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('refresh_interval')->default(300); // 5 minutes
            $table->boolean('auto_refresh')->default(false);
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index(['is_public', 'is_active']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};
