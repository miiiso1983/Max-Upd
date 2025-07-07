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
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('backup_type')->default('full'); // full, incremental, differential
            $table->string('frequency'); // daily, weekly, monthly, custom
            $table->string('cron_expression')->nullable(); // For custom schedules
            $table->time('preferred_time')->default('02:00:00'); // Preferred backup time
            $table->json('days_of_week')->nullable(); // For weekly schedules [1,2,3,4,5]
            $table->integer('day_of_month')->nullable(); // For monthly schedules
            $table->boolean('is_active')->default(true);
            $table->integer('retention_days')->default(30); // How long to keep backups
            $table->integer('max_backups')->default(10); // Maximum number of backups to keep
            $table->boolean('compress_backup')->default(true);
            $table->boolean('encrypt_backup')->default(true);
            $table->json('notification_settings')->nullable(); // Email/SMS notifications
            $table->json('backup_options')->nullable(); // Additional backup options
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('successful_runs')->default(0);
            $table->integer('failed_runs')->default(0);
            $table->text('last_error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'is_active']);
            $table->index(['is_active', 'next_run_at']);
            $table->index('frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
