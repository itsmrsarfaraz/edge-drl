<x-layouts.app :title="$simulation->name . ' — Edge Nodes'">

    <x-ui.page-header
        :title="$simulation->name"
        description="Edge nodes provisioned for this simulation.">
        <x-slot:action>
            <a href="{{ route('simulations.show', $simulation) }}"
               class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                ← Simulation
            </a>
        </x-slot:action>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert class="mb-5">{{ session('success') }}</x-ui.alert>
    @endif

    {{-- Summary Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $idle       = $nodes->where('status', 'idle')->count();
            $busy       = $nodes->where('status', 'busy')->count();
            $overloaded = $nodes->where('status', 'overloaded')->count();
            $offline    = $nodes->where('status', 'offline')->count();
        @endphp
        <x-ui.stat-card label="Idle"       :value="$idle"       color="emerald" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <x-ui.stat-card label="Busy"       :value="$busy"       color="amber"   icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <x-ui.stat-card label="Overloaded" :value="$overloaded" color="red"     icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        <x-ui.stat-card label="Offline"    :value="$offline"    color="slate"   icon="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
    </div>

    @if($nodes->isEmpty())
        <x-ui.empty-state
            title="No edge nodes found"
            description="Edge nodes are provisioned automatically when a simulation is created."
            icon="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($nodes as $node)
                <x-edge-nodes.card :node="$node" :simulation="$simulation"/>
            @endforeach
        </div>
    @endif

</x-layouts.app>
