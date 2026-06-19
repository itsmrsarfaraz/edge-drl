<?php

namespace App\Http\Controllers;

use App\Models\EdgeNode;
use App\Models\Result;
use App\Models\Simulation;
use App\Models\TrainingRun;
use App\Services\PythonAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrainingController extends Controller
{
    public function __construct(private PythonAiService $ai) {}

    // ── Show training page ─────────────────────────────────────

    public function show(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);
        $simulation->load(['trainingRuns' => fn($q) => $q->latest()->take(10), 'latestResult']);

        $aiOnline = $this->ai->isOnline();

        return view('training.show', compact('simulation', 'aiOnline'));
    }

    // ── Start training ─────────────────────────────────────────

    public function start(Simulation $simulation): JsonResponse
    {
        $this->authorizeSimulation($simulation);

        // Check AI engine
        if (! $this->ai->isOnline()) {
            return response()->json([
                'error' => 'AI Engine is offline. Start FastAPI first: cd python && bash api/start.sh'
            ], 503);
        }

        // Check tasks exist
        $taskCount = $simulation->tasks()->count();
        if ($taskCount === 0) {
            return response()->json([
                'error' => 'No tasks generated. Generate tasks before running training.'
            ], 422);
        }

        // Check no training already running
        $running = $simulation->trainingRuns()
            ->where('status', 'running')
            ->exists();

        if ($running) {
            return response()->json([
                'error' => 'A training run is already in progress for this simulation.'
            ], 409);
        }

        // Create training run record
        $trainingRun = TrainingRun::create([
            'simulation_id'       => $simulation->id,
            'algorithm'           => $simulation->algorithm,
            'total_timesteps'     => $this->resolveTimesteps($taskCount),
            'timesteps_completed' => 0,
            'status'              => 'running',
            'started_at'          => now(),
        ]);

        // Reset edge node stats to idle before training starts
        $simulation->edgeNodes()->update([
            'cpu_used'               => 0.0,
            'memory_used'            => 0.0,
            'queue_length'           => 0,
            'utilization_percentage' => 0.0,
            'status'                 => 'idle',
        ]);

        // Build node capacity arrays
        $nodes          = $simulation->edgeNodes()->orderBy('id')->get();
        $cpuCapacities  = $nodes->pluck('cpu_capacity')->map(fn($v) => (float) $v)->toArray();
        $memCapacities  = $nodes->pluck('memory_capacity')->map(fn($v) => (float) $v)->toArray();

        // Call FastAPI
        $response = $this->ai->startTraining([
            'simulation_id'   => $simulation->id,
            'training_run_id' => $trainingRun->id,
            'algorithm'       => $simulation->algorithm,
            'num_nodes'       => $simulation->num_edge_nodes,
            'num_tasks'       => min($taskCount, 200),
            'total_timesteps' => $trainingRun->total_timesteps,
            'cpu_capacities'  => $cpuCapacities,
            'mem_capacities'  => $memCapacities,
        ]);

        if (isset($response['error'])) {
            $trainingRun->update(['status' => 'failed', 'error_log' => $response['error']]);
            return response()->json(['error' => $response['error']], 500);
        }

        // Mark simulation as running
        $simulation->update(['status' => 'running', 'started_at' => now()]);

        return response()->json([
            'training_run_id' => $trainingRun->id,
            'message'         => 'Training started',
            'algorithm'       => $simulation->algorithm,
            'total_timesteps' => $trainingRun->total_timesteps,
        ]);
    }

    // ── Poll training status ───────────────────────────────────

    public function status(Simulation $simulation, TrainingRun $trainingRun): JsonResponse
    {
        $this->authorizeSimulation($simulation);
        abort_if($trainingRun->simulation_id !== $simulation->id, 404);

        // If already finished in DB, return DB record
        if (in_array($trainingRun->status, ['completed', 'failed'])) {
            return response()->json([
                'status'          => $trainingRun->status,
                'progress'        => $trainingRun->status === 'completed' ? 100 : 0,
                'training_run_id' => $trainingRun->id,
                'result'          => $trainingRun->status === 'completed'
                    ? $this->formatRunResult($trainingRun)
                    : null,
                'error'           => $trainingRun->error_log,
            ]);
        }

        // Poll FastAPI
        $apiStatus = $this->ai->trainingStatus($trainingRun->id);

        if (($apiStatus['status'] ?? '') === 'completed') {
            $this->persistResult($simulation, $trainingRun, $apiStatus['result'] ?? []);
        } elseif (($apiStatus['status'] ?? '') === 'failed') {
            $trainingRun->update([
                'status'    => 'failed',
                'error_log' => $apiStatus['error'] ?? 'Unknown error',
            ]);
        } else {
            // Update progress
            $trainingRun->update([
                'timesteps_completed' => (int) (
                    ($apiStatus['progress'] ?? 0) / 100 * $trainingRun->total_timesteps
                ),
            ]);
        }

        return response()->json([
            'status'          => $apiStatus['status']   ?? 'running',
            'progress'        => $apiStatus['progress'] ?? 0,
            'training_run_id' => $trainingRun->id,
            'result'          => ($apiStatus['status'] ?? '') === 'completed'
                ? $this->formatRunResult($trainingRun->fresh())
                : null,
            'error'           => $apiStatus['error'] ?? null,
        ]);
    }

    // ── Persist result to DB ───────────────────────────────────

    private function persistResult(Simulation $simulation, TrainingRun $trainingRun, array $data): void
    {
        $eval = $data['eval_summary'] ?? [];

        // ── Update training run record ─────────────────────────────
        $trainingRun->update([
            'status'              => 'completed',
            'timesteps_completed' => $data['total_timesteps'] ?? $trainingRun->total_timesteps,
            'final_reward'        => $data['final_reward']    ?? null,
            'mean_reward'         => $data['mean_reward']     ?? null,
            'episodes'            => $data['episodes']        ?? 1,
            'model_path'          => $data['model_path']      ?? null,
            'completed_at'        => now(),
        ]);

        // ── Node utilization from Python's result (already written to DB) ──
        // Re-read fresh from DB so we get the values Python just wrote
        $simulation->load('edgeNodes');
        $nodeUtilSnapshot = $this->nodeUtilSnapshot($simulation);

        // ── Also update task statuses based on allocation log ─────
        $this->updateTaskStatuses($simulation, $eval);

        // ── Save result metrics ────────────────────────────────────
        Result::create([
            'simulation_id'           => $simulation->id,
            'training_run_id'         => $trainingRun->id,
            'avg_latency'             => $eval['mean_latency_ms']  ?? null,
            'avg_cpu_utilization'     => $this->avgNodeCpu($simulation),
            'avg_memory_utilization'  => $this->avgNodeMem($simulation),
            'task_success_rate'       => $this->calcSuccessRate($simulation),
            'task_failure_rate'       => $this->calcFailureRate($simulation),
            'avg_queue_length'        => $simulation->edgeNodes->avg('queue_length') ?? 0,
            'total_reward'            => $eval['total_reward']     ?? null,
            'throughput'              => $this->calcThroughput($simulation, $eval),
            'reward_history'          => $eval['reward_history']   ?? [],
            'latency_history'         => $eval['latency_history']  ?? [],
            'cpu_history'             => $this->buildCpuHistory($simulation),
            'queue_history'           => [],
            'node_utilization'        => $nodeUtilSnapshot,
        ]);

        $simulation->update(['status' => 'completed', 'completed_at' => now()]);

        Log::info("Training run #{$trainingRun->id} completed for simulation #{$simulation->id}");
    }

    private function updateTaskStatuses(Simulation $simulation, array $eval): void
    {
        $allocationLog = $eval['allocation_log'] ?? [];
        if (empty($allocationLog)) return;

        // Mark allocated tasks as completed, delayed ones as delayed
        $pendingTasks = $simulation->tasks()
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        foreach ($allocationLog as $i => $entry) {
            $task = $pendingTasks[$i] ?? null;
            if (! $task) continue;

            $status  = $entry['action'] < $simulation->num_edge_nodes ? 'completed' : 'delayed';
            $latency = $entry['latency'] ?? null;

            // Find which edge node was assigned
            $nodeId = null;
            if ($status === 'completed') {
                $nodeIndex = $entry['action'];
                $node = $simulation->edgeNodes->sortBy('name')->values()->get($nodeIndex);
                $nodeId = $node?->id;
            }

            $task->update([
                'status'       => $status,
                'latency'      => $latency,
                'edge_node_id' => $nodeId,
                'assigned_at'  => now(),
                'completed_at' => $status === 'completed' ? now() : null,
            ]);
        }
    }

    private function buildCpuHistory(Simulation $simulation): array
    {
        return $simulation->edgeNodes
            ->map(fn($n) => [
                'node'    => $n->name,
                'cpu_pct' => $n->cpu_usage_percent,
            ])
            ->toArray();
    }

    // ── Helpers ────────────────────────────────────────────────

    private function resolveTimesteps(int $taskCount): int
    {
        return match(true) {
            $taskCount >= 200 => 20000,
            $taskCount >= 100 => 15000,
            default           => 10000,
        };
    }

    private function avgNodeCpu(Simulation $simulation): float
    {
        $nodes = $simulation->edgeNodes;
        if ($nodes->isEmpty()) return 0.0;
        return round(
            $nodes->avg(fn($n) => $n->cpu_usage_percent) ?? 0.0,
            2
        );
    }

    private function avgNodeMem(Simulation $simulation): float
    {
        $nodes = $simulation->edgeNodes;
        if ($nodes->isEmpty()) return 0.0;
        return round(
            $nodes->avg(fn($n) => $n->memory_usage_percent) ?? 0.0,
            2
        );
    }

    private function calcSuccessRate(Simulation $simulation): float
    {
        $total     = $simulation->tasks()->count();
        $completed = $simulation->tasks()->where('status', 'completed')->count();
        return $total > 0 ? round($completed / $total * 100, 2) : 0.0;
    }

    private function calcFailureRate(Simulation $simulation): float
    {
        $total  = $simulation->tasks()->count();
        $failed = $simulation->tasks()->whereIn('status', ['failed', 'delayed'])->count();
        return $total > 0 ? round($failed / $total * 100, 2) : 0.0;
    }

    private function calcThroughput(Simulation $simulation, array $eval): float
    {
        $steps = $eval['steps'] ?? 1;
        return $steps > 0 ? round($simulation->tasks()->count() / max($steps, 1), 3) : 0.0;
    }

    private function nodeUtilSnapshot(Simulation $simulation): array
    {
        return $simulation->edgeNodes()
            ->get()
            ->map(fn($n) => [
                'name'         => $n->name,
                'cpu_util'     => $n->cpu_usage_percent,
                'memory_util'  => $n->memory_usage_percent,
                'queue_length' => $n->queue_length,
                'status'       => $n->status,
            ])
            ->toArray();
    }

    private function formatRunResult(TrainingRun $run): array
    {
        return [
            'algorithm'    => $run->algorithm,
            'final_reward' => $run->final_reward,
            'mean_reward'  => $run->mean_reward,
            'model_path'   => $run->model_path,
            'completed_at' => $run->completed_at?->toISOString(),
        ];
    }

    private function authorizeSimulation(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}