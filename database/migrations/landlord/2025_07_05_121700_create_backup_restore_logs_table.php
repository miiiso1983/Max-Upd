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
        Schema::create('backup_restore_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('backup_id')->nullable()->constrained('tenant_backups')->onDelete('set null');
            $table->enum('operation_type', ['backup', 'restore', 'cleanup', 'verification']);
            $table->enum('status', ['started', 'in_progress', 'completed', 'failed', 'cancelled']);
            $table->text('operation_details')->nullable();
            $table->json('operation_metadata')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('operation_log')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'operation_type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['operation_type', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_restore_logs');
    }
};
