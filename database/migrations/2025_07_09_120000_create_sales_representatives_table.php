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
        Schema::create('sales_representatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            $table->date('hire_date');
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('national_id')->nullable();
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
            $table->decimal('monthly_target', 12, 2)->default(0);
            $table->decimal('quarterly_target', 12, 2)->default(0);
            $table->decimal('annual_target', 12, 2)->default(0);
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'freelance'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->json('emergency_contact')->nullable(); // {name, phone, relationship}
            $table->json('bank_details')->nullable(); // {bank_name, account_number, iban}
            $table->json('documents')->nullable(); // {cv, id_copy, contracts, etc}
            $table->text('notes')->nullable();
            $table->boolean('can_create_orders')->default(true);
            $table->boolean('can_collect_payments')->default(true);
            $table->boolean('can_view_all_customers')->default(false);
            $table->decimal('max_discount_percentage', 5, 2)->default(0);
            $table->decimal('max_order_amount', 12, 2)->default(0);
            $table->json('working_hours')->nullable(); // {start_time, end_time, days}
            $table->json('gps_settings')->nullable(); // {tracking_enabled, accuracy_required}
            $table->timestamp('last_location_update')->nullable();
            $table->decimal('last_latitude', 10, 8)->nullable();
            $table->decimal('last_longitude', 11, 8)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'employment_type']);
            $table->index(['supervisor_id', 'status']);
            $table->index(['governorate', 'city']);
            $table->index(['hire_date', 'status']);
            $table->index(['last_latitude', 'last_longitude']);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('sales_representatives')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_representatives');
    }
};
