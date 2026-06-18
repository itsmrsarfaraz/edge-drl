@props(['color' => 'slate', 'size' => 'sm'])

@php
$colors = [
    'slate'   => 'bg-slate-500/10 text-slate-400 ring-slate-500/20',
    'primary' => 'bg-primary-500/10 text-primary-400 ring-primary-500/20',
    'emerald' => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
    'amber'   => 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
    'red'     => 'bg-red-500/10 text-red-400 ring-red-500/20',
    'violet'  => 'bg-violet-500/10 text-violet-400 ring-violet-500/20',
];
$cls = $colors[$color] ?? $colors['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset $cls"]) }}>
    {{ $slot }}
</span>
