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
        Schema::create('rep_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_rep_id');
            $table->date('metric_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('daily');
            
            // Visit metrics
            $table->integer('visits_planned')->default(0);
            $table->integer('visits_completed')->default(0);
            $table->integer('visits_cancelled')->default(0);
            $table->integer('visits_no_show')->default(0);
            $table->decimal('visit_completion_rate', 5, 2)->default(0); // Percentage
            $table->integer('average_visit_duration_minutes')->default(0);
            $table->integer('unique_customers_visited')->default(0);
            
            // Sales metrics
            $table->integer('orders_created')->default(0);
            $table->decimal('total_order_value', 12, 2)->default(0);
            $table->decimal('average_order_value', 12, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0); // Orders/Visits percentage
            $table->integer('new_customers_acquired')->default(0);
            $table->decimal('target_achievement_rate', 5, 2)->default(0); // Percentage
            
            // Collection metrics
            $table->integer('payments_collected')->default(0);
            $table->decimal('total_collection_amount', 12, 2)->default(0);
            $table->decimal('collection_efficiency', 5, 2)->default(0); // Percentage
            $table->integer('overdue_collections')->default(0);
            
            // Task metrics
            $table->integer('tasks_assigned')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_overdue')->default(0);
            $table->decimal('task_completion_rate', 5, 2)->default(0); // Percentage
            $table->integer('average_task_completion_time_hours')->default(0);
            
            // Territory coverage
            $table->decimal('territory_coverage_percentage', 5, 2)->default(0);
            $table->integer('total_territory_customers')->default(0);
            $table->integer('customers_visited')->default(0);
            $table->decimal('distance_traveled_km', 8, 2)->default(0);
            
            // Quality metrics
            $table->decimal('customer_satisfaction_avg', 3, 2)->default(0); // 1-5 scale
            $table->decimal('visit_quality_avg', 3, 2)->default(0); // 1-5 scale
            $table->integer('complaints_received')->default(0);
            $table->integer('compliments_received')->default(0);
            
            // Financial metrics
            $table->decimal('commission_earned', 10, 2)->default(0);
            $table->decimal('expenses_claimed', 10, 2)->default(0);
            $table->decimal('roi_percentage', 5, 2)->default(0); // Return on investment
            
            // Efficiency metrics
            $table->decimal('calls_per_day_avg', 5, 2)->default(0);
            $table->decimal('revenue_per_visit', 10, 2)->default(0);
            $table->decimal('cost_per_visit', 10, 2)->default(0);
            $table->integer('working_hours_logged')->default(0);
            $table->decimal('productivity_score', 5, 2)->default(0); // Overall productivity 1-100
            
            // Ranking and comparison
            $table->integer('rank_in_team')->nullable();
            $table->integer('rank_in_region')->nullable();
            $table->integer('rank_nationally')->nullable();
            $table->decimal('performance_vs_target', 5, 2)->default(0); // Percentage
            $table->decimal('performance_vs_peers', 5, 2)->default(0); // Percentage
            
            // System fields
            $table->datetime('calculated_at');
            $table->boolean('is_final')->default(false); // Whether this is final or preliminary
            $table->json('calculation_details')->nullable(); // How metrics were calculated
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['sales_rep_id', 'metric_date']);
            $table->index(['metric_date', 'period_type']);
            $table->index(['period_type', 'is_final']);
            $table->index(['sales_rep_id', 'period_type', 'metric_date']);
            $table->unique(['sales_rep_id', 'metric_date', 'period_type'], 'unique_rep_metric_period');

            // Foreign keys
            $table->foreign('sales_rep_id')->references('id')->on('sales_representatives')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rep_performance_metrics');
    }
};
