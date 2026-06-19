<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11px;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.5;
    }

    /* ── Cover Header ────────────────────────────── */
    .cover {
        background: #0f172a;
        color: #ffffff;
        padding: 36px 40px 28px;
        margin-bottom: 0;
    }
    .cover-tag {
        font-size: 9px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #7dd3fc;
        margin-bottom: 6px;
    }
    .cover-title {
        font-size: 20px;
        font-weight: bold;
        color: #f1f5f9;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .cover-sub {
        font-size: 12px;
        color: #94a3b8;
        margin-bottom: 20px;
    }
    .cover-meta {
        display: table;
        width: 100%;
        border-top: 1px solid #1e3a5f;
        padding-top: 14px;
    }
    .cover-meta-cell {
        display: table-cell;
        width: 33%;
        text-align: center;
    }
    .cover-meta-label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .cover-meta-value { font-size: 13px; font-weight: bold; color: #e2e8f0; margin-top: 2px; }

    /* ── Page body ───────────────────────────────── */
    .page-body { padding: 28px 40px; }

    /* ── Section ─────────────────────────────────── */
    .section { margin-bottom: 28px; }
    .section-title {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        padding-bottom: 6px;
        border-bottom: 2px solid #0ea5e9;
        margin-bottom: 14px;
    }

    /* ── Key-value table ─────────────────────────── */
    .kv-table { width: 100%; border-collapse: collapse; }
    .kv-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 10.5px;
    }
    .kv-table td:first-child { color: #64748b; width: 40%; }
    .kv-table td:last-child  { color: #1e293b; font-weight: 500; }
    .kv-table tr:nth-child(even) td { background: #f8fafc; }

    /* ── Metric boxes ────────────────────────────── */
    .metrics { display: table; width: 100%; margin-bottom: 18px; }
    .metric-box {
        display: table-cell;
        width: 25%;
        text-align: center;
        padding: 14px 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }
    .metric-box + .metric-box { margin-left: 8px; }
    .metric-value { font-size: 18px; font-weight: bold; color: #0369a1; }
    .metric-label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

    /* ── Data table ──────────────────────────────── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .data-table th {
        background: #0f172a;
        color: #94a3b8;
        text-transform: uppercase;
        font-size: 9px;
        letter-spacing: 0.5px;
        padding: 8px 10px;
        text-align: left;
    }
    .data-table td {
        padding: 7px 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .data-table tr:nth-child(even) td { background: #f8fafc; }
    .data-table tr:last-child td { border-bottom: none; }

    /* ── Reward pills ────────────────────────────── */
    .pill-wrap { font-size: 9.5px; line-height: 2; }
    .pill {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 3px;
        margin: 1px 2px;
        font-family: monospace;
    }
    .pill-pos { background: #dcfce7; color: #166534; }
    .pill-neg { background: #fee2e2; color: #991b1b; }

    /* ── Node cards ──────────────────────────────── */
    .node-grid { display: table; width: 100%; }
    .node-card {
        display: table-cell;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        vertical-align: top;
    }
    .node-card + .node-card { margin-left: 8px; }
    .node-name { font-size: 11px; font-weight: bold; color: #0f172a; margin-bottom: 8px; }
    .node-row  { display: table; width: 100%; font-size: 10px; margin-bottom: 4px; }
    .node-row-label { display: table-cell; color: #64748b; }
    .node-row-value { display: table-cell; text-align: right; font-weight: 500; color: #334155; }

    /* ── Comparison table ────────────────────────── */
    .compare-table { width: 100%; border-collapse: collapse; }
    .compare-table th {
        background: #1e293b;
        color: #94a3b8;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 12px;
        text-align: left;
    }
    .compare-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 10.5px; }
    .compare-table tr:nth-child(even) td { background: #f8fafc; }
    .highlight { color: #0369a1; font-weight: bold; }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
    }
    .badge-ppo { background: #ede9fe; color: #5b21b6; }
    .badge-dqn { background: #dcfce7; color: #166534; }

    /* ── Latency sample ──────────────────────────── */
    .latency-grid { display: table; width: 100%; }
    .latency-cell {
        display: table-cell;
        width: 10%;
        padding: 4px 3px;
        text-align: center;
        font-size: 9px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 3px;
        color: #0369a1;
    }

    /* ── Footer ──────────────────────────────────── */
    .footer {
        margin-top: 36px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        font-size: 9px;
        color: #94a3b8;
        text-align: center;
    }

    /* ── Page break ──────────────────────────────── */
    .page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════ --}}
{{-- COVER HEADER                               --}}
{{-- ══════════════════════════════════════════ --}}
<div class="cover">
    <div class="cover-tag">Final Year Project — Technical Report</div>
    <div class="cover-title">Resource Allocation in Edge Computing<br>using Deep Reinforcement Learning</div>
    <div class="cover-sub">{{ $simulation->name }}</div>
    <div class="cover-meta">
        <div class="cover-meta-cell">
            <div class="cover-meta-label">Report Date</div>
            <div class="cover-meta-value">{{ now()->format('M d, Y') }}</div>
        </div>
        <div class="cover-meta-cell">
            <div class="cover-meta-label">Algorithm</div>
            <div class="cover-meta-value">{{ $simulation->algorithm }}</div>
        </div>
        <div class="cover-meta-cell">
            <div class="cover-meta-label">Simulation ID</div>
            <div class="cover-meta-value">#{{ $simulation->id }}</div>
        </div>
    </div>
</div>

<div class="page-body">

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 1: SIMULATION OVERVIEW            --}}
{{-- ══════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">1. Simulation Overview</div>
    <table class="kv-table">
        @foreach([
            ['Simulation Name',    $simulation->name],
            ['Description',        $simulation->description ?: 'N/A'],
            ['DRL Algorithm',      $simulation->algorithm],
            ['Edge Nodes',         $simulation->num_edge_nodes],
            ['IoT Devices',        $simulation->num_iot_devices],
            ['Tasks Generated',    $taskStats['total']],
            ['Training Runs',      $trainingRuns->count()],
            ['Simulation Status',  ucfirst($simulation->status)],
            ['Created At',         $simulation->created_at->format('M d, Y H:i:s')],
            ['Completed At',       $simulation->completed_at?->format('M d, Y H:i:s') ?? 'N/A'],
        ] as [$label, $value])
        <tr>
            <td>{{ $label }}</td>
            <td>{{ $value }}</td>
        </tr>
        @endforeach
    </table>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 2: TASK WORKLOAD                  --}}
{{-- ══════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">2. IoT Task Workload Analysis</div>

    <div class="metrics">
        @foreach([
            ['Total Tasks',    $taskStats['total']],
            ['Completed',      $taskStats['completed']],
            ['Failed',         $taskStats['failed']],
            ['Pending',        $taskStats['pending']],
        ] as [$label, $value])
        <div class="metric-box">
            <div class="metric-value">{{ $value }}</div>
            <div class="metric-label">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Priority Level</th>
                <th>Task Count</th>
                <th>Percentage</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = max($taskStats['total'], 1);
                $priorityDesc = [
                    'low'      => 'Background monitoring, non-urgent data',
                    'medium'   => 'Standard IoT telemetry processing',
                    'high'     => 'Time-sensitive actuator commands',
                    'critical' => 'Emergency alerts, real-time control',
                ];
            @endphp
            @foreach(['low','medium','high','critical'] as $p)
            <tr>
                <td><strong>{{ ucfirst($p) }}</strong></td>
                <td>{{ $taskStats['by_priority'][$p] }}</td>
                <td>{{ round($taskStats['by_priority'][$p] / $total * 100, 1) }}%</td>
                <td>{{ $priorityDesc[$p] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($latestResult)

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 3: PERFORMANCE METRICS            --}}
{{-- ══════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">3. DRL Performance Metrics</div>

    <div class="metrics">
        @foreach([
            ['Total Reward',   round($latestResult->total_reward ?? 0, 3)],
            ['Avg Latency',    round($latestResult->avg_latency ?? 0, 1) . ' ms'],
            ['Success Rate',   round($latestResult->task_success_rate ?? 0, 1) . '%'],
            ['Throughput',     round($latestResult->throughput ?? 0, 3) . ' t/s'],
        ] as [$label, $value])
        <div class="metric-box">
            <div class="metric-value">{{ $value }}</div>
            <div class="metric-label">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 4: REWARD HISTORY                 --}}
{{-- ══════════════════════════════════════════ --}}
@if(! empty($latestResult->reward_history))
<div class="section">
    <div class="section-title">4. Reward History (Evaluation Episode)</div>
    <p style="font-size:10px;color:#64748b;margin-bottom:10px;">
        Per-step reward assigned by the environment during the post-training evaluation episode.
        Positive rewards (green) indicate successful low-latency allocations.
        Negative rewards (red) indicate overloaded nodes or delayed tasks.
    </p>
    <div class="pill-wrap">
        @foreach($latestResult->reward_history as $i => $r)
            <span class="pill {{ $r >= 0 ? 'pill-pos' : 'pill-neg' }}">
                S{{ $i+1 }}: {{ round($r, 2) }}
            </span>
        @endforeach
    </div>

    @php
        $rewards  = $latestResult->reward_history;
        $posCount = count(array_filter($rewards, fn($r) => $r >= 0));
        $negCount = count($rewards) - $posCount;
        $avgRew   = count($rewards) > 0 ? round(array_sum($rewards) / count($rewards), 4) : 0;
        $maxRew   = count($rewards) > 0 ? round(max($rewards), 4) : 0;
        $minRew   = count($rewards) > 0 ? round(min($rewards), 4) : 0;
    @endphp

    <table class="kv-table" style="margin-top:12px;">
        <tr><td>Total Steps</td>       <td>{{ count($rewards) }}</td></tr>
        <tr><td>Positive Rewards</td>  <td>{{ $posCount }} ({{ round($posCount/max(count($rewards),1)*100,1) }}%)</td></tr>
        <tr><td>Negative Rewards</td>  <td>{{ $negCount }} ({{ round($negCount/max(count($rewards),1)*100,1) }}%)</td></tr>
        <tr><td>Average Reward</td>    <td>{{ $avgRew }}</td></tr>
        <tr><td>Maximum Reward</td>    <td>{{ $maxRew }}</td></tr>
        <tr><td>Minimum Reward</td>    <td>{{ $minRew }}</td></tr>
    </table>
</div>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 5: LATENCY ANALYSIS               --}}
{{-- ══════════════════════════════════════════ --}}
@if(! empty($latestResult->latency_history))
<div class="section page-break">
    <div class="section-title">5. Task Latency Analysis</div>
    @php
        $lats    = $latestResult->latency_history;
        $avgLat  = count($lats) > 0 ? round(array_sum($lats)/count($lats), 2) : 0;
        $maxLat  = count($lats) > 0 ? round(max($lats), 2) : 0;
        $minLat  = count($lats) > 0 ? round(min($lats), 2) : 0;
        $sorted  = $lats; sort($sorted);
        $p50     = $sorted[(int)(count($sorted)*0.5)] ?? 0;
        $p90     = $sorted[(int)(count($sorted)*0.9)] ?? 0;
        $p99     = $sorted[(int)(count($sorted)*0.99)] ?? 0;
    @endphp
    <table class="kv-table" style="margin-bottom:14px;">
        <tr><td>Mean Latency</td>      <td>{{ $avgLat }} ms</td></tr>
        <tr><td>Min Latency</td>       <td>{{ $minLat }} ms</td></tr>
        <tr><td>Max Latency</td>       <td>{{ $maxLat }} ms</td></tr>
        <tr><td>P50 (Median)</td>      <td>{{ round($p50, 2) }} ms</td></tr>
        <tr><td>P90</td>               <td>{{ round($p90, 2) }} ms</td></tr>
        <tr><td>P99</td>               <td>{{ round($p99, 2) }} ms</td></tr>
    </table>

    <p style="font-size:10px;color:#64748b;margin-bottom:8px;">
        Per-task latency (ms) — first 20 tasks:
    </p>
    <table class="data-table">
        <thead>
            <tr>
                @for($i = 1; $i <= min(10, count($lats)); $i++)
                <th>Task {{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <tr>
                @for($i = 0; $i < min(10, count($lats)); $i++)
                <td>{{ round($lats[$i], 0) }}</td>
                @endfor
            </tr>
            @if(count($lats) > 10)
            <tr>
                @for($i = 10; $i < min(20, count($lats)); $i++)
                <td>{{ round($lats[$i], 0) }}</td>
                @endfor
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 6: EDGE NODE UTILIZATION          --}}
{{-- ══════════════════════════════════════════ --}}
@if(! empty($latestResult->node_utilization))
<div class="section">
    <div class="section-title">6. Edge Node Utilization</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Node Name</th>
                <th>CPU Util %</th>
                <th>Memory Util %</th>
                <th>Queue Length</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($latestResult->node_utilization as $node)
            <tr>
                <td><strong>{{ $node['name'] }}</strong></td>
                <td>{{ round($node['cpu_util'] ?? 0, 1) }}%</td>
                <td>{{ round($node['memory_util'] ?? 0, 1) }}%</td>
                <td>{{ $node['queue_length'] ?? 0 }}</td>
                <td>{{ ucfirst($node['status'] ?? 'idle') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endif {{-- end if latestResult --}}

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 7: TRAINING RUNS                  --}}
{{-- ══════════════════════════════════════════ --}}
@if($trainingRuns->count() > 0)
<div class="section">
    <div class="section-title">7. Training Runs Summary</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Run ID</th>
                <th>Algorithm</th>
                <th>Timesteps</th>
                <th>Mean Reward</th>
                <th>Final Reward</th>
                <th>Avg Latency</th>
                <th>Duration</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainingRuns as $run)
            @php
                $rr = $run->results()->latest()->first();
                $duration = $run->started_at && $run->completed_at
                    ? $run->started_at->diffInSeconds($run->completed_at) . 's'
                    : 'N/A';
            @endphp
            <tr>
                <td>#{{ $run->id }}</td>
                <td><span class="badge badge-{{ strtolower($run->algorithm) }}">{{ $run->algorithm }}</span></td>
                <td>{{ number_format($run->total_timesteps) }}</td>
                <td class="highlight">{{ round($run->mean_reward ?? 0, 4) }}</td>
                <td>{{ round($run->final_reward ?? 0, 4) }}</td>
                <td>{{ $rr ? round($rr->avg_latency ?? 0, 1).' ms' : '—' }}</td>
                <td>{{ $duration }}</td>
                <td>{{ ucfirst($run->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- SECTION 8: PPO vs DQN COMPARISON          --}}
{{-- ══════════════════════════════════════════ --}}
@if($ppoRun && $dqnRun)
<div class="section">
    <div class="section-title">8. Algorithm Comparison: PPO vs DQN</div>
    <table class="compare-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th>PPO (Run #{{ $ppoRun->id }})</th>
                <th>DQN (Run #{{ $dqnRun->id }})</th>
                <th>Winner</th>
            </tr>
        </thead>
        @php
            $ppoRes = $ppoRun->results()->latest()->first();
            $dqnRes = $dqnRun->results()->latest()->first();
            $comparisons = [
                ['Mean Reward',   $ppoRun->mean_reward,             $dqnRun->mean_reward,             'higher'],
                ['Final Reward',  $ppoRun->final_reward,            $dqnRun->final_reward,            'higher'],
                ['Avg Latency',   $ppoRes?->avg_latency ?? 0,       $dqnRes?->avg_latency ?? 0,       'lower'],
                ['Timesteps',     $ppoRun->total_timesteps,         $dqnRun->total_timesteps,         'lower'],
                ['Success Rate',  $ppoRes?->task_success_rate ?? 0, $dqnRes?->task_success_rate ?? 0, 'higher'],
            ];
        @endphp
        <tbody>
            @foreach($comparisons as [$metric, $ppoVal, $dqnVal, $prefer])
            @php
                $winner = ($prefer === 'higher')
                    ? ($ppoVal >= $dqnVal ? 'PPO' : 'DQN')
                    : ($ppoVal <= $dqnVal ? 'PPO' : 'DQN');
            @endphp
            <tr>
                <td><strong>{{ $metric }}</strong></td>
                <td>{{ round($ppoVal, 4) }}</td>
                <td>{{ round($dqnVal, 4) }}</td>
                <td>
                    <span class="badge badge-{{ strtolower($winner) }}">{{ $winner }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="font-size:9.5px;color:#64748b;margin-top:10px;">
        * PPO (Proximal Policy Optimization) is generally more stable for continuous action spaces.
        DQN (Deep Q-Network) converges faster on discrete action problems.
        Both are evaluated under identical environment conditions.
    </p>
</div>
@elseif($ppoRun || $dqnRun)
<div class="section">
    <div class="section-title">8. Algorithm Note</div>
    <p style="font-size:10.5px;color:#475569;">
        Only <strong>{{ $simulation->algorithm }}</strong> has been trained for this simulation.
        To generate a PPO vs DQN comparison, create a new simulation with the other algorithm
        and run training, then both results will appear here.
    </p>
</div>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- FOOTER                                    --}}
{{-- ══════════════════════════════════════════ --}}
<div class="footer">
    <strong>Edge DRL Simulation Platform</strong> &nbsp;|&nbsp;
    Resource Allocation in Edge Computing using Deep Reinforcement Learning &nbsp;|&nbsp;
    Generated: {{ now()->format('M d, Y \a\t H:i') }} &nbsp;|&nbsp;
    Simulation #{{ $simulation->id }}
</div>

</div>{{-- end page-body --}}
</body>
</html>
