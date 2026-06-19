<x-layouts.app :title="$simulation->name . ' — Report'">

    <x-ui.page-header
        :title="$simulation->name"
        description="Simulation report summary. Download as PDF for your FYP defense.">
        <x-slot:action>
            <div class="flex items-center gap-3">
                <a href="{{ route('simulations.reports.download', $simulation) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-500
                          text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
                <a href="{{ route('simulations.show', $simulation) }}"
                   class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                    ← Simulation
                </a>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    @if(! $latestResult)
        <x-ui.empty-state
            title="No report data yet"
            description="Run training first to generate a report."
            icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            action-label="Run Training"
            :action-route="route('simulations.training.show', $simulation)"
        />
    @else

    {{-- Report Preview Card --}}
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header Preview --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-primary-600 to-violet-600 px-8 py-6">
                <p class="text-xs font-semibold text-primary-200 uppercase tracking-widest mb-1">
                    Final Year Project Report
                </p>
                <h1 class="text-2xl font-bold text-white mb-1">
                    Resource Allocation in Edge Computing using DRL
                </h1>
                <p class="text-primary-200 text-sm">{{ $simulation->name }}</p>
            </div>
            <div class="grid grid-cols-3 divide-x divide-slate-800 bg-slate-800/40">
                @foreach([
                    ['label' => 'Generated',  'value' => now()->format('M d, Y')],
                    ['label' => 'Algorithm',  'value' => $simulation->algorithm],
                    ['label' => 'Simulation', 'value' => '#' . $simulation->id],
                ] as $m)
                <div class="px-6 py-3 text-center">
                    <p class="text-xs text-slate-400">{{ $m['label'] }}</p>
                    <p class="text-sm font-semibold text-slate-200">{{ $m['value'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Section 1: Simulation Overview --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-base font-bold text-slate-200 mb-4 pb-2 border-b border-slate-800">
                1. Simulation Overview
            </h2>
            <div class="grid grid-cols-2 gap-x-8 gap-y-0">
                @foreach([
                    ['label' => 'Simulation Name',  'value' => $simulation->name],
                    ['label' => 'DRL Algorithm',    'value' => $simulation->algorithm],
                    ['label' => 'Edge Nodes',       'value' => $simulation->num_edge_nodes],
                    ['label' => 'IoT Devices',      'value' => $simulation->num_iot_devices],
                    ['label' => 'Tasks Generated',  'value' => $simulation->tasks()->count()],
                    ['label' => 'Training Runs',    'value' => $trainingRuns->count()],
                    ['label' => 'Status',           'value' => ucfirst($simulation->status)],
                    ['label' => 'Created',          'value' => $simulation->created_at->format('M d, Y H:i')],
                ] as $row)
                <div class="flex justify-between py-2 border-b border-slate-800/60">
                    <span class="text-sm text-slate-400">{{ $row['label'] }}</span>
                    <span class="text-sm font-medium text-slate-200">{{ $row['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Section 2: Training Results --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-base font-bold text-slate-200 mb-4 pb-2 border-b border-slate-800">
                2. Training Results
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                @foreach([
                    ['label' => 'Total Reward',    'value' => round($latestResult->total_reward ?? 0, 3),       'color' => 'text-primary-400'],
                    ['label' => 'Avg Latency',     'value' => round($latestResult->avg_latency ?? 0, 1).' ms',  'color' => 'text-amber-400'],
                    ['label' => 'Success Rate',    'value' => round($latestResult->task_success_rate ?? 0, 1).'%','color' => 'text-emerald-400'],
                    ['label' => 'Throughput',      'value' => round($latestResult->throughput ?? 0, 3).' t/s',  'color' => 'text-violet-400'],
                ] as $m)
                <div class="bg-slate-800/60 rounded-lg p-4 text-center">
                    <p class="text-xl font-bold {{ $m['color'] }}">{{ $m['value'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $m['label'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Reward history sample --}}
            @if(! empty($latestResult->reward_history))
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                    Reward History (first 20 steps)
                </p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(array_slice($latestResult->reward_history, 0, 20) as $i => $r)
                    <span class="px-2 py-1 rounded text-xs font-mono
                        {{ $r >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                        S{{ $i+1 }}: {{ round($r, 2) }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Section 3: Training Runs Table --}}
        @if($trainingRuns->count() > 0)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-base font-bold text-slate-200 mb-4 pb-2 border-b border-slate-800">
                3. All Training Runs
            </h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700">
                        @foreach(['Run','Algorithm','Total Timesteps','Mean Reward','Final Reward','Avg Latency','Completed']) as $col)
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider py-2 pr-4">
                            {{ $col }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($trainingRuns as $run)
                    @php $rResult = $run->results()->latest()->first(); @endphp
                    <tr>
                        <td class="py-3 pr-4 font-mono text-xs text-slate-400">#{{ $run->id }}</td>
                        <td class="py-3 pr-4"><x-ui.badge color="violet">{{ $run->algorithm }}</x-ui.badge></td>
                        <td class="py-3 pr-4 text-slate-300">{{ number_format($run->total_timesteps) }}</td>
                        <td class="py-3 pr-4 font-mono text-slate-300">{{ round($run->mean_reward ?? 0, 4) }}</td>
                        <td class="py-3 pr-4 font-mono text-slate-300">{{ round($run->final_reward ?? 0, 4) }}</td>
                        <td class="py-3 pr-4 text-slate-300">{{ $rResult ? round($rResult->avg_latency ?? 0, 1).' ms' : '—' }}</td>
                        <td class="py-3 text-xs text-slate-400">{{ $run->completed_at?->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Section 4: Node Utilization --}}
        @if(! empty($latestResult->node_utilization))
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-base font-bold text-slate-200 mb-4 pb-2 border-b border-slate-800">
                4. Edge Node Utilization
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($latestResult->node_utilization as $node)
                <div class="bg-slate-800/60 rounded-lg p-4">
                    <p class="text-sm font-semibold text-slate-300 mb-3">{{ $node['name'] }}</p>
                    <x-ui.resource-bar label="CPU" :used="$node['cpu_util']" :total="100" unit="%"/>
                    <div class="mt-2">
                        <x-ui.resource-bar label="Memory" :used="$node['memory_util']" :total="100" unit="%"/>
                    </div>
                    <div class="mt-3 flex justify-between text-xs">
                        <span class="text-slate-500">Queue</span>
                        <span class="text-slate-300">{{ $node['queue_length'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Download CTA --}}
        <div class="flex justify-center pb-4">
            <a href="{{ route('simulations.reports.download', $simulation) }}"
               class="inline-flex items-center gap-2 px-8 py-3 bg-primary-600 hover:bg-primary-500
                      text-white font-semibold rounded-xl transition-colors shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Full PDF Report
            </a>
        </div>

    </div>
    @endif

</x-layouts.app>
