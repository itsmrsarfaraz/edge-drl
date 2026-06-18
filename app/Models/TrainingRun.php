<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingRun extends Model
{
    protected $fillable = [
        'simulation_id',
        'algorithm',
        'total_timesteps',
        'timesteps_completed',
        'status',
        'final_reward',
        'mean_reward',
        'episodes',
        'model_path',
        'error_log',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'final_reward' => 'float',
        'mean_reward'  => 'float',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(Simulation::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function getProgressPercentAttribute(): float
    {
        return $this->total_timesteps > 0
            ? round(($this->timesteps_completed / $this->total_timesteps) * 100, 1)
            : 0.0;
    }
}