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
        Schema::create('customer_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_rep_id');
            $table->unsignedBigInteger('customer_id');
            $table->datetime('visit_date');
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated duration
            $table->enum('visit_type', ['scheduled', 'unscheduled', 'follow_up', 'emergency', 'collection'])->default('scheduled');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('planned');
            $table->enum('outcome', ['successful', 'partially_successful', 'unsuccessful', 'rescheduled'])->nullable();
            
            // Location tracking
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->integer('location_accuracy_meters')->nullable();
            $table->boolean('location_verified')->default(false);
            
            // Visit details
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('visit_purpose')->nullable();
            $table->text('discussion_points')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->text('visit_notes')->nullable();
            $table->text('next_action_required')->nullable();
            $table->date('next_visit_date')->nullable();
            
            // Business outcomes
            $table->boolean('order_created')->default(false);
            $table->decimal('order_amount', 12, 2)->default(0);
            $table->boolean('payment_collected')->default(false);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->boolean('complaint_received')->default(false);
            $table->text('complaint_details')->nullable();
            
            // Media attachments
            $table->json('photos')->nullable(); // Array of photo URLs
            $table->json('documents')->nullable(); // Array of document URLs
            $table->json('voice_notes')->nullable(); // Array of voice note URLs
            
            // Ratings and feedback
            $table->integer('customer_satisfaction_rating')->nullable(); // 1-5 scale
            $table->integer('visit_quality_rating')->nullable(); // Self-assessment 1-5 scale
            $table->text('improvement_suggestions')->nullable();
            
            // System fields
            $table->json('device_info')->nullable(); // Mobile device information
            $table->string('app_version')->nullable();
            $table->boolean('synced')->default(false);
            $table->datetime('synced_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['sales_rep_id', 'visit_date']);
            $table->index(['customer_id', 'visit_date']);
            $table->index(['visit_date', 'status']);
            $table->index(['status', 'outcome']);
            $table->index(['check_in_latitude', 'check_in_longitude']);
            $table->index(['synced', 'created_at']);
            $table->index(['visit_type', 'status']);

            // Foreign keys
            $table->foreign('sales_rep_id')->references('id')->on('sales_representatives')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_visits');
    }
};
