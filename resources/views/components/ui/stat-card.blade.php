@props(['label', 'value', 'color' => 'primary', 'icon'])

@php
$colors = [
    'primary' => ['bg' => 'bg-primary-500/10', 'text' => 'text-primary-400'],
    'emerald' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400'],
    'amber'   => ['bg' => 'bg-amber-500/10',   'text' => 'text-amber-400'],
    'violet'  => ['bg' => 'bg-violet-500/10',  'text' => 'text-violet-400'],
    'red'     => ['bg' => 'bg-red-500/10',      'text' => 'text-red-400'],
    'slate'   => ['bg' => 'bg-slate-500/10',   'text' => 'text-slate-400'],
];
$c = $colors[$color] ?? $colors['primary'];
@endphp

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">{{ $label }}</p>
        <div class="w-8 h-8 rounded-lg {{ $c['bg'] }} flex items-center justify-center">
            <svg class="w-4 h-4 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-slate-100">{{ $value }}</p>
</div>
