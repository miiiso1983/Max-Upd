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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('code')->unique();
            $table->unsignedBigInteger('department_id');
            $table->enum('level', ['entry', 'junior', 'senior', 'lead', 'manager', 'director', 'executive'])->default('entry');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('requirements')->nullable();
            $table->text('requirements_ar')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('responsibilities_ar')->nullable();
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->string('currency', 3)->default('IQD');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'status']);
            $table->index(['level', 'status']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
