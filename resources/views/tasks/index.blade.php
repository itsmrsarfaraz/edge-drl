<x-layouts.app :title="$simulation->name . ' — Tasks'">

    <x-ui.page-header
        :title="$simulation->name"
        description="IoT task workload for this simulation.">
        <x-slot:action>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('simulations.tasks.generate', $simulation) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-500
                                   text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ $stats['total'] > 0 ? 'Regenerate Tasks' : 'Generate Tasks' }}
                    </button>
                </form>
                <a href="{{ route('simulations.show', $simulation) }}"
                   class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                    ← Simulation
                </a>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert class="mb-5">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach([
            ['label' => 'Total',     'value' => $stats['total'],     'color' => 'slate'],
            ['label' => 'Pending',   'value' => $stats['pending'],   'color' => 'slate'],
            ['label' => 'Completed', 'value' => $stats['completed'], 'color' => 'emerald'],
            ['label' => 'Failed',    'value' => $stats['failed'],    'color' => 'red'],
            ['label' => 'Critical',  'value' => $stats['critical'],  'color' => 'red'],
            ['label' => 'High',      'value' => $stats['high'],      'color' => 'amber'],
        ] as $s)
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-center">
            <p class="text-xl font-bold text-slate-100">{{ $s['value'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    @if($tasks->isEmpty())
        <x-ui.empty-state
            title="No tasks generated yet"
            description="Click 'Generate Tasks' to create a simulated IoT workload using the Python generator."
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
    @else
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/50">
                            @foreach(['Task ID','Device','Priority','CPU Req.','Mem Req.','Size','Deadline','Assigned To','Status','Latency'] as $col)
                            <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider px-4 py-3 whitespace-nowrap">
                                {{ $col }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <x-tasks.row :task="$task"/>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tasks->hasPages())
                <div class="px-4 py-3 border-t border-slate-800">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    @endif

</x-layouts.app>
