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
        Schema::create('rep_customer_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_rep_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('assigned_date');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('assignment_type', ['primary', 'secondary', 'temporary', 'backup'])->default('primary');
            $table->boolean('is_active')->default(true);
            $table->integer('visit_frequency_days')->default(30); // How often to visit (in days)
            $table->decimal('monthly_target', 12, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0); // Override default commission for this customer
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('visit_preferences')->nullable(); // {preferred_days, preferred_times, contact_person}
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['sales_rep_id', 'is_active']);
            $table->index(['customer_id', 'is_active']);
            $table->index(['assigned_date', 'is_active']);
            $table->index(['effective_from', 'effective_to']);
            $table->index(['priority', 'is_active']);
            $table->unique(['sales_rep_id', 'customer_id', 'assignment_type'], 'unique_rep_customer_assignment');

            // Foreign keys
            $table->foreign('sales_rep_id')->references('id')->on('sales_representatives')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rep_customer_assignments');
    }
};
