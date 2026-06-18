<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edge_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // e.g. "Edge Node 1"
            $table->float('cpu_capacity')->default(100.0);   // in percentage units
            $table->float('memory_capacity')->default(8192); // in MB
            $table->float('cpu_used')->default(0.0);
            $table->float('memory_used')->default(0.0);
            $table->integer('queue_length')->default(0);
            $table->float('utilization_percentage')->default(0.0);
            $table->enum('status', ['idle', 'busy', 'overloaded', 'offline'])->default('idle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edge_nodes');
    }
};