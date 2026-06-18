<?php

namespace App\Providers;

use App\Models\Simulation;
use App\Observers\SimulationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Simulation::observe(SimulationObserver::class);
    }
}