<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_run_id')->nullable()->constrained()->nullOnDelete();
            // Aggregated metrics stored here after simulation run
            $table->float('avg_latency')->nullable();           // ms
            $table->float('avg_cpu_utilization')->nullable();   // %
            $table->float('avg_memory_utilization')->nullable();// %
            $table->float('task_success_rate')->nullable();     // %
            $table->float('task_failure_rate')->nullable();     // %
            $table->float('avg_queue_length')->nullable();
            $table->float('total_reward')->nullable();
            $table->float('throughput')->nullable();            // tasks/second
            $table->json('reward_history')->nullable();         // array of reward per episode
            $table->json('cpu_history')->nullable();            // time-series CPU data
            $table->json('latency_history')->nullable();        // time-series latency data
            $table->json('queue_history')->nullable();          // time-series queue data
            $table->json('node_utilization')->nullable();       // per-node breakdown
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};