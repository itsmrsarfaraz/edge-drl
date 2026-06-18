<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('iot_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('edge_node_id')->nullable()->constrained()->nullOnDelete(); // assigned node
            $table->string('task_id_label')->nullable();   // human-readable e.g. "TASK-001"
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->float('cpu_requirement')->default(10.0);    // percentage of node CPU
            $table->float('memory_requirement')->default(256);  // MB
            $table->float('task_size')->default(1.0);           // in MB (data payload)
            $table->float('deadline')->default(5.0);            // seconds
            $table->enum('status', [
                'pending',
                'queued',
                'processing',
                'completed',
                'failed',
                'delayed'
            ])->default('pending');
            $table->float('latency')->nullable();               // ms, filled after execution
            $table->float('execution_time')->nullable();        // seconds
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};