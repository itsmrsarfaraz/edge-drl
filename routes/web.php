<?php

use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::resource('simulations', SimulationController::class)
        ->except(['edit', 'update']);
});

require __DIR__.'/auth.php';