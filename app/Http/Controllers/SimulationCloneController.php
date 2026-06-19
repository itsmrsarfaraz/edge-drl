<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Support\Facades\Auth;

class SimulationCloneController extends Controller
{
    public function clone(Simulation $simulation)
    {
        abort_if($simulation->user_id !== Auth::id(), 403);

        // Flip the algorithm
        $newAlgorithm = $simulation->algorithm === 'PPO' ? 'DQN' : 'PPO';

        $clone = Auth::user()->simulations()->create([
            'name'            => $simulation->name . ' [' . $newAlgorithm . ']',
            'description'     => 'Cloned from "' . $simulation->name . '" to compare ' . $newAlgorithm,
            'num_edge_nodes'  => $simulation->num_edge_nodes,
            'num_iot_devices' => $simulation->num_iot_devices,
            'num_tasks'       => $simulation->num_tasks,
            'algorithm'       => $newAlgorithm,
            'status'          => 'pending',
        ]);

        // Observer auto-provisions edge nodes + IoT devices

        return redirect()
            ->route('simulations.tasks.index', $clone)
            ->with('success', "Cloned as \"{$clone->name}\". Generate tasks then run training.");
    }
}