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
        Schema::create('document_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_workflow_id')->constrained('document_workflows')->onDelete('cascade');
            $table->integer('step_number');
            $table->string('step_name');
            $table->string('step_name_ar')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected', 'cancelled', 'reassigned'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->json('step_data')->nullable();
            $table->timestamps();

            $table->index(['document_workflow_id', 'step_number']);
            $table->index(['status']);
            $table->index(['assigned_to']);
            $table->index(['due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_workflow_steps');
    }
};
