@props(['label', 'value'])

<div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
    <span class="text-sm text-slate-400">{{ $label }}</span>
    <span class="text-sm font-medium text-slate-200">{{ $value }}</span>
</div>
