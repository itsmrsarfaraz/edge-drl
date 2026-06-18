<x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Total Simulations', 'value' => '0', 'color' => 'primary', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['label' => 'Edge Nodes', 'value' => '0', 'color' => 'emerald', 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2'],
                ['label' => 'Tasks Generated', 'value' => '0', 'color' => 'violet', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Training Runs', 'value' => '0', 'color' => 'amber', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ] as $stat)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm text-slate-400">{{ $stat['label'] }}</p>
                    <div class="w-8 h-8 rounded-lg bg-{{ $stat['color'] }}-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-{{ $stat['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-slate-100">{{ $stat['value'] }}</p>
            </div>
            @endforeach
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-base font-semibold text-slate-200 mb-2">Welcome to Edge DRL</h2>
            <p class="text-sm text-slate-400">Your simulation platform for Resource Allocation in Edge Computing using Deep Reinforcement Learning. Start by creating a simulation.</p>
        </div>
    </div>
</x-layouts.app>
