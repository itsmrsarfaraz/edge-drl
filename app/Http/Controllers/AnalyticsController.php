<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Simulation;
use App\Models\TrainingRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    // ── Main analytics page for a simulation ──────────────────

    public function show(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $simulation->load(['trainingRuns', 'results']);

        $results      = $simulation->results()->with('trainingRun')->latest()->get();
        $latestResult = $results->first();
        $trainingRuns = $simulation->trainingRuns()->where('status', 'completed')->get();

        // Summary metrics for the stat cards
        $summary = $this->buildSummary($simulation, $latestResult);

        return view('analytics.show', compact(
            'simulation',
            'results',
            'latestResult',
            'trainingRuns',
            'summary'
        ));
    }

    // ── JSON endpoint for Chart.js ─────────────────────────────

    public function chartData(Simulation $simulation): JsonResponse
    {
        $this->authorizeSimulation($simulation);

        $results      = $simulation->results()->with('trainingRun')->latest()->get();
        $latestResult = $results->first();
        $trainingRuns = $simulation->trainingRuns()->where('status', 'completed')->orderBy('id')->get();

        return response()->json([
            'reward_trend'       => $this->rewardTrend($latestResult),
            'latency_trend'      => $this->latencyTrend($latestResult),
            'node_utilization'   => $this->nodeUtilization($latestResult),
            'priority_breakdown' => $this->priorityBreakdown($simulation),
            'algo_comparison'    => $this->algoComparison($trainingRuns),
            'run_summary'        => $this->runSummary($trainingRuns),
        ]);
    }

    // ── Chart data builders ────────────────────────────────────

    private function rewardTrend(?Result $result): array
    {
        if (! $result || empty($result->reward_history)) {
            return ['labels' => [], 'data' => [], 'smoothed' => []];
        }

        $rewards = $result->reward_history;
        $labels  = array_map(fn($i) => "Step " . ($i + 1), array_keys($rewards));

        // Moving average (window=5) for smoothed line
        $smoothed = [];
        $window   = 5;
        foreach ($rewards as $i => $r) {
            $start      = max(0, $i - $window + 1);
            $slice      = array_slice($rewards, $start, $i - $start + 1);
            $smoothed[] = round(array_sum($slice) / count($slice), 4);
        }

        return [
            'labels'   => $labels,
            'data'     => array_map(fn($r) => round($r, 4), $rewards),
            'smoothed' => $smoothed,
        ];
    }

    private function latencyTrend(?Result $result): array
    {
        if (! $result || empty($result->latency_history)) {
            return ['labels' => [], 'data' => [], 'avg' => 0];
        }

        $latencies = $result->latency_history;
        $labels    = array_map(fn($i) => "Task " . ($i + 1), array_keys($latencies));
        $avg       = round(array_sum($latencies) / count($latencies), 2);

        return [
            'labels' => $labels,
            'data'   => array_map(fn($l) => round($l, 2), $latencies),
            'avg'    => $avg,
        ];
    }

    private function nodeUtilization(?Result $result): array
    {
        if (! $result || empty($result->node_utilization)) {
            return ['labels' => [], 'cpu' => [], 'memory' => [], 'queue' => []];
        }

        $nodes = $result->node_utilization;

        return [
            'labels' => array_column($nodes, 'name'),
            'cpu'    => array_map(fn($n) => round($n['cpu_util'] ?? 0, 1),    $nodes),
            'memory' => array_map(fn($n) => round($n['memory_util'] ?? 0, 1), $nodes),
            'queue'  => array_map(fn($n) => (int) ($n['queue_length'] ?? 0),  $nodes),
        ];
    }

    private function priorityBreakdown(Simulation $simulation): array
    {
        $priorities = ['low', 'medium', 'high', 'critical'];
        $counts     = [];

        foreach ($priorities as $p) {
            $counts[] = $simulation->tasks()->where('priority', $p)->count();
        }

        return [
            'labels' => ['Low', 'Medium', 'High', 'Critical'],
            'data'   => $counts,
        ];
    }

    private function algoComparison($trainingRuns): array
    {
        if ($trainingRuns->isEmpty()) {
            return ['labels' => [], 'mean_reward' => [], 'final_reward' => [], 'latency' => []];
        }

        $labels       = [];
        $meanRewards  = [];
        $finalRewards = [];
        $latencies    = [];

        foreach ($trainingRuns as $run) {
            $labels[]       = $run->algorithm . ' #' . $run->id;
            $meanRewards[]  = round($run->mean_reward  ?? 0, 4);
            $finalRewards[] = round($run->final_reward ?? 0, 4);

            $result      = $run->results()->latest()->first();
            $latencies[] = $result ? round($result->avg_latency ?? 0, 2) : 0;
        }

        return [
            'labels'       => $labels,
            'mean_reward'  => $meanRewards,
            'final_reward' => $finalRewards,
            'latency'      => $latencies,
        ];
    }

    private function runSummary($trainingRuns): array
    {
        return $trainingRuns->map(fn($run) => [
            'id'          => $run->id,
            'algorithm'   => $run->algorithm,
            'mean_reward' => round($run->mean_reward  ?? 0, 4),
            'final_reward'=> round($run->final_reward ?? 0, 4),
            'timesteps'   => $run->total_timesteps,
            'completed'   => $run->completed_at?->diffForHumans(),
        ])->values()->toArray();
    }

    private function buildSummary(Simulation $simulation, ?Result $result): array
    {
        return [
            'avg_latency'         => $result ? round($result->avg_latency, 1)            : null,
            'total_reward'        => $result ? round($result->total_reward, 3)            : null,
            'mean_reward'         => $result ? round($result->total_reward / max(count($result->reward_history ?? [1]), 1), 4) : null,
            'task_success_rate'   => $result ? round($result->task_success_rate, 1)       : null,
            'throughput'          => $result ? round($result->throughput, 3)              : null,
            'training_runs'       => $simulation->trainingRuns()->where('status', 'completed')->count(),
            'total_tasks'         => $simulation->tasks()->count(),
            'best_mean_reward'    => $simulation->trainingRuns()
                                        ->where('status', 'completed')
                                        ->max('mean_reward'),
        ];
    }

    private function authorizeSimulation(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}