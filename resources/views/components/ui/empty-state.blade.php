@props(['title', 'description', 'icon', 'actionLabel' => null, 'actionRoute' => null])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold text-slate-300 mb-1">{{ $title }}</h3>
    <p class="text-sm text-slate-500 max-w-sm mb-6">{{ $description }}</p>
    @if($actionLabel && $actionRoute)
        <a href="{{ $actionRoute }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ $actionLabel }}
        </a>
    @endif
</div>
