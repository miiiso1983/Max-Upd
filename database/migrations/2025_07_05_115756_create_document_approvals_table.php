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
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->unsignedBigInteger('workflow_id')->nullable();
            $table->integer('step_number')->default(1);
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->enum('approver_type', ['user', 'role', 'department', 'group'])->default('user');
            $table->string('approver_role')->nullable();
            $table->enum('status', [
                'pending', 'approved', 'rejected', 'delegated',
                'escalated', 'skipped', 'cancelled'
            ])->default('pending');
            $table->enum('action', [
                'approve', 'reject', 'delegate', 'escalate',
                'request_changes', 'skip'
            ])->nullable();
            $table->text('comments')->nullable();
            $table->text('comments_ar')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('escalation_reason')->nullable();
            $table->foreignId('delegation_from')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('delegation_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('delegation_reason')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent', 'critical'])->default('normal');
            $table->boolean('notification_sent')->default(false);
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'status']);
            $table->index(['approver_id', 'status']);
            $table->index(['workflow_id', 'step_number']);
            $table->index(['due_date']);
            $table->index(['priority', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approvals');
    }
};
