<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('num_edge_nodes')->default(3);
            $table->integer('num_iot_devices')->default(10);
            $table->integer('num_tasks')->default(50);
            $table->enum('algorithm', ['PPO', 'DQN'])->default('PPO');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->json('config')->nullable();          // extra config blob
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};