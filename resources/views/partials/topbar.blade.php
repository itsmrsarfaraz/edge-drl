<header class="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 flex-shrink-0">
    <div>
        <h1 class="text-lg font-semibold text-slate-100">{{ $title ?? 'Dashboard' }}</h1>
    </div>

    <div class="flex items-center gap-5">

        {{-- AI Engine Status --}}
        <div x-data="aiStatus()" x-init="check()" class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full"
                  :class="online ? 'bg-emerald-400' : 'bg-red-400 animate-pulse'"></span>
            <span class="text-xs text-slate-400" x-text="online ? 'AI Engine Online' : 'AI Engine Offline'"></span>
        </div>

        {{-- User --}}
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-500/20 border border-primary-500/30 flex items-center justify-center">
                <span class="text-xs font-semibold text-primary-400">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </span>
            </div>
            <div class="hidden sm:block">
                <p class="text-sm font-medium text-slate-200">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </div>

    </div>
</header>

@push('scripts')
<script>
function aiStatus() {
    return {
        online: false,
        async check() {
            try {
                const res = await fetch('/ai/health');
                const data = await res.json();
                this.online = data.status === 'ok';
            } catch {
                this.online = false;
            }
            // Re-check every 30 seconds
            setTimeout(() => this.check(), 30000);
        }
    }
}
</script>
@endpush