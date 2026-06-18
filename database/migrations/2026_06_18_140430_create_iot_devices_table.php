<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_id')->constrained()->cascadeOnDelete();
            $table->string('name');                    // e.g. "Sensor-A1"
            $table->enum('type', [
                'temperature_sensor',
                'motion_sensor',
                'camera',
                'actuator',
                'gateway'
            ])->default('temperature_sensor');
            $table->float('battery_level')->default(100.0);  // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_devices');
    }
};