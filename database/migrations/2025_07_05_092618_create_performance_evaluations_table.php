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
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('evaluator_id');
            $table->enum('period_type', ['quarterly', 'semi_annual', 'annual'])->default('annual');
            $table->date('evaluation_period_start');
            $table->date('evaluation_period_end');
            $table->decimal('overall_rating', 3, 1)->nullable();
            $table->decimal('goals_achievement', 3, 1)->nullable();
            $table->decimal('technical_skills', 3, 1)->nullable();
            $table->decimal('communication_skills', 3, 1)->nullable();
            $table->decimal('teamwork', 3, 1)->nullable();
            $table->decimal('leadership', 3, 1)->nullable();
            $table->decimal('initiative', 3, 1)->nullable();
            $table->decimal('punctuality', 3, 1)->nullable();
            $table->decimal('quality_of_work', 3, 1)->nullable();
            $table->decimal('productivity', 3, 1)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_for_next_period')->nullable();
            $table->text('training_recommendations')->nullable();
            $table->text('evaluator_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('hr_comments')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');
            $table->datetime('submitted_at')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'period_type']);
            $table->index(['evaluation_period_start', 'evaluation_period_end'], 'perf_eval_period_idx');
            $table->index(['status', 'evaluation_period_start'], 'perf_eval_status_start_idx');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('evaluator_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
