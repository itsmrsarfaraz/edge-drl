<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;

class TaskController extends Controller
{
    // List all tasks for a simulation
    public function index(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $tasks = $simulation->tasks()
            ->with('iotDevice', 'edgeNode')
            ->latest('generated_at')
            ->paginate(25);

        $stats = [
            'total'      => $simulation->tasks()->count(),
            'pending'    => $simulation->tasks()->where('status', 'pending')->count(),
            'completed'  => $simulation->tasks()->where('status', 'completed')->count(),
            'failed'     => $simulation->tasks()->where('status', 'failed')->count(),
            'critical'   => $simulation->tasks()->where('priority', 'critical')->count(),
            'high'       => $simulation->tasks()->where('priority', 'high')->count(),
        ];

        return view('tasks.index', compact('simulation', 'tasks', 'stats'));
    }

    // Trigger Python task generation
    public function generate(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $scriptPath = base_path('python/task_generator/generate_tasks.py');

        $env = array_merge($_ENV, [
            'DB_HOST'     => config('database.connections.mysql.host'),
            'DB_PORT'     => config('database.connections.mysql.port'),
            'DB_USERNAME' => config('database.connections.mysql.username'),
            'DB_PASSWORD' => config('database.connections.mysql.password'),
            'DB_DATABASE' => config('database.connections.mysql.database'),
        ]);

        $process = new Process(
            [
                'python3',
                $scriptPath,
                "--simulation_id={$simulation->id}",
                "--num_tasks={$simulation->num_tasks}",
            ],
            null,
            $env,
            null,
            120
        );

        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', 'Task generation failed: ' . $process->getErrorOutput());
        }

        $output = json_decode($process->getOutput(), true);

        if (! $output || isset($output['error'])) {
            return back()->with('error', $output['error'] ?? 'Unknown generation error.');
        }

        return redirect()
            ->route('simulations.tasks.index', $simulation)
            ->with('success', "Generated {$output['tasks_generated']} tasks successfully.");
    }

    private function authorizeSimulation(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}