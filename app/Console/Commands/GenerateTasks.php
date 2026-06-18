<?php

namespace App\Console\Commands;

use App\Models\Simulation;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GenerateTasks extends Command
{
    protected $signature   = 'simulation:generate-tasks {simulation_id} {--tasks=}';
    protected $description = 'Generate IoT tasks for a simulation using the Python generator';

    public function handle(): int
    {
        $simulationId = (int) $this->argument('simulation_id');
        $simulation   = Simulation::find($simulationId);

        if (! $simulation) {
            $this->error("Simulation #{$simulationId} not found.");
            return self::FAILURE;
        }

        $numTasks = (int) ($this->option('tasks') ?? $simulation->num_tasks);

        $this->info("Generating {$numTasks} tasks for simulation #{$simulationId} ({$simulation->name})...");

        $scriptPath = base_path('python/task_generator/generate_tasks.py');

        $env = array_merge($_ENV, [
            'DB_HOST'     => config('database.connections.mysql.host'),
            'DB_PORT'     => config('database.connections.mysql.port'),
            'DB_USERNAME' => config('database.connections.mysql.username'),
            'DB_PASSWORD' => config('database.connections.mysql.password'),
            'DB_DATABASE' => config('database.connections.mysql.database'),
        ]);

        $pythonBin = env('PYTHON_PATH', 'python3');

        $process = new Process(
            [$pythonBin, $scriptPath, "--simulation_id={$simulationId}", "--num_tasks={$numTasks}"],
            null,
            $env,
            null,
            120
        );

        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Python script failed:');
            $this->line($process->getErrorOutput());
            return self::FAILURE;
        }

        $output = json_decode($process->getOutput(), true);

        if (! $output || isset($output['error'])) {
            $this->error('Generator error: ' . ($output['error'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        $this->info("✔ Generated {$output['tasks_generated']} tasks.");
        $this->table(
            ['Priority', 'Count'],
            collect($output['priority_breakdown'])->map(fn($count, $priority) => [
                ucfirst($priority), $count
            ])->values()->toArray()
        );

        return self::SUCCESS;
    }
}