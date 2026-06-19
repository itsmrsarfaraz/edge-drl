<?php

use App\Http\Controllers\AiStatusController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\EdgeNodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimulationCloneController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingMetricsController;
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

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',                [ProfileController::class, 'show'])->name('show');
        Route::patch('/info',          [ProfileController::class, 'updateInfo'])->name('update-info');
        Route::patch('/password',      [ProfileController::class, 'updatePassword'])->name('update-password');
    });

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

    // Analytics - Dashboard
    Route::prefix('simulations/{simulation}/analytics')->name('simulations.analytics.')->group(function () {
        Route::get('/',          [AnalyticsController::class, 'show'])->name('show');
        Route::get('/chart-data',[AnalyticsController::class, 'chartData'])->name('chart-data');
    });

    // Reports
    Route::prefix('simulations/{simulation}/reports')->name('simulations.reports.')->group(function () {
        Route::get('/',         [ReportController::class, 'index'])->name('index');
        Route::get('/download', [ReportController::class, 'download'])->name('download');
    });

    Route::post('simulations/{simulation}/clone', [SimulationCloneController::class, 'clone'])
    ->name('simulations.clone');

    Route::prefix('comparison')->name('comparison.')->group(function () {
        Route::get('/',           [ComparisonController::class, 'index'])->name('index');
        Route::get('/chart-data', [ComparisonController::class, 'chartData'])->name('chart-data');
    });

    Route::post('simulations/{simulation}/infer', [TrainingController::class, 'infer'])
    ->name('simulations.infer');

    Route::prefix('simulations/{simulation}/training/{trainingRun}/metrics')
    ->name('simulations.training.metrics.')
    ->group(function () {
        Route::get('/',           [TrainingMetricsController::class, 'show'])->name('show');
        Route::get('/chart-data', [TrainingMetricsController::class, 'chartData'])->name('chart-data');
    });
});

require __DIR__.'/auth.php';