<x-layouts.app title="New Simulation">
    <x-ui.page-header
        title="New Simulation"
        description="Configure your edge computing simulation environment.">
        <x-slot:action>
            <a href="{{ route('simulations.index') }}"
               class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                ← Back
            </a>
        </x-slot:action>
    </x-ui.page-header>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('simulations.store') }}" class="space-y-6">
            @csrf

            {{-- Basic Info --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Basic Information</h2>

                <x-forms.input
                    label="Simulation Name"
                    name="name"
                    placeholder="e.g. Edge Network Scenario A"
                    :value="old('name')"
                    :required="true"
                    maxlength="100"
                />

                <x-forms.textarea
                    label="Description"
                    name="description"
                    placeholder="Briefly describe the purpose of this simulation..."
                    :rows="3"
                />
            </div>

            {{-- Environment Config --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Environment Configuration</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <x-forms.input
                        label="Edge Nodes"
                        name="num_edge_nodes"
                        type="number"
                        :value="old('num_edge_nodes', 3)"
                        min="1" max="10"
                        :required="true"
                        hint="1 – 10 nodes"
                    />
                    <x-forms.input
                        label="IoT Devices"
                        name="num_iot_devices"
                        type="number"
                        :value="old('num_iot_devices', 10)"
                        min="1" max="50"
                        :required="true"
                        hint="1 – 50 devices"
                    />
                    <x-forms.input
                        label="Tasks to Generate"
                        name="num_tasks"
                        type="number"
                        :value="old('num_tasks', 50)"
                        min="10" max="500"
                        :required="true"
                        hint="10 – 500 tasks"
                    />
                </div>
            </div>

            {{-- Algorithm --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">DRL Algorithm</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach(['PPO' => ['label' => 'PPO', 'sub' => 'Proximal Policy Optimization — stable, recommended for beginners'], 'DQN' => ['label' => 'DQN', 'sub' => 'Deep Q-Network — discrete action spaces, faster training']] as $algo => $info)
                    <label class="relative flex items-start gap-4 p-4 bg-slate-800 border rounded-xl cursor-pointer transition-all
                                  {{ old('algorithm', 'PPO') === $algo ? 'border-primary-500 ring-1 ring-primary-500/30' : 'border-slate-700 hover:border-slate-600' }}">
                        <input type="radio" name="algorithm" value="{{ $algo }}"
                               {{ old('algorithm', 'PPO') === $algo ? 'checked' : '' }}
                               class="mt-1 text-primary-500 bg-slate-700 border-slate-600 focus:ring-primary-500">
                        <div>
                            <p class="text-sm font-semibold text-slate-200">{{ $info['label'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $info['sub'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('algorithm')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                    Create Simulation
                </button>
                <a href="{{ route('simulations.index') }}"
                   class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
