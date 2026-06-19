<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingRun extends Model
{
    protected $fillable = [
        'simulation_id', 'algorithm', 'total_timesteps', 'timesteps_completed',
        'status', 'final_reward', 'mean_reward', 'episodes', 'model_path', 'error_log',
        'started_at', 'completed_at',
        // New
        'training_curve', 'train_mean_reward',
        'eval_mean_reward', 'eval_std_reward', 'eval_min_reward', 'eval_max_reward',
        'eval_success_rate', 'eval_episodes', 'eval_all_rewards',
    ];

    protected $casts = [
        'final_reward'      => 'float',
        'mean_reward'       => 'float',
        'train_mean_reward' => 'float',
        'eval_mean_reward'  => 'float',
        'eval_std_reward'   => 'float',
        'eval_min_reward'   => 'float',
        'eval_max_reward'   => 'float',
        'eval_success_rate' => 'float',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'training_curve'    => 'array',
        'eval_all_rewards'  => 'array',
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