@props(['label', 'used', 'total', 'unit' => '%', 'color' => 'auto'])

@php
    $percent = $total > 0 ? min(round(($used / $total) * 100, 1), 100) : 0;

    if ($color === 'auto') {
        $barColor = match(true) {
            $percent >= 90 => 'bg-red-500',
            $percent >= 70 => 'bg-amber-500',
            $percent >= 40 => 'bg-primary-500',
            default        => 'bg-emerald-500',
        };
    } else {
        $barColor = "bg-{$color}-500";
    }

    $displayUsed  = $unit === 'MB' ? round($used) . ' MB'  : $percent . '%';
    $displayTotal = $unit === 'MB' ? round($total) . ' MB' : '100%';
@endphp

<div class="space-y-1">
    <div class="flex justify-between items-center">
        <span class="text-xs text-slate-400">{{ $label }}</span>
        <span class="text-xs font-medium text-slate-300">
            {{ $displayUsed }} / {{ $displayTotal }}
        </span>
    </div>
    <div class="h-1.5 bg-slate-700 rounded-full overflow-hidden">
        <div class="{{ $barColor }} h-full rounded-full transition-all duration-500"
             style="width: {{ $percent }}%"></div>
    </div>
</div>
