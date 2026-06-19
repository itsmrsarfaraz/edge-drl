<x-layouts.app title="Simulations">
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'route' => route('dashboard')],
        ['label' => 'Simulations'],
    ]"/>
    <x-ui.page-header
        title="Simulations"
        description="Manage your edge computing simulation environments.">
        <x-slot:action>
            <a href="{{ route('simulations.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Simulation
            </a>
        </x-slot:action>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert class="mb-5">{{ session('success') }}</x-ui.alert>
    @endif

    @if($simulations->isEmpty())
        <x-ui.empty-state
            title="No simulations yet"
            description="Create your first simulation to start experimenting with DRL-based resource allocation."
            icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            action-label="Create Simulation"
            :action-route="route('simulations.create')"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($simulations as $simulation)
                <x-simulations.card :simulation="$simulation"/>
            @endforeach
        </div>

        @if($simulations->hasPages())
            <div class="mt-6">{{ $simulations->links() }}</div>
        @endif
    @endif
</x-layouts.app>
