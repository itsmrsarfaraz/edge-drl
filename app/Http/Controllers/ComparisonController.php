<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\TrainingRun;
use Illuminate\Support\Facades\Auth;

class ComparisonController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $ppoRuns = TrainingRun::whereHas('simulation', fn($q) => $q->where('user_id', $userId))
            ->where('algorithm', 'PPO')
            ->where('status', 'completed')
            ->with(['simulation', 'results'])
            ->latest()
            ->get();

        $dqnRuns = TrainingRun::whereHas('simulation', fn($q) => $q->where('user_id', $userId))
            ->where('algorithm', 'DQN')
            ->where('status', 'completed')
            ->with(['simulation', 'results'])
            ->latest()
            ->get();

        $bestPpo = $ppoRuns->sortByDesc('mean_reward')->first();
        $bestDqn = $dqnRuns->sortByDesc('mean_reward')->first();

        return view('comparison.index', compact(
            'ppoRuns', 'dqnRuns', 'bestPpo', 'bestDqn'
        ));
    }

    public function chartData()
    {
        $userId = Auth::id();

        $runs = TrainingRun::whereHas('simulation', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->with(['simulation', 'results'])
            ->orderBy('algorithm')
            ->orderBy('id')
            ->get();

        $labels       = [];
        $meanRewards  = [];
        $finalRewards = [];
        $latencies    = [];
        $colors       = [];

        foreach ($runs as $run) {
            $result       = $run->results->first();
            $labels[]     = $run->algorithm . ' #' . $run->id . ' (' . $run->simulation->name . ')';
            $meanRewards[]  = round($run->mean_reward  ?? 0, 4);
            $finalRewards[] = round($run->final_reward ?? 0, 4);
            $latencies[]    = $result ? round($result->avg_latency ?? 0, 2) : 0;
            $colors[]       = $run->algorithm === 'PPO' ? '#8b5cf6' : '#10b981';
        }

        // Reward history of best PPO vs best DQN
        $bestPpo    = $runs->where('algorithm', 'PPO')->sortByDesc('mean_reward')->first();
        $bestDqn    = $runs->where('algorithm', 'DQN')->sortByDesc('mean_reward')->first();
        $ppoHistory = $bestPpo?->results->first()?->reward_history ?? [];
        $dqnHistory = $bestDqn?->results->first()?->reward_history ?? [];

        // Pad shorter history to same length
        $maxLen     = max(count($ppoHistory), count($dqnHistory), 1);
        $stepLabels = array_map(fn($i) => "Step " . ($i + 1), range(0, $maxLen - 1));

        return response()->json([
            'run_comparison' => compact('labels', 'meanRewards', 'finalRewards', 'latencies', 'colors'),
            'reward_history' => [
                'labels'     => $stepLabels,
                'ppo'        => $ppoHistory,
                'dqn'        => $dqnHistory,
                'ppo_label'  => $bestPpo ? 'PPO #' . $bestPpo->id : 'PPO',
                'dqn_label'  => $bestDqn ? 'DQN #' . $bestDqn->id : 'DQN',
            ],
        ]);
    }
}