<x-layouts.app title="My Profile">

    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'route' => route('dashboard')],
        ['label' => 'Profile'],
    ]"/>

    <x-ui.page-header
        title="My Profile"
        description="Manage your account settings and preferences.">
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Account Card --}}
        <div class="space-y-5">

            {{-- Avatar + Info --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center">
                <div class="w-20 h-20 rounded-full bg-primary-500/20 border-2 border-primary-500/40
                            flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl font-bold text-primary-400">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
                <h2 class="text-base font-bold text-slate-100">{{ $user->name }}</h2>
                <p class="text-sm text-slate-400 mt-0.5">{{ $user->email }}</p>
                <p class="text-xs text-slate-600 mt-2">
                    Member since {{ $user->created_at->format('M Y') }}
                </p>
            </div>

            {{-- Activity Stats --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">
                    Your Activity
                </h3>
                <div class="space-y-0">
                    @foreach([
                        ['label' => 'Simulations',    'value' => $stats['simulations'],   'color' => 'text-primary-400'],
                        ['label' => 'Training Runs',  'value' => $stats['training_runs'], 'color' => 'text-violet-400'],
                        ['label' => 'Tasks Created',  'value' => $stats['tasks'],         'color' => 'text-amber-400'],
                        ['label' => 'Completed Sims', 'value' => $stats['completed'],     'color' => 'text-emerald-400'],
                    ] as $s)
                    <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                        <span class="text-sm text-slate-400">{{ $s['label'] }}</span>
                        <span class="text-sm font-bold {{ $s['color'] }}">{{ $s['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">
                    Recent Simulations
                </h3>
                @forelse($recentActivity as $sim)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                    <div>
                        <a href="{{ route('simulations.show', $sim) }}"
                           class="text-sm font-medium text-slate-200 hover:text-primary-400 transition-colors">
                            {{ Str::limit($sim->name, 22) }}
                        </a>
                        <p class="text-xs text-slate-500">{{ $sim->algorithm }}</p>
                    </div>
                    <x-ui.badge :color="$sim->status_color">{{ ucfirst($sim->status) }}</x-ui.badge>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">No simulations yet.</p>
                @endforelse
            </div>

        </div>

        {{-- Right: Forms --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Update Profile Info --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-5">
                    Account Information
                </h2>
                <form method="POST" action="{{ route('profile.update-info') }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <x-forms.input
                        label="Full Name"
                        name="name"
                        :value="old('name', $user->name)"
                        :required="true"
                        placeholder="Your full name"
                    />
                    <x-forms.input
                        label="Email Address"
                        name="email"
                        type="email"
                        :value="old('email', $user->email)"
                        :required="true"
                        placeholder="you@example.com"
                    />
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white
                                       text-sm font-medium rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Change Password --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-5">
                    Change Password
                </h2>
                <form method="POST" action="{{ route('profile.update-password') }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <x-forms.input
                        label="Current Password"
                        name="current_password"
                        type="password"
                        :required="true"
                        placeholder="Your current password"
                    />
                    <x-forms.input
                        label="New Password"
                        name="password"
                        type="password"
                        :required="true"
                        placeholder="Minimum 8 characters"
                        hint="Use a mix of letters, numbers, and symbols."
                    />
                    <x-forms.input
                        label="Confirm New Password"
                        name="password_confirmation"
                        type="password"
                        :required="true"
                        placeholder="Repeat new password"
                    />
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white
                                       text-sm font-medium rounded-lg transition-colors">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- System Info --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">
                    System Information
                </h2>
                <div class="space-y-0">
                    @foreach([
                        ['label' => 'Platform',       'value' => 'Edge DRL Simulation Platform'],
                        ['label' => 'Laravel Version', 'value' => app()->version()],
                        ['label' => 'PHP Version',     'value' => PHP_VERSION],
                        ['label' => 'Environment',     'value' => config('app.env')],
                        ['label' => 'AI Engine URL',   'value' => config('services.python_ai.url')],
                        ['label' => 'Database',        'value' => config('database.default') . ' — ' . config('database.connections.mysql.database')],
                    ] as $row)
                    <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                        <span class="text-sm text-slate-400">{{ $row['label'] }}</span>
                        <span class="text-sm font-mono text-slate-300 text-right max-w-xs truncate">
                            {{ $row['value'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
