<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IotDevice extends Model
{
    protected $fillable = [
        'simulation_id',
        'name',
        'type',
        'battery_level',
        'is_active',
    ];

    protected $casts = [
        'battery_level' => 'float',
        'is_active'     => 'boolean',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(Simulation::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'camera'             => '📷',
            'motion_sensor'      => '🔄',
            'actuator'           => '⚙️',
            'gateway'            => '🌐',
            default              => '🌡️',
        };
    }
}