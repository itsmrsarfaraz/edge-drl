@props(['label', 'value', 'badge' => false, 'color' => 'slate'])

<div class="flex items-center justify-between py-3 border-b border-slate-800 last:border-0">
    <span class="text-sm text-slate-400">{{ $label }}</span>
    @if($badge)
        <x-ui.badge :color="$color">{{ $value }}</x-ui.badge>
    @else
        <span class="text-sm font-medium text-slate-200">{{ $value }}</span>
    @endif
</div>
