<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Simulation;
use App\Models\TrainingRun;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $simulation->load(['trainingRuns', 'results', 'edgeNodes']);
        $results      = $simulation->results()->with('trainingRun')->latest()->get();
        $latestResult = $results->first();
        $trainingRuns = $simulation->trainingRuns()->where('status', 'completed')->orderBy('id')->get();

        return view('reports.index', compact(
            'simulation', 'results', 'latestResult', 'trainingRuns'
        ));
    }

    public function download(Simulation $simulation)
    {
        $this->authorizeSimulation($simulation);

        $simulation->load(['trainingRuns', 'results', 'edgeNodes', 'iotDevices']);
        $results      = $simulation->results()->with('trainingRun')->latest()->get();
        $latestResult = $results->first();
        $trainingRuns = $simulation->trainingRuns()->where('status', 'completed')->orderBy('id')->get();

        $taskStats = [
            'total'     => $simulation->tasks()->count(),
            'completed' => $simulation->tasks()->where('status', 'completed')->count(),
            'failed'    => $simulation->tasks()->where('status', 'failed')->count(),
            'pending'   => $simulation->tasks()->where('status', 'pending')->count(),
            'by_priority' => [
                'low'      => $simulation->tasks()->where('priority', 'low')->count(),
                'medium'   => $simulation->tasks()->where('priority', 'medium')->count(),
                'high'     => $simulation->tasks()->where('priority', 'high')->count(),
                'critical' => $simulation->tasks()->where('priority', 'critical')->count(),
            ],
        ];

        // PPO vs DQN comparison if both exist
        $ppoRun = $trainingRuns->where('algorithm', 'PPO')->sortByDesc('mean_reward')->first();
        $dqnRun = $trainingRuns->where('algorithm', 'DQN')->sortByDesc('mean_reward')->first();

        $pdf = Pdf::loadView('reports.pdf', compact(
            'simulation',
            'results',
            'latestResult',
            'trainingRuns',
            'taskStats',
            'ppoRun',
            'dqnRun'
        ))->setPaper('a4', 'portrait');

        $filename = 'edge-drl-report-' . $simulation->id . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function authorizeSimulation(Simulation $simulation): void
    {
        abort_if($simulation->user_id !== Auth::id(), 403);
    }
}