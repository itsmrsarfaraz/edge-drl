<header class="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 flex-shrink-0">

    {{-- Page title --}}
    <div>
        <h1 class="text-base font-semibold text-slate-200">{{ $title ?? 'Dashboard' }}</h1>
    </div>

    <div class="flex items-center gap-5">

        {{-- AI Engine status dot --}}
        <div x-data="aiStatus()" x-init="check()" class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full transition-colors"
                  :class="online ? 'bg-emerald-400' : 'bg-red-400 animate-pulse'"></span>
            <span class="text-xs text-slate-400 hidden sm:block"
                  x-text="online ? 'AI Engine Online' : 'AI Engine Offline'"></span>
        </div>

        {{-- User avatar → profile --}}
        <a href="{{ route('profile.show') }}"
           class="flex items-center gap-3 hover:opacity-80 transition-opacity">
            <div class="w-8 h-8 rounded-full bg-primary-500/20 border border-primary-500/40
                        flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-bold text-primary-400">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
            <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-slate-200 leading-tight">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 leading-tight">{{ auth()->user()->email }}</p>
            </div>
        </a>

    </div>
</header>

@push('scripts')
<script>
function aiStatus() {
    return {
        online: false,
        async check() {
            try {
                const res  = await fetch('/ai/health');
                const data = await res.json();
                this.online = data.status === 'ok';
            } catch {
                this.online = false;
            }
            setTimeout(() => this.check(), 30000);
        }
    }
}
</script>
@endpush
