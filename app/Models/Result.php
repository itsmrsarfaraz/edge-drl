<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'simulation_id',
        'training_run_id',
        'avg_latency',
        'avg_cpu_utilization',
        'avg_memory_utilization',
        'task_success_rate',
        'task_failure_rate',
        'avg_queue_length',
        'total_reward',
        'throughput',
        'reward_history',
        'cpu_history',
        'latency_history',
        'queue_history',
        'node_utilization',
    ];

    protected $casts = [
        'avg_latency'             => 'float',
        'avg_cpu_utilization'     => 'float',
        'avg_memory_utilization'  => 'float',
        'task_success_rate'       => 'float',
        'task_failure_rate'       => 'float',
        'avg_queue_length'        => 'float',
        'total_reward'            => 'float',
        'throughput'              => 'float',
        'reward_history'          => 'array',
        'cpu_history'             => 'array',
        'latency_history'         => 'array',
        'queue_history'           => 'array',
        'node_utilization'        => 'array',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(Simulation::class);
    }

    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }
}