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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('communication_number')->unique();
            $table->string('related_type');
            $table->unsignedBigInteger('related_id');
            $table->enum('type', ['email', 'phone', 'meeting', 'sms', 'whatsapp', 'letter', 'visit', 'other']);
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('subject');
            $table->string('subject_ar')->nullable();
            $table->text('content');
            $table->text('content_ar')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->text('cc_email')->nullable();
            $table->text('bcc_email')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'delivered', 'read', 'replied', 'completed', 'failed', 'cancelled'])->default('draft');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['related_type', 'related_id']);
            $table->index(['type', 'direction']);
            $table->index(['status']);
            $table->index(['scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
