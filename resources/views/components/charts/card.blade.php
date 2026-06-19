@props(['title', 'description' => null, 'height' => 'h-64'])

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-slate-300">{{ $title }}</h3>
        @if($description)
            <p class="text-xs text-slate-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    <div class="{{ $height }} relative">
        {{ $slot }}
    </div>
</div>
