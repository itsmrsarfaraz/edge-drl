<header class="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 flex-shrink-0">
    <div>
        <h1 class="text-lg font-semibold text-slate-100">{{ $title ?? 'Dashboard' }}</h1>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-500/20 border border-primary-500/30 flex items-center justify-center">
                <span class="text-xs font-semibold text-primary-400">{{ substr(auth()->user()->name, 0, 1) }}</span>
            </div>
            <div class="hidden sm:block">
                <p class="text-sm font-medium text-slate-200">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</header>
