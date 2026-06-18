<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EdgeNode extends Model
{
    protected $fillable = [
        'simulation_id',
        'name',
        'cpu_capacity',
        'memory_capacity',
        'cpu_used',
        'memory_used',
        'queue_length',
        'utilization_percentage',
        'status',
    ];

    protected $casts = [
        'cpu_capacity'           => 'float',
        'memory_capacity'        => 'float',
        'cpu_used'               => 'float',
        'memory_used'            => 'float',
        'utilization_percentage' => 'float',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(Simulation::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function getCpuUsagePercentAttribute(): float
    {
        return $this->cpu_capacity > 0
            ? round(($this->cpu_used / $this->cpu_capacity) * 100, 1)
            : 0.0;
    }

    public function getMemoryUsagePercentAttribute(): float
    {
        return $this->memory_capacity > 0
            ? round(($this->memory_used / $this->memory_capacity) * 100, 1)
            : 0.0;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'busy'       => 'amber',
            'overloaded' => 'red',
            'offline'    => 'slate',
            default      => 'emerald',
        };
    }
}