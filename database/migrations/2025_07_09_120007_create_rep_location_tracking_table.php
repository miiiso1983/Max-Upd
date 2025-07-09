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
        Schema::create('rep_location_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_rep_id');
            $table->datetime('tracked_at');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('accuracy_meters')->nullable();
            $table->decimal('altitude_meters', 8, 2)->nullable();
            $table->decimal('speed_kmh', 6, 2)->nullable();
            $table->decimal('bearing_degrees', 6, 3)->nullable(); // Direction of movement
            
            // Location context
            $table->enum('location_type', ['customer_site', 'office', 'home', 'transit', 'other'])->nullable();
            $table->unsignedBigInteger('customer_id')->nullable(); // If at customer location
            $table->unsignedBigInteger('visit_id')->nullable(); // If during a visit
            $table->string('address')->nullable(); // Reverse geocoded address
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            
            // Activity context
            $table->enum('activity_type', ['working', 'break', 'lunch', 'travel', 'meeting', 'training', 'off_duty'])->nullable();
            $table->boolean('is_working_hours')->default(true);
            $table->boolean('is_authorized_location')->default(true);
            $table->decimal('distance_from_last_km', 8, 3)->nullable();
            $table->integer('time_since_last_minutes')->nullable();
            
            // Device and technical info
            $table->string('device_id')->nullable();
            $table->string('app_version')->nullable();
            $table->enum('location_source', ['gps', 'network', 'passive', 'fused'])->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->json('raw_location_data')->nullable(); // Original location data from device
            
            // Battery and performance
            $table->integer('battery_level')->nullable(); // 0-100
            $table->boolean('is_charging')->nullable();
            $table->enum('network_type', ['wifi', '4g', '3g', '2g', 'offline'])->nullable();
            $table->integer('signal_strength')->nullable(); // -120 to 0 dBm
            
            // Privacy and compliance
            $table->boolean('location_shared_with_customer')->default(false);
            $table->boolean('is_emergency_location')->default(false);
            $table->text('privacy_notes')->nullable();
            
            // Geofencing
            $table->json('geofences_entered')->nullable(); // Array of geofence IDs entered
            $table->json('geofences_exited')->nullable(); // Array of geofence IDs exited
            $table->boolean('is_in_assigned_territory')->default(true);
            $table->decimal('distance_from_territory_center_km', 8, 3)->nullable();
            
            // System fields
            $table->boolean('processed')->default(false);
            $table->datetime('processed_at')->nullable();
            $table->boolean('synced')->default(false);
            $table->datetime('synced_at')->nullable();
            $table->json('processing_notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['sales_rep_id', 'tracked_at']);
            $table->index(['tracked_at', 'is_working_hours']);
            $table->index(['latitude', 'longitude']);
            $table->index(['customer_id', 'tracked_at']);
            $table->index(['visit_id', 'tracked_at']);
            $table->index(['activity_type', 'tracked_at']);
            $table->index(['processed', 'tracked_at']);
            $table->index(['synced', 'created_at']);
            $table->index(['is_emergency_location', 'tracked_at']);

            // Foreign keys
            $table->foreign('sales_rep_id')->references('id')->on('sales_representatives')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('visit_id')->references('id')->on('customer_visits')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rep_location_tracking');
    }
};
