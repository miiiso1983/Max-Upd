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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->string('file_extension', 10);
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->onDelete('set null');
            $table->foreignId('folder_id')->nullable()->constrained('document_folders')->onDelete('set null');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'archived', 'expired'])->default('draft');
            $table->enum('visibility', ['private', 'internal', 'public', 'restricted'])->default('internal');
            $table->boolean('is_template')->default(false);
            $table->string('template_type')->nullable();
            $table->string('version', 10)->default('1.0');
            $table->foreignId('parent_document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->string('checksum', 32);
            $table->integer('download_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibility']);
            $table->index(['category_id']);
            $table->index(['folder_id']);
            $table->index(['related_type', 'related_id']);
            $table->index(['is_template', 'template_type']);
            $table->index(['expires_at']);
            $table->index(['checksum']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
