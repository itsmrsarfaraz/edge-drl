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
        <x-ui.stat-card label="Total Tasks"    :value="$stats['total_tasks']"     color="primary" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        <x-ui.stat-card label="Completed"      :value="$stats['completed_tasks']" color="emerald" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <x-ui.stat-card label="Failed"         :value="$stats['failed_tasks']"    color="red"     icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <x-ui.stat-card label="Training Runs"  :value="$stats['training_runs']"   color="violet"  icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Config + Danger --}}
        <div class="space-y-5">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-1">Configuration</h2>
                <x-simulations.info-row label="Algorithm"   :value="$simulation->algorithm"       badge color="violet"/>
                <x-simulations.info-row label="Edge Nodes"  :value="$simulation->num_edge_nodes"/>
                <x-simulations.info-row label="IoT Devices" :value="$simulation->num_iot_devices"/>
                <x-simulations.info-row label="Task Count"  :value="$simulation->num_tasks"/>
                <x-simulations.info-row label="Status"      :value="ucfirst($simulation->status)" badge :color="$simulation->status_color"/>
                <x-simulations.info-row label="Created"     :value="$simulation->created_at->format('M d, Y')"/>
            </div>

            {{-- Run Training Button --}}
            <a href="{{ route('simulations.training.show', $simulation) }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 px-4 mt-2
                      bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold
                      rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Run Simulation
            </a>

            {{-- Analytics Link (only if results exist) --}}
            @if($simulation->latestResult)
            <a href="{{ route('simulations.analytics.show', $simulation) }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 px-4 mt-2
                      bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium
                      rounded-xl transition-colors">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                View Analytics
            </a>
            @endif

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

        {{-- Right: Modules --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Edge Nodes Preview --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Edge Nodes</h2>
                    <a href="{{ route('simulations.nodes.index', $simulation) }}"
                       class="text-xs text-primary-400 hover:text-primary-300 transition-colors">
                        View all {{ $simulation->edgeNodes->count() }} →
                    </a>
                </div>

                @if($simulation->edgeNodes->isEmpty())
                    <p class="text-sm text-slate-500 py-4 text-center">No edge nodes provisioned.</p>
                @else
                    <div class="space-y-2">
                        @foreach($simulation->edgeNodes->take(4) as $node)
                        <div class="bg-slate-800/60 rounded-lg px-4 py-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        {{ $node->status === 'idle' ? 'bg-emerald-400' : ($node->status === 'busy' ? 'bg-amber-400' : 'bg-red-400') }}">
                                    </span>
                                    <a href="{{ route('simulations.nodes.show', [$simulation, $node]) }}"
                                       class="text-sm font-medium text-slate-200 hover:text-primary-400 transition-colors">
                                        {{ $node->name }}
                                    </a>
                                </div>
                                <x-ui.badge :color="$node->status_color">{{ $node->status }}</x-ui.badge>
                            </div>
                            <x-ui.resource-bar
                                label="CPU"
                                :used="$node->cpu_used"
                                :total="$node->cpu_capacity"
                                unit="%"
                            />
                        </div>
                        @endforeach

                        @if($simulation->edgeNodes->count() > 4)
                            <p class="text-xs text-center text-slate-500 pt-1">
                                +{{ $simulation->edgeNodes->count() - 4 }} more —
                                <a href="{{ route('simulations.nodes.index', $simulation) }}"
                                   class="text-primary-400 hover:underline">view all</a>
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Tasks Preview --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">IoT Tasks</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-500">{{ $stats['total_tasks'] }} generated</span>
                        <a href="{{ route('simulations.tasks.index', $simulation) }}"
                           class="text-xs text-primary-400 hover:text-primary-300 transition-colors">
                            View all →
                        </a>
                    </div>
                </div>
                @if($stats['total_tasks'] === 0)
                    <div class="flex items-center justify-between py-4">
                        <p class="text-sm text-slate-500">No tasks generated yet.</p>
                        <form method="POST" action="{{ route('simulations.tasks.generate', $simulation) }}">
                            @csrf
                            <button type="submit"
                                    onclick="this.disabled=true; this.innerText='Generating…'; this.form.submit();"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-500
                                        text-white text-xs font-medium rounded-lg transition-colors">
                                Generate Tasks
                            </button>
                        </form>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([
                            ['label' => 'Total',     'value' => $stats['total_tasks'],     'color' => 'text-slate-200'],
                            ['label' => 'Completed', 'value' => $stats['completed_tasks'], 'color' => 'text-emerald-400'],
                            ['label' => 'Pending',   'value' => $stats['total_tasks'] - $stats['completed_tasks'] - $stats['failed_tasks'], 'color' => 'text-slate-400'],
                            ['label' => 'Failed',    'value' => $stats['failed_tasks'],    'color' => 'text-red-400'],
                        ] as $m)
                        <div class="bg-slate-800/60 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold {{ $m['color'] }}">{{ $m['value'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $m['label'] }}</p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Training Runs Preview --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Training Runs</h2>
                    <a href="{{ route('simulations.training.show', $simulation) }}"
                       class="text-xs text-primary-400 hover:text-primary-300 transition-colors">
                        Train →
                    </a>
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

            {{-- Latest Results --}}
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
