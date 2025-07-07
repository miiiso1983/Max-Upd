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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('type', ['receiving', 'storage', 'picking', 'shipping', 'quarantine', 'damage', 'return', 'staging'])->default('storage');
            $table->string('zone')->nullable();
            $table->string('aisle')->nullable();
            $table->string('rack')->nullable();
            $table->string('shelf')->nullable();
            $table->string('bin')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('capacity', 10, 2)->default(0);
            $table->decimal('current_utilization', 10, 2)->default(0);
            $table->boolean('temperature_controlled')->default(false);
            $table->decimal('temperature_min', 5, 1)->nullable();
            $table->decimal('temperature_max', 5, 1)->nullable();
            $table->boolean('humidity_controlled')->default(false);
            $table->decimal('humidity_min', 5, 1)->nullable();
            $table->decimal('humidity_max', 5, 1)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pickable')->default(true);
            $table->boolean('is_receivable')->default(true);
            $table->integer('priority')->default(0);
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['warehouse_id', 'type']);
            $table->index(['zone', 'aisle', 'rack']);
            $table->index(['is_active']);
            $table->index(['priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
