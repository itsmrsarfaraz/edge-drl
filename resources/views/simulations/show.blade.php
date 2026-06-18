<x-layouts.app title="{{ $simulation->name }}">
    <x-ui.page-header
        :title="$simulation->name"
        :description="$simulation->description ?? 'No description provided.'">
        <x-slot:action>
            <div class="flex items-center gap-3">
                <x-ui.badge :color="$simulation->status_color" class="text-sm px-3 py-1">
                    {{ ucfirst($simulation->status) }}
                </x-ui.badge>
                <a href="{{ route('simulations.index') }}"
                   class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                    ← Back
                </a>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert class="mb-5">{{ session('success') }}</x-ui.alert>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card
            label="Total Tasks"
            :value="$stats['total_tasks']"
            color="primary"
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
        <x-ui.stat-card
            label="Completed"
            :value="$stats['completed_tasks']"
            color="emerald"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
        />
        <x-ui.stat-card
            label="Failed"
            :value="$stats['failed_tasks']"
            color="red"
            icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
        />
        <x-ui.stat-card
            label="Training Runs"
            :value="$stats['training_runs']"
            color="violet"
            icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Simulation Config --}}
        <div class="space-y-5">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-1">Configuration</h2>
                <x-simulations.info-row label="Algorithm"    :value="$simulation->algorithm"       :badge="true" color="violet"/>
                <x-simulations.info-row label="Edge Nodes"   :value="$simulation->num_edge_nodes"/>
                <x-simulations.info-row label="IoT Devices"  :value="$simulation->num_iot_devices"/>
                <x-simulations.info-row label="Task Count"   :value="$simulation->num_tasks"/>
                <x-simulations.info-row label="Status"       :value="ucfirst($simulation->status)" :badge="true" :color="$simulation->status_color"/>
                <x-simulations.info-row label="Created"      :value="$simulation->created_at->format('M d, Y')"/>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-slate-900 border border-red-500/20 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-red-400 uppercase tracking-wider mb-3">Danger Zone</h2>
                <form method="POST" action="{{ route('simulations.destroy', $simulation) }}"
                      onsubmit="return confirm('Permanently delete this simulation and all its data?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2 px-4 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 text-sm font-medium rounded-lg transition-colors">
                        Delete Simulation
                    </button>
                </form>
            </div>
        </div>

        {{-- Right: Modules Status --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Edge Nodes Preview --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Edge Nodes</h2>
                    <span class="text-xs text-slate-500">{{ $simulation->edgeNodes->count() }} / {{ $simulation->num_edge_nodes }} provisioned</span>
                </div>
                @if($simulation->edgeNodes->isEmpty())
                    <p class="text-sm text-slate-500 py-4 text-center">
                        Edge nodes will be generated when simulation runs.
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach($simulation->edgeNodes->take(5) as $node)
                        <div class="flex items-center justify-between bg-slate-800/60 rounded-lg px-3 py-2">
                            <span class="text-sm text-slate-300">{{ $node->name }}</span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">CPU: {{ $node->cpu_usage_percent }}%</span>
                                <x-ui.badge :color="$node->status_color">{{ $node->status }}</x-ui.badge>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Training Runs Preview --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Training Runs</h2>
                </div>
                @if($simulation->trainingRuns->isEmpty())
                    <p class="text-sm text-slate-500 py-4 text-center">
                        No training runs yet. Run the simulation to start training.
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach($simulation->trainingRuns->take(5) as $run)
                        <div class="flex items-center justify-between bg-slate-800/60 rounded-lg px-3 py-2">
                            <span class="text-sm text-slate-300">{{ $run->algorithm }} Run #{{ $run->id }}</span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ $run->progress_percent }}%</span>
                                <x-ui.badge color="{{ $run->status === 'completed' ? 'emerald' : ($run->status === 'running' ? 'primary' : 'slate') }}">
                                    {{ $run->status }}
                                </x-ui.badge>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Latest Results Preview --}}
            @if($simulation->latestResult)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">Latest Results</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([
                        ['label' => 'Avg Latency',  'value' => round($simulation->latestResult->avg_latency ?? 0, 1) . ' ms'],
                        ['label' => 'CPU Util.',     'value' => round($simulation->latestResult->avg_cpu_utilization ?? 0, 1) . '%'],
                        ['label' => 'Success Rate',  'value' => round($simulation->latestResult->task_success_rate ?? 0, 1) . '%'],
                        ['label' => 'Throughput',    'value' => round($simulation->latestResult->throughput ?? 0, 2) . ' t/s'],
                    ] as $metric)
                    <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                        <p class="text-lg font-bold text-slate-100">{{ $metric['value'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $metric['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-layouts.app>
