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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->string('company_name');
            $table->string('company_name_ar')->nullable();
            $table->string('contact_person');
            $table->string('contact_person_ar')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->text('address_ar')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('city_ar', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_ar', 100)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('industry_ar', 100)->nullable();
            $table->string('source');
            $table->string('source_ar', 100)->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'converted', 'lost', 'unqualified'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->integer('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('last_contact_date')->nullable();
            $table->timestamp('next_follow_up_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
            $table->index(['source']);
            $table->index(['assigned_to']);
            $table->index(['next_follow_up_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
