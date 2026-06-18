<x-layouts.app title="Dashboard">
    @php
        $user = auth()->user();
        $stats = [
            ['label' => 'Total Simulations', 'value' => $user->simulations()->count(),                              'color' => 'primary', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ['label' => 'Edge Nodes',         'value' => \App\Models\EdgeNode::whereHas('simulation', fn($q) => $q->where('user_id', $user->id))->count(), 'color' => 'emerald', 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2'],
            ['label' => 'Tasks Generated',    'value' => \App\Models\Task::whereHas('simulation', fn($q) => $q->where('user_id', $user->id))->count(),     'color' => 'violet',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['label' => 'Training Runs',      'value' => \App\Models\TrainingRun::whereHas('simulation', fn($q) => $q->where('user_id', $user->id))->count(), 'color' => 'amber', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ];
        $recent = $user->simulations()->latest()->take(5)->get();
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($stats as $s)
                <x-ui.stat-card :label="$s['label']" :value="$s['value']" :color="$s['color']" :icon="$s['icon']"/>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Recent Simulations</h2>
                    <a href="{{ route('simulations.index') }}" class="text-xs text-primary-400 hover:text-primary-300">View all →</a>
                </div>
                @if($recent->isEmpty())
                    <x-ui.empty-state
                        title="No simulations yet"
                        description="Create your first simulation to get started."
                        icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        action-label="New Simulation"
                        :action-route="route('simulations.create')"
                    />
                @else
                    <div class="space-y-2">
                        @foreach($recent as $sim)
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                            <div>
                                <a href="{{ route('simulations.show', $sim) }}"
                                   class="text-sm font-medium text-slate-200 hover:text-primary-400 transition-colors">
                                    {{ $sim->name }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $sim->algorithm }} · {{ $sim->num_tasks }} tasks</p>
                            </div>
                            <x-ui.badge :color="$sim->status_color">{{ ucfirst($sim->status) }}</x-ui.badge>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('simulations.create') }}"
                       class="flex items-center gap-3 w-full px-3 py-2.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-sm text-slate-300 transition-colors">
                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Simulation
                    </a>
                    <a href="{{ route('simulations.index') }}"
                       class="flex items-center gap-3 w-full px-3 py-2.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-sm text-slate-300 transition-colors">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        All Simulations
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
