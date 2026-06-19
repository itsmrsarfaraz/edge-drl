<?php

use App\Http\Controllers\AiStatusController;
use App\Http\Controllers\EdgeNodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// AI Engine — no auth needed (health ping from topbar JS)
Route::prefix('ai')->name('ai.')->group(function () {
    Route::get('/health',   [AiStatusController::class, 'health'])->name('health');
    Route::get('/env-info', [AiStatusController::class, 'envInfo'])->name('env-info');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Simulations
    Route::resource('simulations', SimulationController::class)
        ->except(['edit', 'update']);

    // Edge Nodes — nested under simulations
    Route::prefix('simulations/{simulation}/nodes')->name('simulations.nodes.')->group(function () {
        Route::get('/',                  [EdgeNodeController::class, 'index'])->name('index');
        Route::get('/stats',             [EdgeNodeController::class, 'stats'])->name('stats');
        Route::get('/{edgeNode}',        [EdgeNodeController::class, 'show'])->name('show');
        Route::post('/{edgeNode}/reset', [EdgeNodeController::class, 'reset'])->name('reset');
    });

    // Tasks — nested under simulations
    Route::prefix('simulations/{simulation}/tasks')->name('simulations.tasks.')->group(function () {
        Route::get('/',          [TaskController::class, 'index'])->name('index');
        Route::post('/generate', [TaskController::class, 'generate'])->name('generate');
    });

    // Training — nested under simulations
    Route::prefix('simulations/{simulation}/training')->name('simulations.training.')->group(function () {
        Route::get('/',                              [TrainingController::class, 'show'])->name('show');
        Route::post('/start',                        [TrainingController::class, 'start'])->name('start');
        Route::get('/{trainingRun}/status',          [TrainingController::class, 'status'])->name('status');
    });
});

require __DIR__.'/auth.php';