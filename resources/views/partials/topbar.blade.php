<header class="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 flex-shrink-0">
    <div>
        <h1 class="text-lg font-semibold text-slate-100">{{ $title ?? 'Dashboard' }}</h1>
    </div>
    <div class="flex items-center gap-4">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 group hover:opacity-90 transition-opacity">
            <div class="w-8 h-8 rounded-full bg-primary-500/20 border border-primary-500/30 flex items-center justify-center group-hover:border-primary-500/50 transition-colors">
                <span class="text-xs font-semibold text-primary-400">{{ substr(auth()->user()->name, 0, 1) }}</span>
            </div>
            <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-slate-200 group-hover:text-primary-400 transition-colors">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </a>
    </div>
</header>
