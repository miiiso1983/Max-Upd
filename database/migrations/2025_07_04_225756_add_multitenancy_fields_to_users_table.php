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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->boolean('is_super_admin')->default(false)->after('is_active');
            $table->unsignedBigInteger('tenant_id')->nullable()->after('is_super_admin');
            $table->timestamp('last_login_at')->nullable()->after('tenant_id');
            $table->json('settings')->nullable()->after('last_login_at');
            $table->string('employee_id')->nullable()->after('settings');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('position')->nullable()->after('department');

            $table->index(['tenant_id', 'is_active']);
            $table->index('is_super_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'is_active',
                'is_super_admin',
                'tenant_id',
                'last_login_at',
                'settings',
                'employee_id',
                'department',
                'position'
            ]);
        });
    }
};
