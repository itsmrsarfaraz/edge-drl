@props(['title', 'description' => null])

<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-100">{{ $title }}</h1>
        @if($description)
            <p class="text-sm text-slate-400 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    @if(isset($action))
        <div>{{ $action }}</div>
    @endif
</div>
