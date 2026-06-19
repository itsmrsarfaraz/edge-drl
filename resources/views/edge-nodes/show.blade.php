<x-layouts.app :title="$edgeNode->name">
    <x-ui.breadcrumb :items="[
        ['label' => 'Simulations',  'route' => route('simulations.index')],
        ['label' => $simulation->name, 'route' => route('simulations.show', $simulation)],
        ['label' => 'Edge Nodes',   'route' => route('simulations.nodes.index', $simulation)],
        ['label' => $edgeNode->name],
    ]"/>
    <x-ui.page-header
        :title="$edgeNode->name"
        :description="'Node in simulation: ' . $simulation->name">
        <x-slot:action>
            <div class="flex items-center gap-3">
                <x-ui.badge :color="$edgeNode->status_color">{{ ucfirst($edgeNode->status) }}</x-ui.badge>
                <a href="{{ route('simulations.nodes.index', $simulation) }}"
                   class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                    ← Nodes
                </a>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert class="mb-5">{{ session('success') }}</x-ui.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Specs & Controls --}}
        <div class="space-y-5">

            {{-- Resource Gauges --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">
                    Live Resources
                </h2>
                <div class="space-y-5">
                    <x-ui.resource-bar
                        label="CPU Usage"
                        :used="$edgeNode->cpu_used"
                        :total="$edgeNode->cpu_capacity"
                        unit="%"
                    />
                    <x-ui.resource-bar
                        label="Memory Usage"
                        :used="$edgeNode->memory_used"
                        :total="$edgeNode->memory_capacity"
                        unit="MB"
                    />
                </div>

                {{-- Big utilization circle-ish display --}}
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-slate-100">
                            {{ $edgeNode->queue_length }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">Queued Tasks</p>
                    </div>
                    <div class="bg-slate-800/60 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold
                            @if($edgeNode->utilization_percentage >= 90) text-red-400
                            @elseif($edgeNode->utilization_percentage >= 70) text-amber-400
                            @else text-emerald-400
                            @endif">
                            {{ round($edgeNode->utilization_percentage, 1) }}%
                        </p>
                        <p class="text-xs text-slate-500 mt-1">Utilization</p>
                    </div>
                </div>
            </div>

            {{-- Specs --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Specifications
                </h2>
                <x-edge-nodes.spec-row label="CPU Capacity"    :value="$edgeNode->cpu_capacity . '%'"/>
                <x-edge-nodes.spec-row label="Memory Capacity" :value="number_format($edgeNode->memory_capacity) . ' MB'"/>
                <x-edge-nodes.spec-row label="CPU Used"        :value="round($edgeNode->cpu_used, 1) . '%'"/>
                <x-edge-nodes.spec-row label="Memory Used"     :value="number_format($edgeNode->memory_used) . ' MB'"/>
                <x-edge-nodes.spec-row label="Simulation"      :value="$simulation->name"/>
                <x-edge-nodes.spec-row label="Created"         :value="$edgeNode->created_at->format('M d, Y H:i')"/>
            </div>

            {{-- Reset Control --}}
            <div class="bg-slate-900 border border-amber-500/20 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-amber-400 uppercase tracking-wider mb-3">
                    Node Controls
                </h2>
                <form method="POST"
                      action="{{ route('simulations.nodes.reset', [$simulation, $edgeNode]) }}"
                      onsubmit="return confirm('Reset this node to idle state?')">
                    @csrf
                    <button type="submit"
                            class="w-full py-2 px-4 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30
                                   text-amber-400 text-sm font-medium rounded-lg transition-colors">
                        Reset Node to Idle
                    </button>
                </form>
            </div>

        </div>

        {{-- Right Column: Assigned Tasks --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                        Assigned Tasks
                    </h2>
                    <span class="text-xs text-slate-500">
                        Showing latest {{ $tasks->count() }}
                    </span>
                </div>

                @if($tasks->isEmpty())
                    <x-ui.empty-state
                        title="No tasks assigned yet"
                        description="Tasks will appear here once the simulation runs and the DRL agent starts allocating work."
                        icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-800">
                                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider pb-3 pr-4">Task</th>
                                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider pb-3 pr-4">Priority</th>
                                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider pb-3 pr-4">CPU Req.</th>
                                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider pb-3 pr-4">Status</th>
                                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider pb-3">Latency</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @foreach($tasks as $task)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="py-3 pr-4">
                                        <span class="font-mono text-xs text-slate-300">
                                            {{ $task->task_id_label ?? 'TASK-' . str_pad($task->id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-ui.badge :color="$task->priority_color">
                                            {{ ucfirst($task->priority) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-300">
                                        {{ round($task->cpu_requirement, 1) }}%
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-ui.badge :color="$task->status_color">
                                            {{ ucfirst($task->status) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="py-3 text-slate-300">
                                        {{ $task->latency ? round($task->latency, 2) . ' ms' : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
