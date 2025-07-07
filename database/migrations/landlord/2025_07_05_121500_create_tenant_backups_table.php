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
        Schema::create('tenant_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('backup_name');
            $table->string('backup_type')->default('full'); // full, incremental, differential
            $table->string('trigger_type')->default('manual'); // manual, scheduled, automatic
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('compression_type')->default('gzip'); // gzip, zip, none
            $table->boolean('encrypted')->default(true);
            $table->string('encryption_method')->default('AES-256-CBC');
            $table->text('encryption_key_hash')->nullable(); // Hashed encryption key for verification
            $table->json('backup_metadata')->nullable(); // Additional backup information
            $table->json('database_info')->nullable(); // Database structure info
            $table->json('file_info')->nullable(); // File system backup info
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('error_message')->nullable();
            $table->text('backup_log')->nullable();
            $table->string('checksum')->nullable(); // File integrity verification
            $table->timestamp('expires_at')->nullable(); // Backup retention policy
            $table->boolean('is_restorable')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('restored_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('restored_at')->nullable();
            $table->text('restore_notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'backup_type']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['status', 'trigger_type']);
            $table->index('expires_at');
            $table->index('is_restorable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_backups');
    }
};
