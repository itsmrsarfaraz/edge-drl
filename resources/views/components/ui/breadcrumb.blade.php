@props(['items' => []])

{{--
Usage:
<x-ui.breadcrumb :items="[
    ['label' => 'Simulations', 'route' => route('simulations.index')],
    ['label' => $simulation->name, 'route' => route('simulations.show', $simulation)],
    ['label' => 'Analytics'],
]"/>
--}}

@if(count($items) > 1)
<nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-5">
    @foreach($items as $i => $item)
        @if($i > 0)
            <svg class="w-3 h-3 text-slate-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @endif

        @if(isset($item['route']) && $i < count($items) - 1)
            <a href="{{ $item['route'] }}"
               class="hover:text-primary-400 transition-colors truncate max-w-[160px]">
                {{ $item['label'] }}
            </a>
        @else
            <span class="text-slate-300 truncate max-w-[200px]">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
@endif
