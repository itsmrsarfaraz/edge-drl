<x-layouts.app :title="$simulation->name . ' — Analytics'">
    <x-ui.breadcrumb :items="[
        ['label' => 'Simulations',  'route' => route('simulations.index')],
        ['label' => $simulation->name, 'route' => route('simulations.show', $simulation)],
        ['label' => 'Analytics'],
    ]"/>
    <x-ui.page-header
        :title="$simulation->name"
        description="DRL training analytics and resource allocation performance.">
        <x-slot:action>
            <div class="flex items-center gap-3">
                <a href="{{ route('simulations.training.show', $simulation) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-500
                          text-white text-xs font-medium rounded-lg transition-colors">
                    Run Again
                </a>
                <a href="{{ route('simulations.show', $simulation) }}"
                   class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                    ← Simulation
                </a>
                <a href="{{ route('simulations.reports.index', $simulation) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700
                          text-slate-300 text-xs font-medium rounded-lg transition-colors">
                    PDF Report
                </a>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    @if(! $latestResult)
        <x-ui.empty-state
            title="No results yet"
            description="Run the simulation training first to generate analytics data."
            icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
            action-label="Run Training"
            :action-route="route('simulations.training.show', $simulation)"
        />
    @else

    {{-- Summary Metrics --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-analytics.metric
            label="Avg Latency"
            :value="$summary['avg_latency'] ? $summary['avg_latency'] . ' ms' : '—'"
            sub="Mean task processing latency"
            color="amber"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
        />
        <x-analytics.metric
            label="Total Reward"
            :value="$summary['total_reward'] ?? '—'"
            sub="Cumulative episode reward"
            color="primary"
            icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
        />
        <x-analytics.metric
            label="Training Runs"
            :value="$summary['training_runs']"
            :sub="'Best mean R̄: ' . ($summary['best_mean_reward'] ? round($summary['best_mean_reward'], 3) : '—')"
            color="violet"
            icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
        />
        <x-analytics.metric
            label="Total Tasks"
            :value="$summary['total_tasks']"
            sub="Tasks in simulation"
            color="emerald"
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
    </div>

    {{-- Charts Grid --}}
    <div x-data="analyticsCharts('{{ route('simulations.analytics.chart-data', $simulation) }}')"
         x-init="loadCharts()"
         class="space-y-6">

        {{-- Loading state --}}
        <div x-show="loading" class="flex items-center justify-center py-20">
            <div class="flex items-center gap-3 text-slate-400">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-sm">Loading chart data…</span>
            </div>
        </div>

        <div x-show="! loading" x-cloak>

            {{-- Row 1: Reward Trend + Latency Trend --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <x-charts.card
                    title="Reward Trend"
                    description="Per-step reward during the evaluation episode. Smoothed line = moving average (window 5)."
                    height="h-72">
                    <canvas id="rewardChart"></canvas>
                </x-charts.card>

                <x-charts.card
                    title="Latency per Task"
                    description="Processing latency in milliseconds for each allocated task."
                    height="h-72">
                    <canvas id="latencyChart"></canvas>
                </x-charts.card>

            </div>

            {{-- Row 2: Node Utilization + Task Priority --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <x-charts.card
                    title="Edge Node Utilization"
                    description="CPU and memory utilisation per edge node at simulation end."
                    height="h-72">
                    <canvas id="nodeChart"></canvas>
                </x-charts.card>

                <x-charts.card
                    title="Task Priority Distribution"
                    description="Breakdown of task priorities generated for this simulation."
                    height="h-72">
                    <canvas id="priorityChart"></canvas>
                </x-charts.card>

            </div>

            {{-- Row 3: Algorithm Comparison (full width) --}}
            <x-charts.card
                title="Algorithm / Run Comparison"
                description="Mean reward and average latency across all completed training runs."
                height="h-80">
                <canvas id="algoChart"></canvas>
            </x-charts.card>

            {{-- Run Summary Table --}}
            @if($trainingRuns->count() > 0)
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden mt-6">
                <div class="px-5 py-4 border-b border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-300">Completed Training Runs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-800/40">
                                @foreach(['Run ID', 'Algorithm', 'Mean Reward'] as $col)
                                    <th>{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($trainingRuns as $run)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3 font-mono text-xs text-slate-400">#{{ $run->id }}</td>
                                <td class="px-5 py-3">
                                    <x-ui.badge color="violet">{{ $run->algorithm }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-3 text-slate-300 font-mono">
                                    {{ $run->mean_reward !== null ? round($run->mean_reward, 4) : '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-300 font-mono">
                                    {{ $run->final_reward !== null ? round($run->final_reward, 4) : '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-300">
                                    {{ number_format($run->total_timesteps) }}
                                </td>
                                <td class="px-5 py-3 text-slate-400 text-xs">
                                    {{ $run->completed_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>{{-- end x-show --}}
    </div>{{-- end x-data --}}

    @endif

</x-layouts.app>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Shared Chart.js defaults ───────────────────────────────────
Chart.defaults.color          = '#94a3b8';
Chart.defaults.borderColor    = '#1e293b';
Chart.defaults.font.family    = 'Inter, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.font.size      = 11;

// ── Color palette ──────────────────────────────────────────────
const COLORS = {
    primary:  { line: '#0ea5e9', fill: 'rgba(14,165,233,0.1)',  point: '#0ea5e9' },
    emerald:  { line: '#10b981', fill: 'rgba(16,185,129,0.1)',  point: '#10b981' },
    amber:    { line: '#f59e0b', fill: 'rgba(245,158,11,0.1)',  point: '#f59e0b' },
    violet:   { line: '#8b5cf6', fill: 'rgba(139,92,246,0.1)', point: '#8b5cf6' },
    red:      { line: '#ef4444', fill: 'rgba(239,68,68,0.1)',   point: '#ef4444' },
    slate:    { line: '#64748b', fill: 'rgba(100,116,139,0.1)', point: '#64748b' },
};

function analyticsCharts(dataUrl) {
    return {
        loading:  true,
        charts:   {},

        async loadCharts() {
            try {
                const res  = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                this.loading = false;

                await this.$nextTick();

                this.buildRewardChart(data.reward_trend);
                this.buildLatencyChart(data.latency_trend);
                this.buildNodeChart(data.node_utilization);
                this.buildPriorityChart(data.priority_breakdown);
                this.buildAlgoChart(data.algo_comparison);

            } catch(e) {
                console.error('Chart load error:', e);
                this.loading = false;
            }
        },

        // ── 1. Reward Trend ───────────────────────────────────
        buildRewardChart(d) {
            if (! d || ! d.labels.length) return;
            const ctx = document.getElementById('rewardChart');
            if (! ctx) return;

            this.charts.reward = new Chart(ctx, {
                type: 'line',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'Step Reward',
                            data:            d.data,
                            borderColor:     COLORS.primary.line,
                            backgroundColor: COLORS.primary.fill,
                            fill:            true,
                            tension:         0.1,
                            pointRadius:     2,
                            pointHoverRadius:5,
                            borderWidth:     1.5,
                        },
                        {
                            label:       'Moving Avg (5)',
                            data:        d.smoothed,
                            borderColor: COLORS.emerald.line,
                            fill:        false,
                            tension:     0.4,
                            pointRadius: 0,
                            borderWidth: 2.5,
                            borderDash:  [],
                        },
                    ],
                },
                options: this.lineOptions('Reward'),
            });
        },

        // ── 2. Latency Trend ──────────────────────────────────
        buildLatencyChart(d) {
            if (! d || ! d.labels.length) return;
            const ctx = document.getElementById('latencyChart');
            if (! ctx) return;

            // Avg line data (flat)
            const avgLine = new Array(d.data.length).fill(d.avg);

            this.charts.latency = new Chart(ctx, {
                type: 'line',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'Latency (ms)',
                            data:            d.data,
                            borderColor:     COLORS.amber.line,
                            backgroundColor: COLORS.amber.fill,
                            fill:            true,
                            tension:         0.2,
                            pointRadius:     1.5,
                            borderWidth:     1.5,
                        },
                        {
                            label:       `Avg (${d.avg} ms)`,
                            data:        avgLine,
                            borderColor: COLORS.red.line,
                            fill:        false,
                            pointRadius: 0,
                            borderWidth: 1.5,
                            borderDash:  [6, 3],
                        },
                    ],
                },
                options: this.lineOptions('Latency (ms)'),
            });
        },

        // ── 3. Node Utilization ───────────────────────────────
        buildNodeChart(d) {
            if (! d || ! d.labels.length) return;
            const ctx = document.getElementById('nodeChart');
            if (! ctx) return;

            this.charts.node = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'CPU Util %',
                            data:            d.cpu,
                            backgroundColor: 'rgba(14,165,233,0.7)',
                            borderColor:     COLORS.primary.line,
                            borderWidth:     1,
                            borderRadius:    4,
                        },
                        {
                            label:           'Memory Util %',
                            data:            d.memory,
                            backgroundColor: 'rgba(139,92,246,0.7)',
                            borderColor:     COLORS.violet.line,
                            borderWidth:     1,
                            borderRadius:    4,
                        },
                    ],
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: {
                        x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                        y: {
                            grid:        { color: '#1e293b' },
                            ticks:       { color: '#64748b', callback: v => v + '%' },
                            min:         0,
                            max:         100,
                            title:       { display: true, text: 'Utilization %', color: '#64748b' },
                        },
                    },
                },
            });
        },

        // ── 4. Priority Doughnut ──────────────────────────────
        buildPriorityChart(d) {
            if (! d || ! d.labels.length) return;
            const ctx = document.getElementById('priorityChart');
            if (! ctx) return;

            this.charts.priority = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels:   d.labels,
                    datasets: [{
                        data:            d.data,
                        backgroundColor: [
                            'rgba(100,116,139,0.8)',
                            'rgba(14,165,233,0.8)',
                            'rgba(245,158,11,0.8)',
                            'rgba(239,68,68,0.8)',
                        ],
                        borderColor:     '#0f172a',
                        borderWidth:     3,
                        hoverOffset:     8,
                    }],
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    cutout:              '65%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels:   { boxWidth: 12, padding: 16, color: '#94a3b8' },
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                    const pct   = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                }
                            }
                        },
                    },
                },
            });
        },

        // ── 5. Algorithm Comparison ───────────────────────────
        buildAlgoChart(d) {
            if (! d || ! d.labels.length) return;
            const ctx = document.getElementById('algoChart');
            if (! ctx) return;

            this.charts.algo = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'Mean Reward',
                            data:            d.mean_reward,
                            backgroundColor: 'rgba(16,185,129,0.7)',
                            borderColor:     COLORS.emerald.line,
                            borderWidth:     1,
                            borderRadius:    4,
                            yAxisID:         'y',
                        },
                        {
                            label:           'Final Reward',
                            data:            d.final_reward,
                            backgroundColor: 'rgba(14,165,233,0.7)',
                            borderColor:     COLORS.primary.line,
                            borderWidth:     1,
                            borderRadius:    4,
                            yAxisID:         'y',
                        },
                        {
                            label:           'Avg Latency (ms)',
                            data:            d.latency,
                            type:            'line',
                            borderColor:     COLORS.amber.line,
                            backgroundColor: COLORS.amber.fill,
                            fill:            false,
                            tension:         0.3,
                            pointRadius:     5,
                            borderWidth:     2,
                            yAxisID:         'y2',
                        },
                    ],
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    interaction:         { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
                    },
                    scales: {
                        x:  { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                        y:  {
                            type:     'linear',
                            position: 'left',
                            grid:     { color: '#1e293b' },
                            ticks:    { color: '#64748b' },
                            title:    { display: true, text: 'Reward', color: '#64748b' },
                        },
                        y2: {
                            type:     'linear',
                            position: 'right',
                            grid:     { drawOnChartArea: false },
                            ticks:    { color: COLORS.amber.line, callback: v => v + ' ms' },
                            title:    { display: true, text: 'Latency (ms)', color: COLORS.amber.line },
                        },
                    },
                },
            });
        },

        // ── Shared line chart options ─────────────────────────
        lineOptions(yLabel) {
            return {
                responsive:          true,
                maintainAspectRatio: false,
                interaction:         { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` } },
                },
                scales: {
                    x: {
                        grid:    { color: '#1e293b' },
                        ticks:   { color: '#64748b', maxTicksLimit: 12, maxRotation: 0 },
                    },
                    y: {
                        grid:    { color: '#1e293b' },
                        ticks:   { color: '#64748b' },
                        title:   { display: true, text: yLabel, color: '#64748b' },
                    },
                },
            };
        },
    };
}
</script>
@endpush
