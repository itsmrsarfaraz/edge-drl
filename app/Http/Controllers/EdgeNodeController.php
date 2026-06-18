<?php

namespace App\Http\Controllers;

use App\Models\EdgeNode;
use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EdgeNodeController extends Controller
{
    // List all nodes for a simulation
    public function index(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $nodes = $simulation->edgeNodes()->orderBy('name')->get();

        return view('edge-nodes.index', compact('simulation', 'nodes'));
    }

    // Show single node detail
    public function show(Simulation $simulation, EdgeNode $edgeNode)
    {
        $this->authorizeSimulation($simulation);
        abort_if($edgeNode->simulation_id !== $simulation->id, 404);

        $tasks = $edgeNode->tasks()
            ->latest()
            ->take(20)
            ->get();

        return view('edge-nodes.show', compact('simulation', 'edgeNode', 'tasks'));
    }

    // Reset a node's load back to idle
    public function reset(Simulation $simulation, EdgeNode $edgeNode)
    {
        $this->authorizeSimulation($simulation);
        abort_if($edgeNode->simulation_id !== $simulation->id, 404);

        $edgeNode->update([
            'cpu_used'               => 0.0,
            'memory_used'            => 0.0,
            'queue_length'           => 0,
            'utilization_percentage' => 0.0,
            'status'                 => 'idle',
        ]);

        return back()->with('success', $edgeNode->name . ' has been reset to idle.');
    }

    // API-style endpoint — returns node stats as JSON (used by Chart.js later)
    public function stats(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $nodes = $simulation->edgeNodes()
            ->orderBy('name')
            ->get()
            ->map(fn($node) => [
                'id'                     => $node->id,
                'name'                   => $node->name,
                'status'                 => $node->status,
                'cpu_usage_percent'      => $node->cpu_usage_percent,
                'memory_usage_percent'   => $node->memory_usage_percent,
                'queue_length'           => $node->queue_length,
                'utilization_percentage' => $node->utilization_percentage,
            ]);

        return response()->json($nodes);
    }

    private function authorizeSimulation(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}