@props(['node', 'simulation'])

@php
$statusDot = match($node->status) {
    'busy'       => 'bg-amber-400',
    'overloaded' => 'bg-red-400 animate-pulse',
    'offline'    => 'bg-slate-500',
    default      => 'bg-emerald-400',
};
@endphp

<div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-xl p-5 transition-colors">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full {{ $statusDot }} flex-shrink-0 mt-0.5"></span>
            <div>
                <a href="{{ route('simulations.nodes.show', [$simulation, $node]) }}"
                   class="text-sm font-semibold text-slate-200 hover:text-primary-400 transition-colors">
                    {{ $node->name }}
                </a>
                <p class="text-xs text-slate-500">ID #{{ $node->id }}</p>
            </div>
        </div>
        <x-ui.badge :color="$node->status_color">{{ ucfirst($node->status) }}</x-ui.badge>
    </div>

    {{-- Resource Bars --}}
    <div class="space-y-3 mb-4">
        <x-ui.resource-bar
            label="CPU"
            :used="$node->cpu_used"
            :total="$node->cpu_capacity"
            unit="%"
        />
        <x-ui.resource-bar
            label="Memory"
            :used="$node->memory_used"
            :total="$node->memory_capacity"
            unit="MB"
        />
    </div>

    {{-- Queue & Utilization --}}
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-slate-800/60 rounded-lg px-3 py-2 text-center">
            <p class="text-lg font-bold text-slate-200">{{ $node->queue_length }}</p>
            <p class="text-xs text-slate-500">Queue</p>
        </div>
        <div class="bg-slate-800/60 rounded-lg px-3 py-2 text-center">
            <p class="text-lg font-bold text-slate-200">{{ round($node->utilization_percentage, 1) }}%</p>
            <p class="text-xs text-slate-500">Utilization</p>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="flex items-center justify-between pt-3 border-t border-slate-800">
        <a href="{{ route('simulations.nodes.show', [$simulation, $node]) }}"
           class="text-xs text-slate-400 hover:text-primary-400 transition-colors">
            View details →
        </a>
        <form method="POST"
              action="{{ route('simulations.nodes.reset', [$simulation, $node]) }}"
              onsubmit="return confirm('Reset {{ $node->name }} to idle?')">
            @csrf
            <button type="submit"
                    class="text-xs text-slate-500 hover:text-amber-400 transition-colors px-2 py-1 rounded hover:bg-slate-800">
                Reset
            </button>
        </form>
    </div>
</div>
