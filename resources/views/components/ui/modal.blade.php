@props(['name', 'show' => false])

<div
    x-data="{ 
        show: @js($show),
        focusables() {
            let selector = 'a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)].filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables()[this.focusables().length - 1] },
        nextFocusable() { return this.focusables()[this.focusables().indexOf(document.activeElement) + 1] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.focusables().indexOf(document.activeElement) - 1] || this.lastFocusable() }
    }"
    x-init="
        $watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
                {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
    "
    x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail.name === '{{ $name }}') show = false"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey ? prevFocusable().focus() : nextFocusable().focus()"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
    style="display: none;"
>
    <div x-show="show" class="fixed inset-0 transform transition-all z-40" x-on:click="show = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
    </div>

    <div x-show="show" class="relative z-50 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-md p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        {{ $slot }}
    </div>
</div>