<x-layouts.app title="Algorithm Comparison">

    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'route' => route('dashboard')],
        ['label' => 'PPO vs DQN Comparison'],
    ]"/>

    <x-ui.page-header
        title="PPO vs DQN Comparison"
        description="Side-by-side performance analysis across all your training runs."/>

    @if(! $bestPpo && ! $bestDqn)
        <x-ui.empty-state
            title="No completed training runs yet"
            description="Run at least one PPO and one DQN simulation to see the comparison."
            icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
            action-label="Create Simulation"
            :action-route="route('simulations.create')"
        />
    @else

    {{-- Head-to-head Summary Cards --}}
    @if($bestPpo && $bestDqn)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        {{-- PPO Card --}}
        <div class="bg-slate-900 border border-violet-500/30 rounded-xl p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-3 h-3 rounded-full bg-violet-500"></div>
                <h3 class="text-sm font-bold text-violet-400">PPO — Best Run #{{ $bestPpo->id }}</h3>
            </div>
            @foreach([
                ['Mean Reward',   round($bestPpo->mean_reward ?? 0, 4)],
                ['Final Reward',  round($bestPpo->final_reward ?? 0, 4)],
                ['Timesteps',     number_format($bestPpo->total_timesteps)],
                ['Simulation',    $bestPpo->simulation->name],
            ] as [$label, $val])
            <div class="flex justify-between py-2 border-b border-slate-800 last:border-0 text-sm">
                <span class="text-slate-400">{{ $label }}</span>
                <span class="text-slate-200 font-medium">{{ $val }}</span>
            </div>
            @endforeach
        </div>

        {{-- VS Badge --}}
        <div class="flex items-center justify-center">
            <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-slate-700
                        flex items-center justify-center">
                <span class="text-lg font-black text-slate-400">VS</span>
            </div>
        </div>

        {{-- DQN Card --}}
        <div class="bg-slate-900 border border-emerald-500/30 rounded-xl p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                <h3 class="text-sm font-bold text-emerald-400">DQN — Best Run #{{ $bestDqn->id }}</h3>
            </div>
            @foreach([
                ['Mean Reward',   round($bestDqn->mean_reward ?? 0, 4)],
                ['Final Reward',  round($bestDqn->final_reward ?? 0, 4)],
                ['Timesteps',     number_format($bestDqn->total_timesteps)],
                ['Simulation',    $bestDqn->simulation->name],
            ] as [$label, $val])
            <div class="flex justify-between py-2 border-b border-slate-800 last:border-0 text-sm">
                <span class="text-slate-400">{{ $label }}</span>
                <span class="text-slate-200 font-medium">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Winner Summary --}}
    @php
        $ppoMean   = $bestPpo->mean_reward  ?? 0;
        $dqnMean   = $bestDqn->mean_reward  ?? 0;
        $winner    = $ppoMean >= $dqnMean ? 'PPO' : 'DQN';
        $winColor  = $winner === 'PPO' ? 'violet' : 'emerald';
        $margin    = round(abs($ppoMean - $dqnMean), 4);
    @endphp
    <div class="bg-slate-900 border border-{{ $winColor }}-500/20 rounded-xl px-6 py-4 mb-6
                flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-{{ $winColor }}-500/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-{{ $winColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-200">
                <span class="text-{{ $winColor }}-400">{{ $winner }}</span> leads by mean reward margin of
                <span class="font-mono text-{{ $winColor }}-400">{{ $margin }}</span>
            </p>
            <p class="text-xs text-slate-500 mt-0.5">
                Based on best performing run of each algorithm across all your simulations.
            </p>
        </div>
    </div>
    @endif

    {{-- Charts --}}
    <div x-data="comparisonCharts('{{ route('comparison.chart-data') }}')"
         x-init="load()"
         class="space-y-6">

        <div x-show="loading" class="flex justify-center py-16">
            <div class="flex items-center gap-3 text-slate-400">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-sm">Loading comparison data…</span>
            </div>
        </div>

        <div x-show="!loading" x-cloak class="space-y-6">

            {{-- Reward History Overlay --}}
            @if($bestPpo && $bestDqn)
            <x-charts.card
                title="PPO vs DQN — Reward History Overlay"
                description="Step-by-step reward comparison of the best run from each algorithm."
                height="h-80">
                <canvas id="historyChart"></canvas>
            </x-charts.card>
            @endif

            {{-- All Runs Comparison --}}
            <x-charts.card
                title="All Runs — Mean Reward Comparison"
                description="Mean reward across every completed training run, grouped by algorithm."
                height="h-72">
                <canvas id="runsChart"></canvas>
            </x-charts.card>

        </div>
    </div>

    {{-- Detailed Comparison Table --}}
    @if($bestPpo && $bestDqn)
    @php
        $ppoResult = $bestPpo->results->first();
        $dqnResult = $bestDqn->results->first();
        $rows = [
            ['Metric',              'PPO',                                      'DQN',                                    'Better'],
            ['Mean Reward',        round($bestPpo->mean_reward ?? 0, 4),        round($bestDqn->mean_reward ?? 0, 4),    'higher'],
            ['Final Reward',       round($bestPpo->final_reward ?? 0, 4),       round($bestDqn->final_reward ?? 0, 4),   'higher'],
            ['Avg Latency (ms)',   round($ppoResult->avg_latency ?? 0, 1),      round($dqnResult->avg_latency ?? 0, 1),  'lower'],
            ['Total Reward',       round($ppoResult->total_reward ?? 0, 3),     round($dqnResult->total_reward ?? 0, 3), 'higher'],
            ['Throughput (t/s)',   round($ppoResult->throughput ?? 0, 3),        round($dqnResult->throughput ?? 0, 3),   'higher'],
            ['Timesteps',          number_format($bestPpo->total_timesteps),    number_format($bestDqn->total_timesteps),'lower'],
        ];
    @endphp
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-slate-800">
            <h3 class="text-sm font-semibold text-slate-300">Head-to-Head Metrics Table</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 bg-slate-800/40">
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider px-5 py-3">Metric</th>
                    <th class="text-left text-xs font-medium text-violet-400 uppercase tracking-wider px-5 py-3">PPO</th>
                    <th class="text-left text-xs font-medium text-emerald-400 uppercase tracking-wider px-5 py-3">DQN</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider px-5 py-3">Winner</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach(array_slice($rows, 1) as [$metric, $ppoVal, $dqnVal, $prefer])
                @php
                    $ppoNum  = is_numeric($ppoVal) ? floatval($ppoVal) : 0;
                    $dqnNum  = is_numeric($dqnVal) ? floatval($dqnVal) : 0;
                    $winner  = $prefer === 'higher'
                        ? ($ppoNum >= $dqnNum ? 'PPO' : 'DQN')
                        : ($ppoNum <= $dqnNum ? 'PPO' : 'DQN');
                    $wColor  = $winner === 'PPO' ? 'violet' : 'emerald';
                @endphp
                <tr class="hover:bg-slate-800/30">
                    <td class="px-5 py-3 font-medium text-slate-300">{{ $metric }}</td>
                    <td class="px-5 py-3 font-mono {{ $winner === 'PPO' ? 'text-violet-400 font-bold' : 'text-slate-400' }}">
                        {{ $ppoVal }}
                    </td>
                    <td class="px-5 py-3 font-mono {{ $winner === 'DQN' ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">
                        {{ $dqnVal }}
                    </td>
                    <td class="px-5 py-3">
                        <x-ui.badge color="{{ $wColor }}">{{ $winner }}</x-ui.badge>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Individual Run Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        @foreach([['PPO', $ppoRuns, 'violet'], ['DQN', $dqnRuns, 'emerald']] as [$algo, $runs, $color])
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-{{ $color }}-500"></div>
                <h3 class="text-sm font-semibold text-slate-300">All {{ $algo }} Runs</h3>
            </div>
            @if($runs->isEmpty())
                <p class="text-sm text-slate-500 text-center py-8">No {{ $algo }} runs yet.</p>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 bg-slate-800/30">
                        <th class="text-left text-xs font-medium text-slate-400 uppercase px-4 py-2.5">Run</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase px-4 py-2.5">Simulation</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase px-4 py-2.5">Mean R</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase px-4 py-2.5">Done</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($runs as $run)
                    <tr class="hover:bg-slate-800/20">
                        <td class="px-4 py-2.5 font-mono text-xs text-slate-400">#{{ $run->id }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-300 truncate max-w-[120px]">
                            <a href="{{ route('simulations.show', $run->simulation) }}"
                               class="hover:text-{{ $color }}-400 transition-colors">
                                {{ $run->simulation->name }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5 font-mono text-xs
                                   {{ $run->id === ($algo === 'PPO' ? $bestPpo?->id : $bestDqn?->id) ? 'text-'.$color.'-400 font-bold' : 'text-slate-300' }}">
                            {{ round($run->mean_reward ?? 0, 4) }}
                        </td>
                        <td class="px-4 py-2.5 text-xs text-slate-500">
                            {{ $run->completed_at?->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @endforeach
    </div>

    @endif {{-- end if bestPpo || bestDqn --}}

</x-layouts.app>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color       = '#94a3b8';
Chart.defaults.borderColor = '#1e293b';
Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.font.size   = 11;

function comparisonCharts(dataUrl) {
    return {
        loading: true,

        async load() {
            try {
                const res  = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                this.loading = false;
                await this.$nextTick();
                this.buildRunsChart(data.run_comparison);
                this.buildHistoryChart(data.reward_history);
            } catch(e) {
                console.error(e);
                this.loading = false;
            }
        },

        buildHistoryChart(d) {
            const ctx = document.getElementById('historyChart');
            if (!ctx || !d.ppo.length && !d.dqn.length) return;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.labels,
                    datasets: [
                        {
                            label:           d.ppo_label,
                            data:            d.ppo,
                            borderColor:     '#8b5cf6',
                            backgroundColor: 'rgba(139,92,246,0.08)',
                            fill:            true,
                            tension:         0.3,
                            pointRadius:     2,
                            borderWidth:     2,
                        },
                        {
                            label:           d.dqn_label,
                            data:            d.dqn,
                            borderColor:     '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            fill:            true,
                            tension:         0.3,
                            pointRadius:     2,
                            borderWidth:     2,
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } } },
                    scales: {
                        x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', maxTicksLimit: 12, maxRotation: 0 } },
                        y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' },
                             title: { display: true, text: 'Reward', color: '#64748b' } },
                    },
                },
            });
        },

        buildRunsChart(d) {
            const ctx = document.getElementById('runsChart');
            if (!ctx || !d.labels.length) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [
                        {
                            label:           'Mean Reward',
                            data:            d.meanRewards,
                            backgroundColor: d.colors.map(c => c + 'cc'),
                            borderColor:     d.colors,
                            borderWidth:     1,
                            borderRadius:    4,
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false },
                               tooltip: { callbacks: { label: ctx => ` Mean Reward: ${ctx.parsed.y}` } } },
                    scales: {
                        x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', maxRotation: 20 } },
                        y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' },
                             title: { display: true, text: 'Mean Reward', color: '#64748b' } },
                    },
                },
            });
        },
    }
}
</script>
@endpush
