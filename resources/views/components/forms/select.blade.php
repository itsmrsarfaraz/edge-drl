@props(['label', 'name', 'options' => [], 'hint' => null, 'required' => false])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-300 mb-1.5">
        {{ $label }}
        @if($required)<span class="text-red-400 ml-0.5">*</span>@endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition']) }}
    >
        @foreach($options as $value => $display)
            <option value="{{ $value }}" {{ old($name) == $value ? 'selected' : '' }}>
                {{ $display }}
            </option>
        @endforeach
    </select>
    @if($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
