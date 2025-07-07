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
        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('action', [
                'view', 'download', 'print', 'share', 'edit', 'delete',
                'upload', 'approve', 'reject', 'sign', 'comment',
                'version_create', 'lock', 'unlock', 'archive', 'restore',
                'access_denied', 'search', 'export'
            ]);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('location')->nullable();
            $table->json('device_info')->nullable();
            $table->enum('access_method', [
                'web', 'api', 'mobile', 'email', 'ftp', 'sync'
            ])->default('web');
            $table->integer('duration')->nullable(); // in seconds
            $table->integer('pages_viewed')->nullable();
            $table->boolean('download_attempted')->default(false);
            $table->boolean('download_successful')->default(false);
            $table->boolean('print_attempted')->default(false);
            $table->boolean('print_successful')->default(false);
            $table->boolean('share_attempted')->default(false);
            $table->boolean('share_successful')->default(false);
            $table->text('access_denied_reason')->nullable();
            $table->enum('security_level', [
                'low', 'medium', 'high', 'critical'
            ])->default('medium');
            $table->json('compliance_flags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'action']);
            $table->index(['user_id', 'action']);
            $table->index(['action', 'created_at']);
            $table->index(['security_level']);
            $table->index(['access_method']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_access_logs');
    }
};
