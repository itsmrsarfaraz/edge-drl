@props(['type' => 'success'])

@php
$styles = [
    'success' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
    'error'   => 'bg-red-500/10 border-red-500/20 text-red-400',
    'info'    => 'bg-primary-500/10 border-primary-500/20 text-primary-400',
];
$cls = $styles[$type] ?? $styles['success'];
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg px-4 py-3 text-sm $cls"]) }}>
    {{ $slot }}
</div>
