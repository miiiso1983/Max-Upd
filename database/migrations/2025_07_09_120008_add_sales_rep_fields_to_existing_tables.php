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
        // Add sales rep fields to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('postal_code');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('visit_frequency_days')->default(30)->after('longitude');
            $table->enum('priority_level', ['low', 'medium', 'high', 'critical'])->default('medium')->after('visit_frequency_days');
            $table->datetime('last_visit_date')->nullable()->after('priority_level');
            $table->datetime('next_visit_date')->nullable()->after('last_visit_date');
            $table->json('visit_preferences')->nullable()->after('next_visit_date'); // {preferred_days, preferred_times, contact_person}
            $table->boolean('gps_verified')->default(false)->after('visit_preferences');
            $table->text('location_notes')->nullable()->after('gps_verified');
            
            // Indexes
            $table->index(['latitude', 'longitude']);
            $table->index(['priority_level', 'is_active']);
            $table->index(['last_visit_date', 'next_visit_date']);
        });

        // Add sales rep tracking to sales_orders table if it exists
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_orders', 'sales_rep_id')) {
                    $table->unsignedBigInteger('sales_rep_id')->nullable()->after('customer_id');
                    $table->index('sales_rep_id');
                }
                
                // Add visit tracking
                $table->unsignedBigInteger('visit_id')->nullable()->after('sales_rep_id');
                $table->boolean('created_during_visit')->default(false)->after('visit_id');
                $table->decimal('rep_commission_rate', 5, 2)->nullable()->after('created_during_visit');
                $table->decimal('rep_commission_amount', 10, 2)->default(0)->after('rep_commission_rate');
                
                // Location where order was created
                $table->decimal('order_latitude', 10, 8)->nullable()->after('rep_commission_amount');
                $table->decimal('order_longitude', 11, 8)->nullable()->after('order_latitude');
                $table->string('order_location_address')->nullable()->after('order_longitude');
                
                // Indexes
                $table->index(['visit_id', 'created_during_visit']);
                $table->index(['order_latitude', 'order_longitude']);
                
                // Foreign key for visit_id
                $table->foreign('visit_id')->references('id')->on('customer_visits')->onDelete('set null');
            });
        }

        // Add rep tracking to invoices table if it exists
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_rep_id')->nullable()->after('customer_id');
                $table->decimal('rep_commission_rate', 5, 2)->nullable()->after('sales_rep_id');
                $table->decimal('rep_commission_amount', 10, 2)->default(0)->after('rep_commission_rate');
                $table->boolean('commission_paid')->default(false)->after('rep_commission_amount');
                $table->date('commission_paid_date')->nullable()->after('commission_paid');
                
                // Indexes
                $table->index(['sales_rep_id', 'commission_paid']);
            });
        }

        // Add rep tracking to payments table if it exists
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('collected_by_rep_id')->nullable()->after('payment_method');
                $table->unsignedBigInteger('visit_id')->nullable()->after('collected_by_rep_id');
                $table->boolean('collected_during_visit')->default(false)->after('visit_id');
                $table->decimal('collection_latitude', 10, 8)->nullable()->after('collected_during_visit');
                $table->decimal('collection_longitude', 11, 8)->nullable()->after('collection_latitude');
                $table->string('collection_location_address')->nullable()->after('collection_longitude');
                $table->text('collection_notes')->nullable()->after('collection_location_address');
                
                // Indexes
                $table->index(['collected_by_rep_id', 'payment_date']);
                $table->index(['visit_id', 'collected_during_visit']);
                $table->index(['collection_latitude', 'collection_longitude']);
                
                // Foreign keys
                $table->foreign('collected_by_rep_id')->references('id')->on('sales_representatives')->onDelete('set null');
                $table->foreign('visit_id')->references('id')->on('customer_visits')->onDelete('set null');
            });
        }

        // Add role for sales representatives
        if (Schema::hasTable('roles')) {
            // This will be handled in the seeder
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'latitude', 'longitude', 'visit_frequency_days', 'priority_level',
                'last_visit_date', 'next_visit_date', 'visit_preferences',
                'gps_verified', 'location_notes'
            ]);
        });

        // Remove fields from sales_orders table if it exists
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropForeign(['visit_id']);
                $table->dropColumn([
                    'visit_id', 'created_during_visit', 'rep_commission_rate',
                    'rep_commission_amount', 'order_latitude', 'order_longitude',
                    'order_location_address'
                ]);
            });
        }

        // Remove fields from invoices table if it exists
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn([
                    'sales_rep_id', 'rep_commission_rate', 'rep_commission_amount',
                    'commission_paid', 'commission_paid_date'
                ]);
            });
        }

        // Remove fields from payments table if it exists
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['collected_by_rep_id']);
                $table->dropForeign(['visit_id']);
                $table->dropColumn([
                    'collected_by_rep_id', 'visit_id', 'collected_during_visit',
                    'collection_latitude', 'collection_longitude',
                    'collection_location_address', 'collection_notes'
                ]);
            });
        }
    }
};
