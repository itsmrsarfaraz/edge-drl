<x-layouts.app :title="$simulation->name . ' — Training'">

    <x-ui.breadcrumb :items="[
        ['label' => 'Simulations',      'route' => route('simulations.index')],
        ['label' => $simulation->name,  'route' => route('simulations.show', $simulation)],
        ['label' => 'Training'],
    ]"/>

    <x-ui.page-header
        :title="$simulation->name"
        description="Run DRL training and monitor progress in real time.">
        <x-slot:action>
            <a href="{{ route('simulations.show', $simulation) }}"
               class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                ← Simulation
            </a>
        </x-slot:action>
    </x-ui.page-header>

    @if(! $aiOnline)
    <x-ui.alert type="error" class="mb-5">
        ⚠️ AI Engine is offline. Open a second terminal and run:
        <code class="ml-2 bg-red-500/10 px-2 py-0.5 rounded font-mono text-xs">
            cd ~/projects/edge-drl/python &amp;&amp; source venv/bin/activate &amp;&amp; bash api/start.sh
        </code>
    </x-ui.alert>
    @endif

    {{--
        Pass all PHP values into one data-* attribute on the root div.
        This completely separates PHP from JS and fixes all VS Code errors.
    --}}
    <div id="training-root"
         data-simulation-id="{{ $simulation->id }}"
         data-algorithm="{{ $simulation->algorithm }}"
         data-ai-online="{{ $aiOnline ? '1' : '0' }}"
         data-has-trained="{{ $simulation->trainingRuns()->where('status','completed')->exists() ? '1' : '0' }}"
         data-active-run-id="{{ $simulation->trainingRuns()->where('status','running')->first()?->id ?? '' }}"
         data-active-run-timesteps="{{ $simulation->trainingRuns()->where('status','running')->first()?->total_timesteps ?? '0' }}"
         data-active-run-started="{{ $simulation->trainingRuns()->where('status','running')->first()?->started_at?->toISOString() ?? '' }}"
         data-csrf="{{ csrf_token() }}"
         x-data="trainingPage()"
         x-init="init()">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── LEFT COLUMN ──────────────────────────────── --}}
            <div class="space-y-5">

                {{-- Simulation Config --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">
                        Configuration
                    </h2>
                    <x-simulations.info-row label="Algorithm"  :value="$simulation->algorithm" badge color="violet"/>
                    <x-simulations.info-row label="Edge Nodes" :value="$simulation->num_edge_nodes"/>
                    <x-simulations.info-row label="Tasks"      :value="$simulation->tasks()->count()"/>
                    <x-simulations.info-row label="Status"     :value="ucfirst($simulation->status)" badge :color="$simulation->status_color"/>
                </div>

                {{-- Training Controls --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">
                        Training Controls
                    </h2>

                    {{-- Timestep Selector --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">
                            Training Depth
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="ts" value="10000" x-model="selectedTimesteps" class="sr-only">
                                <div :class="selectedTimesteps === '10000'
                                        ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                                        : 'border-slate-700 text-slate-400 hover:border-slate-600'"
                                     class="border rounded-lg p-2.5 text-center transition-all">
                                    <p class="text-xs font-bold">Quick</p>
                                    <p class="text-xs opacity-70">~1 min</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="ts" value="25000" x-model="selectedTimesteps" class="sr-only">
                                <div :class="selectedTimesteps === '25000'
                                        ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                                        : 'border-slate-700 text-slate-400 hover:border-slate-600'"
                                     class="border rounded-lg p-2.5 text-center transition-all">
                                    <p class="text-xs font-bold">Standard</p>
                                    <p class="text-xs opacity-70">~3 min</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="ts" value="50000" x-model="selectedTimesteps" class="sr-only">
                                <div :class="selectedTimesteps === '50000'
                                        ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                                        : 'border-slate-700 text-slate-400 hover:border-slate-600'"
                                     class="border rounded-lg p-2.5 text-center transition-all">
                                    <p class="text-xs font-bold">Deep</p>
                                    <p class="text-xs opacity-70">~7 min</p>
                                </div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-600 mt-1.5 text-center">
                            More timesteps = better agent but longer wait
                        </p>
                    </div>

                    {{-- Run Button --}}
                    <button
                        @click="startTraining()"
                        :disabled="isRunning || !aiOnline"
                        :class="isRunning || !aiOnline
                            ? 'opacity-50 cursor-not-allowed bg-slate-700 text-slate-400'
                            : 'bg-primary-600 hover:bg-primary-500 text-white'"
                        class="w-full py-3 px-4 font-semibold text-sm rounded-xl transition-all
                               flex items-center justify-center gap-2">
                        <svg x-show="!isRunning" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <svg x-show="isRunning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="isRunning ? 'Training in Progress…' : 'Run Simulation'"></span>
                    </button>

                    <p class="text-xs text-slate-500 mt-3 text-center">
                        Training runs on CPU. Duration depends on depth selected.
                    </p>

                    <div x-show="errorMsg" x-cloak class="mt-3">
                        <div class="border rounded-lg px-4 py-3 text-sm bg-red-500/10 border-red-500/20 text-red-400"
                             x-text="errorMsg"></div>
                    </div>

                    {{-- ── INFERENCE BUTTON ────────────────────────── --}}
                    <div x-show="hasTrained" x-cloak class="mt-4 pt-4 border-t border-slate-800">
                        <button
                            @click="runInfer()"
                            :disabled="inferring"
                            :class="inferring
                                ? 'opacity-50 cursor-not-allowed bg-emerald-800'
                                : 'bg-emerald-600 hover:bg-emerald-500'"
                            class="w-full py-2.5 px-4 text-white text-sm font-semibold
                                   rounded-xl transition-all flex items-center justify-center gap-2">
                            <svg x-show="!inferring" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <svg x-show="inferring" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span x-text="inferring ? 'Allocating…' : 'Run Inference (Use Trained Model)'"></span>
                        </button>
                        <p class="text-xs text-slate-500 mt-2 text-center">
                            Re-allocates all tasks using your trained model
                        </p>

                        {{-- Inference error --}}
                        <div x-show="inferError" x-cloak class="mt-3">
                            <div class="border rounded-lg px-4 py-3 text-sm bg-red-500/10 border-red-500/20 text-red-400"
                                 x-text="inferError"></div>
                        </div>

                        {{-- Inference Results --}}
                        <div x-show="allocations.length > 0" x-cloak class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Results (<span x-text="allocations.length"></span> tasks)
                                </p>
                                <div class="flex gap-3 text-xs">
                                    <span class="text-emerald-400 font-semibold"
                                          x-text="allocations.filter(a => a.status === 'completed').length + ' allocated'"></span>
                                    <span class="text-amber-400 font-semibold"
                                          x-text="allocations.filter(a => a.status === 'delayed').length + ' delayed'"></span>
                                </div>
                            </div>

                            <div class="max-h-52 overflow-y-auto space-y-1 rounded-lg border border-slate-800 p-2 bg-slate-950">
                                <template x-for="(a, i) in allocations" :key="i">
                                    <div class="flex items-center justify-between rounded px-2.5 py-1.5 text-xs hover:bg-slate-800/60 transition-colors">
                                        <span class="font-mono text-slate-400 w-24" x-text="a.task_label"></span>
                                        <span class="font-medium w-28 text-center"
                                              :class="a.status === 'completed' ? 'text-emerald-400' : 'text-amber-400'"
                                              x-text="a.node_assigned"></span>
                                        <span class="text-slate-500 font-mono text-right w-16"
                                              x-text="a.latency_ms.toFixed(0) + ' ms'"></span>
                                        <span class="font-mono text-right w-14"
                                              :class="a.reward >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                              x-text="'R:' + a.reward.toFixed(2)"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <div class="bg-slate-800/60 rounded-lg p-2.5 text-center">
                                    <p class="text-base font-bold text-emerald-400"
                                       x-text="allocations.filter(a => a.status === 'completed').length"></p>
                                    <p class="text-xs text-slate-500 mt-0.5">Allocated</p>
                                </div>
                                <div class="bg-slate-800/60 rounded-lg p-2.5 text-center">
                                    <p class="text-base font-bold text-amber-400"
                                       x-text="allocations.filter(a => a.status === 'delayed').length"></p>
                                    <p class="text-xs text-slate-500 mt-0.5">Delayed</p>
                                </div>
                                <div class="bg-slate-800/60 rounded-lg p-2.5 text-center">
                                    <p class="text-base font-bold text-primary-400"
                                       x-text="allocations.length > 0
                                           ? (allocations.reduce((s,a) => s + a.latency_ms, 0) / allocations.length).toFixed(0) + 'ms'
                                           : '—'"></p>
                                    <p class="text-xs text-slate-500 mt-0.5">Avg Latency</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- ── END INFERENCE ───────────────────────────── --}}

                </div>

                {{-- Past Runs --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">
                        Past Runs
                    </h2>
                    @forelse($simulation->trainingRuns()->latest()->take(10)->get() as $run)
                    <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-slate-300">
                                {{ $run->algorithm }} #{{ $run->id }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $run->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            @if($run->mean_reward !== null)
                                <span class="text-xs text-slate-400 font-mono">
                                    R̄ {{ round($run->mean_reward, 3) }}
                                </span>
                            @endif
                            <x-ui.badge color="{{ match($run->status) {
                                'completed' => 'emerald',
                                'running'   => 'primary',
                                'failed'    => 'red',
                                default     => 'slate'
                            } }}">{{ $run->status }}</x-ui.badge>
                            @if($run->status === 'completed')
                            <a href="{{ route('simulations.training.metrics.show', [$simulation, $run]) }}"
                               class="text-xs text-primary-400 hover:text-primary-300 transition-colors whitespace-nowrap">
                                Metrics →
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 text-center py-4">No training runs yet.</p>
                    @endforelse
                </div>

            </div>
            {{-- ── END LEFT COLUMN ──────────────────────────── --}}

            {{-- ── RIGHT COLUMN ─────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Live Progress (shown while running or after first run) --}}
                <div x-show="isRunning || progress > 0" x-cloak
                     class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                            Training Progress
                        </h2>
                        <span class="text-xs font-mono text-slate-400"
                              x-text="algorithm + ' — ' + Number(totalTimesteps).toLocaleString() + ' timesteps'"></span>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between text-xs text-slate-400 mb-2">
                            <span>Timesteps Completed</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full transition-all duration-700 ease-out"
                                 :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                            <p class="text-lg font-bold text-slate-200" x-text="elapsedTime"></p>
                            <p class="text-xs text-slate-500 mt-0.5">Elapsed</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                            <p class="text-lg font-bold text-slate-200" x-text="progress + '%'"></p>
                            <p class="text-xs text-slate-500 mt-0.5">Progress</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                            <p class="text-lg font-bold text-slate-200" x-text="pollCount"></p>
                            <p class="text-xs text-slate-500 mt-0.5">Status Polls</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                            <p class="text-lg font-bold text-slate-200" x-text="trainingRunId || '—'"></p>
                            <p class="text-xs text-slate-500 mt-0.5">Run ID</p>
                        </div>
                    </div>
                </div>

                {{-- Idle State --}}
                <div x-show="!isRunning && progress === 0"
                     class="bg-slate-900 border border-slate-800 rounded-xl p-10">
                    <x-ui.empty-state
                        title="Ready to Train"
                        description="Click 'Run Simulation' to start the DRL training engine. The agent will learn to allocate IoT tasks across your edge nodes."
                        icon="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </div>

                {{-- Completed Result Card --}}
                <div x-show="result" x-cloak
                     class="bg-slate-900 border border-emerald-500/20 rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-emerald-400">Training Complete</h2>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-5">
                        <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-slate-100" x-text="result ? result.algorithm : ''"></p>
                            <p class="text-xs text-slate-500 mt-1">Algorithm</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-emerald-400"
                               x-text="result && result.mean_reward !== null
                                   ? parseFloat(result.mean_reward).toFixed(3)
                                   : '—'"></p>
                            <p class="text-xs text-slate-500 mt-1">Mean Reward</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-primary-400"
                               x-text="result && result.final_reward !== null
                                   ? parseFloat(result.final_reward).toFixed(3)
                                   : '—'"></p>
                            <p class="text-xs text-slate-500 mt-1">Final Reward</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button onclick="location.reload()"
                                class="flex-1 py-2.5 text-center bg-slate-800 hover:bg-slate-700
                                       text-slate-300 text-sm font-medium rounded-lg transition-colors">
                            Refresh Page
                        </button>
                        <a href="{{ route('simulations.show', $simulation) }}"
                           class="flex-1 py-2.5 text-center bg-emerald-600 hover:bg-emerald-500
                                  text-white text-sm font-medium rounded-lg transition-colors">
                            Go to Simulation
                        </a>
                    </div>
                </div>

            </div>
            {{-- ── END RIGHT COLUMN ─────────────────────────── --}}

        </div>
    </div>

@push('scripts')
<script>
/**
 * Training Page — Alpine Component
 * All PHP values are read from data-* attributes on #training-root.
 * Zero Blade directives inside this script block.
 */
function trainingPage() {
    const root = document.getElementById('training-root');

    return {
        // ── Config from PHP (via data-* attrs) ────────────────
        simulationId:      parseInt(root.dataset.simulationId),
        algorithm:         root.dataset.algorithm,
        aiOnline:          root.dataset.aiOnline === '1',
        hasTrained:        root.dataset.hasTrained === '1',
        csrf:              root.dataset.csrf,

        // ── Training state ─────────────────────────────────────
        isRunning:         false,
        progress:          0,
        totalTimesteps:    0,
        trainingRunId:     null,
        pollInterval:      null,
        pollCount:         0,
        elapsedTime:       '0s',
        startedAt:         null,
        result:            null,
        errorMsg:          '',
        selectedTimesteps: '10000',

        // ── Inference state ────────────────────────────────────
        inferring:         false,
        inferError:        '',
        allocations:       [],

        // ── Init ───────────────────────────────────────────────
        init() {
            const activeRunId         = root.dataset.activeRunId;
            const activeRunTimesteps  = root.dataset.activeRunTimesteps;
            const activeRunStarted    = root.dataset.activeRunStarted;

            if (activeRunId) {
                this.isRunning      = true;
                this.trainingRunId  = parseInt(activeRunId);
                this.totalTimesteps = parseInt(activeRunTimesteps) || 0;
                this.startedAt      = activeRunStarted ? new Date(activeRunStarted) : new Date();
                this.beginPolling();
            }
        },

        // ── Start Training ─────────────────────────────────────
        async startTraining() {
            this.errorMsg    = '';
            this.result      = null;
            this.progress    = 0;
            this.pollCount   = 0;
            this.allocations = [];
            this.inferError  = '';
            this.isRunning   = true;
            this.startedAt   = new Date();

            try {
                const res = await fetch('/simulations/' + this.simulationId + '/training/start', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ timesteps: this.selectedTimesteps }),
                });

                const data = await res.json();

                if (!res.ok) {
                    this.errorMsg  = data.error || 'Training failed to start.';
                    this.isRunning = false;
                    return;
                }

                this.trainingRunId  = data.training_run_id;
                this.totalTimesteps = data.total_timesteps;
                this.algorithm      = data.algorithm;
                this.beginPolling();

            } catch (err) {
                this.errorMsg  = 'Network error: ' + err.message;
                this.isRunning = false;
            }
        },

        // ── Polling ────────────────────────────────────────────
        beginPolling() {
            this.startTimer();
            this.pollInterval = setInterval(() => this.pollStatus(), 3000);
        },

        async pollStatus() {
            if (!this.trainingRunId) return;
            this.pollCount++;

            try {
                const res  = await fetch(
                    '/simulations/' + this.simulationId + '/training/' + this.trainingRunId + '/status',
                    { headers: { 'Accept': 'application/json' } }
                );
                const data = await res.json();

                this.progress = data.progress || 0;

                if (data.status === 'completed') {
                    this.progress    = 100;
                    this.isRunning   = false;
                    this.result      = data.result;
                    this.hasTrained  = true;   // show inference button immediately
                    this.stopPolling();
                } else if (data.status === 'failed') {
                    this.isRunning = false;
                    this.errorMsg  = data.error || 'Training failed.';
                    this.stopPolling();
                }

            } catch (err) {
                console.error('Poll error:', err);
            }
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },

        // ── Timer ──────────────────────────────────────────────
        startTimer() {
            const tick = () => {
                if (!this.startedAt || !this.isRunning) return;
                const secs = Math.floor((new Date() - this.startedAt) / 1000);
                this.elapsedTime = secs < 60
                    ? secs + 's'
                    : Math.floor(secs / 60) + 'm ' + (secs % 60) + 's';
            };
            setInterval(tick, 1000);
        },

        // ── Inference ──────────────────────────────────────────
        async runInfer() {
            this.inferring   = true;
            this.inferError  = '';
            this.allocations = [];

            try {
                const res = await fetch('/simulations/' + this.simulationId + '/infer', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept':       'application/json',
                    },
                });
                const data = await res.json();

                if (!res.ok) {
                    this.inferError = data.error || 'Inference failed.';
                } else {
                    this.allocations = data.allocations || [];
                }
            } catch (err) {
                this.inferError = 'Network error: ' + err.message;
            } finally {
                this.inferring = false;
            }
        },
    };
}
</script>
@endpush

</x-layouts.app>
