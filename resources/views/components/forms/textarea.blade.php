@props(['label', 'name', 'rows' => 3, 'hint' => null, 'required' => false])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-300 mb-1.5">
        {{ $label }}
        @if($required)<span class="text-red-400 ml-0.5">*</span>@endif
    </label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm
                                          placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500
                                          focus:border-transparent transition resize-none']) }}
    >{{ old($name) }}</textarea>
    @if($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
