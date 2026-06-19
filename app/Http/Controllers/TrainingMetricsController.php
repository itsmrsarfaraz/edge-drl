<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use App\Models\TrainingRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TrainingMetricsController extends Controller
{
    public function show(Simulation $simulation, TrainingRun $trainingRun)
    {
        $this->authorize($simulation);
        abort_if($trainingRun->simulation_id !== $simulation->id, 404);
        abort_if($trainingRun->status !== 'completed', 404, 'Training not completed yet.');

        return view('training.metrics', compact('simulation', 'trainingRun'));
    }

    public function chartData(Simulation $simulation, TrainingRun $trainingRun): JsonResponse
    {
        $this->authorize($simulation);
        abort_if($trainingRun->simulation_id !== $simulation->id, 404);

        $curve = $trainingRun->training_curve ?? ['timesteps' => [], 'mean_reward' => [], 'std_reward' => []];

        // Upper and lower bound for shaded std area
        $upper = array_map(
            fn($m, $s) => round($m + $s, 4),
            $curve['mean_reward'], $curve['std_reward']
        );
        $lower = array_map(
            fn($m, $s) => round($m - $s, 4),
            $curve['mean_reward'], $curve['std_reward']
        );

        // Eval episode rewards (box plot data)
        $evalRewards = $trainingRun->eval_all_rewards ?? [];

        // Train vs Eval comparison points
        $trainFinal = ! empty($curve['mean_reward'])
            ? round(end($curve['mean_reward']), 4)
            : null;

        return response()->json([
            'learning_curve' => [
                'labels'      => array_map(fn($t) => number_format($t), $curve['timesteps']),
                'mean_reward' => $curve['mean_reward'],
                'upper_bound' => $upper,
                'lower_bound' => $lower,
            ],
            'eval_distribution' => [
                'rewards'  => $evalRewards,
                'mean'     => $trainingRun->eval_mean_reward,
                'std'      => $trainingRun->eval_std_reward,
                'min'      => $trainingRun->eval_min_reward,
                'max'      => $trainingRun->eval_max_reward,
                'n'        => $trainingRun->eval_episodes,
            ],
            'train_vs_eval' => [
                'labels'      => ['Training (final 5)', 'Evaluation (mean over 10 eps)'],
                'values'      => [$trainingRun->train_mean_reward, $trainingRun->eval_mean_reward],
                'colors'      => ['rgba(14,165,233,0.8)', 'rgba(16,185,129,0.8)'],
            ],
            'summary' => [
                'algorithm'        => $trainingRun->algorithm,
                'total_timesteps'  => $trainingRun->total_timesteps,
                'train_mean'       => $trainingRun->train_mean_reward,
                'eval_mean'        => $trainingRun->eval_mean_reward,
                'eval_std'         => $trainingRun->eval_std_reward,
                'eval_success_rate'=> $trainingRun->eval_success_rate,
                'eval_episodes'    => $trainingRun->eval_episodes,
                'overfit_gap'      => $trainingRun->train_mean_reward !== null && $trainingRun->eval_mean_reward !== null
                    ? round($trainingRun->train_mean_reward - $trainingRun->eval_mean_reward, 4)
                    : null,
            ],
        ]);
    }

    private function authorize(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}