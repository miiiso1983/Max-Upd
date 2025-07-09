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
        Schema::create('rep_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_rep_id');
            $table->unsignedBigInteger('assigned_by');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['visit', 'follow_up', 'collection', 'report', 'training', 'meeting', 'other'])->default('visit');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'overdue'])->default('pending');
            
            // Related entities
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->unsignedBigInteger('related_visit_id')->nullable();
            $table->unsignedBigInteger('related_order_id')->nullable();
            
            // Timing
            $table->datetime('due_date');
            $table->datetime('reminder_date')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->integer('actual_duration_minutes')->nullable();
            
            // Location (if applicable)
            $table->decimal('target_latitude', 10, 8)->nullable();
            $table->decimal('target_longitude', 11, 8)->nullable();
            $table->string('target_address')->nullable();
            
            // Task details
            $table->json('requirements')->nullable(); // What needs to be done
            $table->json('checklist')->nullable(); // Task checklist items
            $table->text('instructions')->nullable();
            $table->text('completion_notes')->nullable();
            $table->text('supervisor_feedback')->nullable();
            
            // Attachments
            $table->json('attachments')->nullable(); // Files, photos, documents
            $table->json('completion_attachments')->nullable(); // Evidence of completion
            
            // Notifications
            $table->boolean('reminder_sent')->default(false);
            $table->datetime('reminder_sent_at')->nullable();
            $table->boolean('overdue_notification_sent')->default(false);
            
            // Scoring and evaluation
            $table->integer('completion_quality_score')->nullable(); // 1-10 scale
            $table->text('quality_feedback')->nullable();
            $table->boolean('requires_follow_up')->default(false);
            $table->date('follow_up_date')->nullable();
            
            // System fields
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable(); // {frequency, interval, end_date}
            $table->unsignedBigInteger('parent_task_id')->nullable(); // For recurring tasks
            $table->boolean('synced')->default(false);
            $table->datetime('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['sales_rep_id', 'status']);
            $table->index(['assigned_by', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['priority', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['territory_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['is_recurring', 'status']);
            $table->index(['synced', 'created_at']);

            // Foreign keys
            $table->foreign('sales_rep_id')->references('id')->on('sales_representatives')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->foreign('related_visit_id')->references('id')->on('customer_visits')->onDelete('set null');
            $table->foreign('parent_task_id')->references('id')->on('rep_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rep_tasks');
    }
};
