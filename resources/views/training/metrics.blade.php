<x-layouts.app :title="$trainingRun->algorithm . ' Run #' . $trainingRun->id . ' — Metrics'">

    <x-ui.breadcrumb :items="[
        ['label' => 'Simulations',     'route' => route('simulations.index')],
        ['label' => $simulation->name, 'route' => route('simulations.show', $simulation)],
        ['label' => 'Training',        'route' => route('simulations.training.show', $simulation)],
        ['label' => $trainingRun->algorithm . ' Run #' . $trainingRun->id . ' Metrics'],
    ]"/>

    <x-ui.page-header
        :title="$trainingRun->algorithm . ' Run #' . $trainingRun->id . ' — Training & Evaluation Metrics'"
        description="Learning curve, evaluation performance, and train vs test comparison."/>

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-analytics.metric
            label="Train Mean Reward"
            :value="$trainingRun->train_mean_reward !== null ? round($trainingRun->train_mean_reward, 4) : '—'"
            sub="Avg of last 5 training checkpoints"
            color="primary"
            icon="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
        />
        <x-analytics.metric
            label="Eval Mean Reward"
            :value="$trainingRun->eval_mean_reward !== null ? round($trainingRun->eval_mean_reward, 4) : '—'"
            sub="Mean over 10 test episodes"
            color="emerald"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
        />
        <x-analytics.metric
            label="Eval Std Dev"
            :value="$trainingRun->eval_std_reward !== null ? '±' . round($trainingRun->eval_std_reward, 4) : '—'"
            sub="Reward variance across test episodes"
            color="amber"
            icon="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"
        />
        <x-analytics.metric
            label="Step Success Rate"
            :value="$trainingRun->eval_success_rate !== null ? round($trainingRun->eval_success_rate, 1) . '%' : '—'"
            sub="% steps with positive reward in eval"
            color="violet"
            icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
        />
    </div>

    {{-- Train vs Eval interpretation banner --}}
    @if($trainingRun->train_mean_reward !== null && $trainingRun->eval_mean_reward !== null)
    @php
        $gap        = round($trainingRun->train_mean_reward - $trainingRun->eval_mean_reward, 4);
        $absGap     = abs($gap);
        $isOverfit  = $gap > 0.5;
        $isUnderfit = $trainingRun->eval_mean_reward < -0.5;
        $isGood     = ! $isOverfit && ! $isUnderfit;
    @endphp
    <div class="mb-6 bg-slate-900 border rounded-xl px-6 py-4
                {{ $isGood ? 'border-emerald-500/20' : ($isOverfit ? 'border-amber-500/20' : 'border-red-500/20') }}">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $isGood ? 'bg-emerald-500/10' : ($isOverfit ? 'bg-amber-500/10' : 'bg-red-500/10') }}">
                <svg class="w-5 h-5 {{ $isGood ? 'text-emerald-400' : ($isOverfit ? 'text-amber-400' : 'text-red-400') }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isGood)
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    @endif
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold {{ $isGood ? 'text-emerald-400' : ($isOverfit ? 'text-amber-400' : 'text-red-400') }}">
                    @if($isGood) Policy generalizes well (Train ≈ Eval)
                    @elseif($isOverfit) Possible overfitting detected (Train &gt; Eval by {{ $absGap }})
                    @else Underfitting — agent needs more training
                    @endif
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    Train mean reward: <span class="font-mono text-slate-300">{{ $trainingRun->train_mean_reward }}</span>
                    &nbsp;|&nbsp;
                    Eval mean reward: <span class="font-mono text-slate-300">{{ $trainingRun->eval_mean_reward }}</span>
                    &nbsp;|&nbsp;
                    Gap: <span class="font-mono {{ $absGap > 0.5 ? 'text-amber-400' : 'text-slate-300' }}">{{ $gap }}</span>
                </p>
                <p class="text-xs text-slate-500 mt-1.5">
                    @if($isGood)
                        The agent's policy learned during training performs consistently on unseen task sequences.
                        This is the ideal outcome — training reward predicts evaluation reward accurately.
                    @elseif($isOverfit)
                        The agent performs better on seen training patterns than on novel evaluation episodes.
                        Try training with more timesteps or increasing environment randomness.
                    @else
                        Both training and evaluation rewards are negative, meaning the agent hasn't converged yet.
                        Increase timesteps (use Deep training mode) or run more training iterations.
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Charts --}}
    <div x-data="metricsCharts('{{ route('simulations.training.metrics.chart-data', [$simulation, $trainingRun]) }}')"
         x-init="load()"
         class="space-y-6">

        <div x-show="loading" class="flex justify-center py-16">
            <div class="flex items-center gap-3 text-slate-400">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-sm">Loading metrics…</span>
            </div>
        </div>

        <div x-show="!loading" x-cloak class="space-y-6">

            {{-- Row 1: Learning Curve + Train vs Eval --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2">
                    <x-charts.card
                        title="Learning Curve (Training Performance)"
                        description="Mean episode reward at each checkpoint during training. Shaded area = ±1 standard deviation. Rising curve = agent is learning."
                        height="h-80">
                        <canvas id="learningCurveChart"></canvas>
                    </x-charts.card>
                </div>

                <div>
                    <x-charts.card
                        title="Train vs Eval Reward"
                        description="Final training reward vs mean evaluation reward over 10 independent test episodes."
                        height="h-80">
                        <canvas id="trainEvalChart"></canvas>
                    </x-charts.card>
                </div>

            </div>

            {{-- Row 2: Eval Distribution --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <x-charts.card
                    title="Evaluation Episode Rewards (Test Performance)"
                    description="Total reward for each of the 10 independent evaluation episodes. Shows how consistent the policy is."
                    height="h-72">
                    <canvas id="evalDistChart"></canvas>
                </x-charts.card>

                {{-- Numeric Eval Stats --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-slate-300 mb-4">
                        Evaluation Statistics ({{ $trainingRun->eval_episodes ?? 10 }} Test Episodes)
                    </h3>
                    <div class="space-y-0">
                        @foreach([
                            ['Metric',                   'Value',                              'Note'],
                            ['Mean Reward (μ)',           $trainingRun->eval_mean_reward,       'Average total reward per episode'],
                            ['Std Deviation (σ)',         $trainingRun->eval_std_reward ? '±'.$trainingRun->eval_std_reward : null, 'Variance — lower = more consistent'],
                            ['Best Episode Reward',       $trainingRun->eval_max_reward,        'Maximum reward achieved'],
                            ['Worst Episode Reward',      $trainingRun->eval_min_reward,        'Minimum reward achieved'],
                            ['Step Success Rate',         $trainingRun->eval_success_rate ? round($trainingRun->eval_success_rate, 1).'%' : null, '% of steps with positive reward'],
                            ['Train Mean (last 5 chkpts)',$trainingRun->train_mean_reward,      'Training performance at end'],
                            ['Train−Eval Gap',
                                ($trainingRun->train_mean_reward !== null && $trainingRun->eval_mean_reward !== null)
                                    ? round($trainingRun->train_mean_reward - $trainingRun->eval_mean_reward, 4)
                                    : null,
                                'Closer to 0 = better generalization'],
                        ] as [$label, $value, $note])
                        @if($label !== 'Metric')
                        <div class="py-3 border-b border-slate-800 last:border-0">
                            <div class="flex justify-between items-start">
                                <span class="text-sm text-slate-400">{{ $label }}</span>
                                <span class="text-sm font-mono font-semibold text-slate-200 ml-4">
                                    {{ $value !== null ? $value : '—' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $note }}</p>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

            </div>

        </div>
    </div>

</x-layouts.app>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color       = '#94a3b8';
Chart.defaults.borderColor = '#1e293b';
Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.font.size   = 11;

function metricsCharts(dataUrl) {
    return {
        loading: true,

        async load() {
            try {
                const res  = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                this.loading = false;
                await this.$nextTick();
                this.buildLearningCurve(data.learning_curve);
                this.buildTrainEval(data.train_vs_eval);
                this.buildEvalDist(data.eval_distribution);
            } catch(e) {
                console.error(e);
                this.loading = false;
            }
        },

        // ── 1. Learning Curve with std band ───────────────────
        buildLearningCurve(d) {
            const ctx = document.getElementById('learningCurveChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.labels,
                    datasets: [
                        {
                            label:           'Mean Reward (training)',
                            data:            d.mean_reward,
                            borderColor:     '#0ea5e9',
                            backgroundColor: 'rgba(14,165,233,0.15)',
                            fill:            false,
                            tension:         0.4,
                            pointRadius:     3,
                            pointHoverRadius:6,
                            borderWidth:     2.5,
                            order:           2,
                        },
                        {
                            label:           'Upper bound (μ+σ)',
                            data:            d.upper_bound,
                            borderColor:     'rgba(14,165,233,0.2)',
                            backgroundColor: 'rgba(14,165,233,0.07)',
                            fill:            '+1',
                            tension:         0.4,
                            pointRadius:     0,
                            borderWidth:     1,
                            borderDash:      [4,4],
                            order:           1,
                        },
                        {
                            label:           'Lower bound (μ−σ)',
                            data:            d.lower_bound,
                            borderColor:     'rgba(14,165,233,0.2)',
                            backgroundColor: 'rgba(14,165,233,0.07)',
                            fill:            false,
                            tension:         0.4,
                            pointRadius:     0,
                            borderWidth:     1,
                            borderDash:      [4,4],
                            order:           1,
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, padding: 16,
                            filter: item => item.text !== 'Lower bound (μ−σ)' } },
                        tooltip: { callbacks: {
                            label: ctx => {
                                if (ctx.dataset.label.includes('bound')) return null;
                                return ` ${ctx.dataset.label}: ${ctx.parsed.y}`;
                            }
                        }},
                    },
                    scales: {
                        x: { grid: { color: '#1e293b' },
                             ticks: { color: '#64748b', maxTicksLimit: 10, maxRotation: 0 },
                             title: { display: true, text: 'Timesteps', color: '#64748b' } },
                        y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' },
                             title: { display: true, text: 'Mean Episode Reward', color: '#64748b' } },
                    },
                },
            });
        },

        // ── 2. Train vs Eval bar ──────────────────────────────
        buildTrainEval(d) {
            const ctx = document.getElementById('trainEvalChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels:   d.labels,
                    datasets: [{
                        label:           'Reward',
                        data:            d.values,
                        backgroundColor: d.colors,
                        borderColor:     d.colors.map(c => c.replace('0.8','1')),
                        borderWidth:     1,
                        borderRadius:    6,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` Reward: ${ctx.parsed.y}` } },
                    },
                    scales: {
                        x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                        y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' },
                             title: { display: true, text: 'Mean Reward', color: '#64748b' } },
                    },
                },
            });
        },

        // ── 3. Eval episode distribution ──────────────────────
        buildEvalDist(d) {
            const ctx = document.getElementById('evalDistChart');
            if (!ctx || !d.rewards.length) return;

            const labels = d.rewards.map((_, i) => `Ep ${i+1}`);
            const mean   = d.mean;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label:           'Episode Reward',
                            data:            d.rewards,
                            backgroundColor: d.rewards.map(r =>
                                r >= mean
                                    ? 'rgba(16,185,129,0.75)'
                                    : 'rgba(239,68,68,0.65)'
                            ),
                            borderColor: d.rewards.map(r =>
                                r >= mean ? '#10b981' : '#ef4444'
                            ),
                            borderWidth:  1,
                            borderRadius: 3,
                        },
                        {
                            label:       `Mean (${mean})`,
                            data:        new Array(d.rewards.length).fill(mean),
                            type:        'line',
                            borderColor: '#f59e0b',
                            borderWidth: 2,
                            borderDash:  [6, 3],
                            pointRadius: 0,
                            fill:        false,
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
                        tooltip: { callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`
                        }},
                    },
                    scales: {
                        x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                        y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' },
                             title: { display: true, text: 'Total Episode Reward', color: '#64748b' } },
                    },
                },
            });
        },
    }
}
</script>
@endpush
