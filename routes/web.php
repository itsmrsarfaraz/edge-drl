<?php

use App\Http\Controllers\EdgeNodeController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // Simulations
    Route::resource('simulations', SimulationController::class)->except(['edit', 'update']);

    // Edge Nodes — nested under simulations
    Route::prefix('simulations/{simulation}/nodes')->name('simulations.nodes.')->group(function () {
        Route::get('/',              [EdgeNodeController::class, 'index'])->name('index');
        Route::get('/stats',         [EdgeNodeController::class, 'stats'])->name('stats');
        Route::get('/{edgeNode}',    [EdgeNodeController::class, 'show'])->name('show');
        Route::post('/{edgeNode}/reset', [EdgeNodeController::class, 'reset'])->name('reset');
    });
});

require __DIR__.'/auth.php';