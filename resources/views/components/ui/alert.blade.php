@props(['type' => 'success'])

@php
$config = [
    'success' => [
        'wrap'  => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300',
        'icon'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'iclr'  => 'text-emerald-400',
    ],
    'error' => [
        'wrap'  => 'bg-red-500/10 border-red-500/20 text-red-300',
        'icon'  => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'iclr'  => 'text-red-400',
    ],
    'info' => [
        'wrap'  => 'bg-primary-500/10 border-primary-500/20 text-primary-300',
        'icon'  => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'iclr'  => 'text-primary-400',
    ],
    'warning' => [
        'wrap'  => 'bg-amber-500/10 border-amber-500/20 text-amber-300',
        'icon'  => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'iclr'  => 'text-amber-400',
    ],
];
$c = $config[$type] ?? $config['success'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 border rounded-lg px-4 py-3 text-sm {$c['wrap']}"]) }}
     x-data="{ show: true }" x-show="show" x-cloak>
    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 {{ $c['iclr'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}"/>
    </svg>
    <span class="flex-1">{{ $slot }}</span>
    <button @click="show = false" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity ml-2">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
