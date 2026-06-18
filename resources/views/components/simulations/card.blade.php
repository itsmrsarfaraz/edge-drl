@props(['simulation'])

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition-colors group">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
            <a href="{{ route('simulations.show', $simulation) }}"
               class="text-base font-semibold text-slate-100 hover:text-primary-400 transition-colors truncate block">
                {{ $simulation->name }}
            </a>
            @if($simulation->description)
                <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $simulation->description }}</p>
            @endif
        </div>
        <x-ui.badge :color="$simulation->status_color" class="ml-3 flex-shrink-0">
            {{ ucfirst($simulation->status) }}
        </x-ui.badge>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-4">
        @foreach([
            ['label' => 'Nodes',    'value' => $simulation->num_edge_nodes],
            ['label' => 'Devices',  'value' => $simulation->num_iot_devices],
            ['label' => 'Tasks',    'value' => $simulation->num_tasks],
        ] as $item)
        <div class="bg-slate-800/60 rounded-lg px-3 py-2 text-center">
            <p class="text-lg font-bold text-slate-200">{{ $item['value'] }}</p>
            <p class="text-xs text-slate-500">{{ $item['label'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <x-ui.badge color="violet">{{ $simulation->algorithm }}</x-ui.badge>
        <div class="flex items-center gap-2">
            <a href="{{ route('simulations.show', $simulation) }}"
               class="text-xs text-slate-400 hover:text-primary-400 transition-colors px-2 py-1 rounded hover:bg-slate-800">
                View →
            </a>
            <form method="POST" action="{{ route('simulations.destroy', $simulation) }}"
                  onsubmit="return confirm('Delete this simulation? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="text-xs text-slate-500 hover:text-red-400 transition-colors px-2 py-1 rounded hover:bg-slate-800">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <p class="text-xs text-slate-600 mt-3 border-t border-slate-800 pt-3">
        Created {{ $simulation->created_at->diffForHumans() }}
    </p>
</div>
