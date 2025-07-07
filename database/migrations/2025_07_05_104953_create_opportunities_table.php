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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('opportunity_number')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->enum('stage', ['prospecting', 'qualification', 'needs_analysis', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('prospecting');
            $table->integer('probability')->default(10);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IQD');
            $table->date('expected_close_date');
            $table->date('actual_close_date')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('source_ar', 100)->nullable();
            $table->enum('type', ['new_business', 'existing_business', 'renewal', 'upsell', 'cross_sell'])->default('new_business');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->string('lost_reason')->nullable();
            $table->string('lost_reason_ar')->nullable();
            $table->string('competitor')->nullable();
            $table->string('competitor_ar')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage', 'priority']);
            $table->index(['customer_id']);
            $table->index(['assigned_to']);
            $table->index(['expected_close_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
