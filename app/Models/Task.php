<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'simulation_id',
        'iot_device_id',
        'edge_node_id',
        'task_id_label',
        'priority',
        'cpu_requirement',
        'memory_requirement',
        'task_size',
        'deadline',
        'status',
        'latency',
        'execution_time',
        'generated_at',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'cpu_requirement'    => 'float',
        'memory_requirement' => 'float',
        'task_size'          => 'float',
        'deadline'           => 'float',
        'latency'            => 'float',
        'execution_time'     => 'float',
        'generated_at'       => 'datetime',
        'assigned_at'        => 'datetime',
        'completed_at'       => 'datetime',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(Simulation::class);
    }

    public function iotDevice(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class);
    }

    public function edgeNode(): BelongsTo
    {
        return $this->belongsTo(EdgeNode::class);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'amber',
            default    => 'slate',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed'  => 'emerald',
            'processing' => 'primary',
            'failed'     => 'red',
            'delayed'    => 'amber',
            'queued'     => 'violet',
            default      => 'slate',
        };
    }
}