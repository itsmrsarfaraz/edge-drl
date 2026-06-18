<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Simulation extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'num_edge_nodes',
        'num_iot_devices',
        'num_tasks',
        'algorithm',
        'status',
        'config',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'config'       => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function edgeNodes(): HasMany
    {
        return $this->hasMany(EdgeNode::class);
    }

    public function iotDevices(): HasMany
    {
        return $this->hasMany(IotDevice::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function trainingRuns(): HasMany
    {
        return $this->hasMany(TrainingRun::class);
    }

    public function latestResult(): HasOne
    {
        return $this->hasOne(Result::class)->latestOfMany();
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'emerald',
            'running'   => 'primary',
            'failed'    => 'red',
            default     => 'slate',
        };
    }
}