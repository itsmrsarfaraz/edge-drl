<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $stats = [
            'simulations'   => $user->simulations()->count(),
            'training_runs' => \App\Models\TrainingRun::whereHas(
                'simulation', fn($q) => $q->where('user_id', $user->id)
            )->count(),
            'tasks'         => \App\Models\Task::whereHas(
                'simulation', fn($q) => $q->where('user_id', $user->id)
            )->count(),
            'completed'     => $user->simulations()->where('status', 'completed')->count(),
        ];

        $recentActivity = $user->simulations()
            ->with(['trainingRuns' => fn($q) => $q->latest()->take(1)])
            ->latest()
            ->take(5)
            ->get();

        return view('profile.show', compact('user', 'stats', 'recentActivity'));
    }

    public function updateInfo(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required|current_password',
            'password'              => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => 'required',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}