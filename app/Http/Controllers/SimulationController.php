<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    public function index()
    {
        $simulations = Auth::user()
            ->simulations()
            ->withCount(['tasks', 'edgeNodes', 'trainingRuns'])
            ->latest()
            ->paginate(9);

        return view('simulations.index', compact('simulations'));
    }

    public function create()
    {
        return view('simulations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string|max:500',
            'num_edge_nodes'  => 'required|integer|min:1|max:10',
            'num_iot_devices' => 'required|integer|min:1|max:50',
            'num_tasks'       => 'required|integer|min:10|max:500',
            'algorithm'       => 'required|in:PPO,DQN',
        ]);

        $simulation = Auth::user()->simulations()->create($validated);

        return redirect()
            ->route('simulations.show', $simulation)
            ->with('success', 'Simulation "' . $simulation->name . '" created successfully.');
    }

    public function show(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $simulation->load(['edgeNodes', 'iotDevices', 'trainingRuns', 'latestResult']);

        $stats = [
            'total_tasks'     => $simulation->tasks()->count(),
            'completed_tasks' => $simulation->tasks()->where('status', 'completed')->count(),
            'failed_tasks'    => $simulation->tasks()->where('status', 'failed')->count(),
            'training_runs'   => $simulation->trainingRuns()->count(),
        ];

        return view('simulations.show', compact('simulation', 'stats'));
    }

    public function destroy(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);
        $name = $simulation->name;
        $simulation->delete();

        return redirect()
            ->route('simulations.index')
            ->with('success', 'Simulation "' . $name . '" deleted.');
    }

    private function authorizeSimulation(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}