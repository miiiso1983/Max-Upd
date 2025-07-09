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
        Schema::create('rep_territory_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_rep_id');
            $table->unsignedBigInteger('territory_id');
            $table->date('assigned_date');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('assignment_type', ['primary', 'secondary', 'temporary', 'backup'])->default('primary');
            $table->boolean('is_active')->default(true);
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->integer('target_visits_per_month')->default(0);
            $table->json('working_days')->nullable(); // Array of working days for this territory
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['sales_rep_id', 'is_active']);
            $table->index(['territory_id', 'is_active']);
            $table->index(['assigned_date', 'is_active']);
            $table->index(['effective_from', 'effective_to']);
            $table->unique(['sales_rep_id', 'territory_id', 'assignment_type'], 'unique_rep_territory_assignment');

            // Foreign keys
            $table->foreign('sales_rep_id')->references('id')->on('sales_representatives')->onDelete('cascade');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rep_territory_assignments');
    }
};
