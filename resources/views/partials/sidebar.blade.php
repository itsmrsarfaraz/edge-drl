<div x-data="{ open: false }" 
     @toggle-sidebar.window="open = $event.detail"
     class="contents">

    {{-- Mobile Overlay Backdrop --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false" 
         class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden"
         x-cloak>
    </div>

    {{-- Sidebar Container Drawer --}}
    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
           x-cloak>

        {{-- Logo & Close Button --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-ui.application-logo />
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-100">Edge DRL</p>
                    <p class="text-xs text-slate-400">Edge Computing Sim</p>
                </div>
            </div>

            {{-- Close Button (Mobile Only) --}}
            <button @click="open = false" 
                    class="p-1 rounded-md text-slate-400 hover:text-slate-200 hover:bg-slate-800 lg:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            @php
                $navItems = [
                    [
                        'label'  => 'Dashboard',
                        'href'   => route('dashboard'),
                        'active' => request()->routeIs('dashboard'),
                        'icon'   => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                    ],
                    [
                        'label'  => 'Simulations',
                        'href'   => route('simulations.index'),
                        'active' => request()->routeIs('simulations.index')
                                   || request()->routeIs('simulations.create')
                                   || request()->routeIs('simulations.show')
                                   || request()->routeIs('simulations.training.*'),
                        'icon'   => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                    ],
                    [
                        'label'  => 'Edge Nodes',
                        'href'   => route('simulations.index'),
                        'active' => request()->routeIs('simulations.nodes.*'),
                        'icon'   => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2',
                    ],
                    [
                        'label'  => 'IoT Tasks',
                        'href'   => route('simulations.index'),
                        'active' => request()->routeIs('simulations.tasks.*'),
                        'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    ],
                    [
                        'label'  => 'Analytics',
                        'href'   => route('simulations.index'),
                        'active' => request()->routeIs('simulations.analytics.*'),
                        'icon'   => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    ],
                    [
                        'label'  => 'Reports',
                        'href'   => route('simulations.index'),
                        'active' => request()->routeIs('simulations.reports.*'),
                        'icon'   => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all border
                          {{ $item['active']
                                ? 'bg-primary-500/10 text-primary-400 border-primary-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200 border-transparent' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Profile & Logout --}}
        <div class="px-3 py-4 border-t border-slate-800 space-y-0.5">
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all border
                      {{ request()->routeIs('profile.*')
                            ? 'bg-primary-500/10 text-primary-400 border-primary-500/20'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200 border-transparent' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-all border border-transparent">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>
</div>