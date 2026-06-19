<x-layouts.app :title="$simulation->name . ' — Training'">
    <x-ui.breadcrumb :items="[
        ['label' => 'Simulations',  'route' => route('simulations.index')],
        ['label' => $simulation->name, 'route' => route('simulations.show', $simulation)],
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

    {{-- Alpine.js Training Controller --}}
    <div x-data="trainingManager({{ $simulation->id }}, '{{ csrf_token() }}')" x-init="init()">

        {{-- AI Engine Warning --}}
        @if(! $aiOnline)
        <x-ui.alert type="error" class="mb-5">
            ⚠️ AI Engine is offline. Open a second terminal and run:
            <code class="ml-2 bg-red-500/10 px-2 py-0.5 rounded font-mono text-xs">
                cd ~/projects/edge-drl/python && source venv/bin/activate && bash api/start.sh
            </code>
        </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Controls --}}
            <div class="space-y-5">

                {{-- Simulation Config Card --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">
                        Configuration
                    </h2>
                    <x-simulations.info-row label="Algorithm"  :value="$simulation->algorithm" badge color="violet"/>
                    <x-simulations.info-row label="Edge Nodes" :value="$simulation->num_edge_nodes"/>
                    <x-simulations.info-row label="Tasks"      :value="$simulation->tasks()->count()"/>
                    <x-simulations.info-row label="Status"     :value="ucfirst($simulation->status)" badge :color="$simulation->status_color"/>
                </div>

                {{-- Run Button --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">
                        Training Controls
                    </h2>

                    <button
                        @click="startTraining()"
                        :disabled="isRunning || ! aiOnline"
                        :class="isRunning || ! aiOnline
                            ? 'opacity-50 cursor-not-allowed bg-slate-700 text-slate-400'
                            : 'bg-primary-600 hover:bg-primary-500 text-white'"
                        class="w-full py-3 px-4 font-semibold text-sm rounded-xl transition-all flex items-center justify-center gap-2">

                        <template x-if="! isRunning">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <template x-if="isRunning">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </template>

                        <span x-text="isRunning ? 'Training in Progress…' : 'Run Simulation'"></span>
                    </button>

                    <p class="text-xs text-slate-500 mt-3 text-center">
                        Training runs on CPU and may take 1–3 minutes depending on timesteps.
                    </p>

                    {{-- Error display --}}
                    <div x-show="errorMsg" x-cloak class="mt-3">
                        <x-ui.alert type="error" x-text="errorMsg"></x-ui.alert>
                    </div>
                </div>

                {{-- Past Training Runs --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">
                        Past Runs
                    </h2>
                    @forelse($simulation->trainingRuns as $run)
                    <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-slate-300">
                                {{ $run->algorithm }} #{{ $run->id }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $run->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($run->mean_reward !== null)
                                <span class="text-xs text-slate-400">
                                    R̄ {{ round($run->mean_reward, 3) }}
                                </span>
                            @endif
                            <x-ui.badge color="{{ $run->status === 'completed' ? 'emerald' : ($run->status === 'running' ? 'primary' : ($run->status === 'failed' ? 'red' : 'slate')) }}">
                                {{ $run->status }}
                            </x-ui.badge>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 text-center py-4">No training runs yet.</p>
                    @endforelse
                </div>

            </div>

            {{-- Right: Progress + Results --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Live Progress --}}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6" x-show="isRunning || progress > 0">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                            Training Progress
                        </h2>
                        <span class="text-xs font-mono text-slate-400" x-text="algorithm + ' — ' + totalTimesteps + ' timesteps'"></span>
                    </div>

                    {{-- Progress Bar --}}
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

                    {{-- Live Metrics --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([
                            ['key' => 'elapsedTime',  'label' => 'Elapsed',      'suffix' => ''],
                            ['key' => 'progress',      'label' => 'Progress',     'suffix' => '%'],
                            ['key' => 'pollCount',     'label' => 'Status Polls', 'suffix' => ''],
                            ['key' => 'trainingRunId', 'label' => 'Run ID',       'suffix' => ''],
                        ] as $m)
                        <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                            <p class="text-lg font-bold text-slate-200"
                               x-text="{{ $m['key'] }} + '{{ $m['suffix'] }}'"></p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $m['label'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Idle State --}}
                <div x-show="! isRunning && progress === 0" class="bg-slate-900 border border-slate-800 rounded-xl p-10">
                    <x-ui.empty-state
                        title="Ready to Train"
                        description="Click 'Run Simulation' to start the DRL training engine. The agent will learn to allocate IoT tasks across your edge nodes."
                        icon="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </div>

                {{-- Completed Result --}}
                <div x-show="result" x-cloak class="bg-slate-900 border border-emerald-500/20 rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-emerald-400">Training Complete</h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
                        <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-slate-100"
                               x-text="result ? result.algorithm : ''"></p>
                            <p class="text-xs text-slate-500 mt-1">Algorithm</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-emerald-400"
                               x-text="result ? (result.mean_reward ? parseFloat(result.mean_reward).toFixed(3) : '—') : ''"></p>
                            <p class="text-xs text-slate-500 mt-1">Mean Reward</p>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-primary-400"
                               x-text="result ? (result.final_reward ? parseFloat(result.final_reward).toFixed(3) : '—') : ''"></p>
                            <p class="text-xs text-slate-500 mt-1">Final Reward</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a :href="'/simulations/{{ $simulation->id }}/training'"
                           onclick="location.reload()"
                           class="flex-1 py-2.5 text-center bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">
                            View Updated Page
                        </a>
                        <a href="{{ route('simulations.show', $simulation) }}"
                           class="flex-1 py-2.5 text-center bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-lg transition-colors">
                            Go to Simulation
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

@push('scripts')
<script>
function trainingManager(simulationId, csrfToken) {
    return {
        simulationId:   simulationId,
        isRunning:      false,
        aiOnline:       {{ $aiOnline ? 'true' : 'false' }},
        progress:       0,
        algorithm:      '{{ $simulation->algorithm }}',
        totalTimesteps: 0,
        trainingRunId:  null,
        pollInterval:   null,
        pollCount:      0,
        elapsedTime:    '0s',
        startedAt:      null,
        result:         null,
        errorMsg:       '',

        init() {
            // Check if a run is already in progress
            @foreach($simulation->trainingRuns as $run)
                @if($run->status === 'running')
                    this.isRunning      = true;
                    this.trainingRunId  = {{ $run->id }};
                    this.totalTimesteps = {{ $run->total_timesteps }};
                    this.startedAt      = new Date('{{ $run->started_at?->toISOString() }}');
                    this.beginPolling();
                @endif
            @endforeach
        },

        async startTraining() {
            this.errorMsg  = '';
            this.result    = null;
            this.progress  = 0;
            this.pollCount = 0;
            this.isRunning = true;
            this.startedAt = new Date();

            try {
                const res = await fetch(`/simulations/${this.simulationId}/training/start`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept':       'application/json',
                    },
                });

                const data = await res.json();

                if (! res.ok) {
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

        beginPolling() {
            this.pollInterval = setInterval(() => this.pollStatus(), 3000);
            this.beginTimer();
        },

        async pollStatus() {
            if (! this.trainingRunId) return;
            this.pollCount++;

            try {
                const res  = await fetch(
                    `/simulations/${this.simulationId}/training/${this.trainingRunId}/status`,
                    { headers: { 'Accept': 'application/json' } }
                );
                const data = await res.json();

                this.progress = data.progress || 0;

                if (data.status === 'completed') {
                    this.progress  = 100;
                    this.isRunning = false;
                    this.result    = data.result;
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

        beginTimer() {
            setInterval(() => {
                if (! this.startedAt || ! this.isRunning) return;
                const secs = Math.floor((new Date() - this.startedAt) / 1000);
                if (secs < 60) this.elapsedTime = secs + 's';
                else           this.elapsedTime = Math.floor(secs / 60) + 'm ' + (secs % 60) + 's';
            }, 1000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },
    }
}
</script>
@endpush

</x-layouts.app>
