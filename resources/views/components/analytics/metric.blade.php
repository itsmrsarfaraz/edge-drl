@props(['label', 'value', 'sub' => null, 'color' => 'primary', 'icon'])

@php
$colors = [
    'primary' => ['ring' => 'ring-primary-500/20', 'bg' => 'bg-primary-500/10', 'text' => 'text-primary-400', 'val' => 'text-primary-300'],
    'emerald' => ['ring' => 'ring-emerald-500/20', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'val' => 'text-emerald-300'],
    'amber'   => ['ring' => 'ring-amber-500/20',   'bg' => 'bg-amber-500/10',   'text' => 'text-amber-400',   'val' => 'text-amber-300'],
    'violet'  => ['ring' => 'ring-violet-500/20',  'bg' => 'bg-violet-500/10',  'text' => 'text-violet-400',  'val' => 'text-violet-300'],
    'red'     => ['ring' => 'ring-red-500/20',      'bg' => 'bg-red-500/10',     'text' => 'text-red-400',     'val' => 'text-red-300'],
];
$c = $colors[$color] ?? $colors['primary'];
@endphp

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 ring-1 {{ $c['ring'] }}">
    <div class="flex items-start justify-between mb-3">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">{{ $label }}</p>
        <div class="w-8 h-8 rounded-lg {{ $c['bg'] }} flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold {{ $c['val'] }}">{{ $value ?? '—' }}</p>
    @if($sub)
        <p class="text-xs text-slate-500 mt-1">{{ $sub }}</p>
    @endif
</div>
