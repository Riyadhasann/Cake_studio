{{--
    A styled dropdown for the design page (a native <select> can't be styled when open). It runs inside the page's
    cakeStudio() Alpine scope; the props are JavaScript expressions in that scope, where "o" is one option:
    options (the list), selected (is "o" the current one), pick (choose "o"), label / meta (the text of "o"),
    current / currentMeta (the text on the button). Keyboard: arrows move, Enter or Space picks, Esc closes.
--}}
@props(['id', 'labelledby', 'options', 'selected', 'pick', 'label', 'meta' => "''", 'current', 'currentMeta' => "''", 'icon' => 'size'])

<div x-data="{ open: false, active: 0 }" class="relative" @click.outside="open = false" @keydown.escape.stop="if (open) { open = false; $refs.button.focus() }">
    <button type="button" x-ref="button" id="{{ $id }}-button"
            aria-haspopup="listbox" :aria-expanded="open" aria-labelledby="{{ $labelledby }} {{ $id }}-button"
            @click="open = !open; active = Math.max(0, ({{ $options }}).findIndex(o => {{ $selected }})); if (open) $nextTick(() => $refs.list.focus())"
            @keydown.arrow-down.prevent="open = true; active = Math.max(0, ({{ $options }}).findIndex(o => {{ $selected }})); $nextTick(() => $refs.list.focus())"
            class="group flex w-full items-center gap-3 rounded-2xl border-2 bg-white px-3 py-2.5 text-left shadow-[0_4px_12px_-4px_rgba(74,36,18,0.2)] transition hover:border-brand/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand/20"
            :class="open ? 'border-brand ring-4 ring-brand/15' : 'border-line'">
        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand text-white shadow-[0_6px_12px_-6px_rgba(181,86,42,0.7)]" aria-hidden="true">
            @if ($icon === 'layers')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/></svg>
            @else
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16M5 21v-6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6M5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0M12 13V9"/><path d="M12 6.5a1.3 1.3 0 0 0 1.3-1.3C13.3 4.2 12 3 12 3s-1.3 1.2-1.3 2.2A1.3 1.3 0 0 0 12 6.5Z"/></svg>
            @endif
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-base font-bold leading-tight text-ink" x-text="{{ $current }}"></span>
            <span class="block text-sm font-semibold text-brand" x-text="{{ $currentMeta }}"></span>
        </span>
        <svg class="h-5 w-5 shrink-0 text-ink/70 transition-transform duration-200" :class="open && 'rotate-180 text-brand'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
    </button>

    <ul x-show="open" x-cloak x-ref="list" role="listbox" tabindex="-1" aria-labelledby="{{ $labelledby }}"
        :aria-activedescendant="'{{ $id }}-option-' + active"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.arrow-down.prevent="active = Math.min(active + 1, ({{ $options }}).length - 1)"
        @keydown.arrow-up.prevent="active = Math.max(active - 1, 0)"
        @keydown.home.prevent="active = 0"
        @keydown.end.prevent="active = ({{ $options }}).length - 1"
        @keydown.enter.prevent="document.getElementById('{{ $id }}-option-' + active)?.click()"
        @keydown.space.prevent="document.getElementById('{{ $id }}-option-' + active)?.click()"
        @keydown.tab="open = false"
        class="absolute inset-x-0 z-30 mt-2 max-h-72 origin-top overflow-auto rounded-2xl border border-line bg-white p-1.5 shadow-[0_24px_48px_-16px_rgba(74,36,18,0.25)] focus:outline-none">
        <template x-for="(o, i) in {{ $options }}" :key="i">
            <li :id="'{{ $id }}-option-' + i" role="option" :aria-selected="{{ $selected }}"
                @click="{{ $pick }}; open = false; $refs.button.focus()" @mouseenter="active = i"
                class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors"
                :class="active === i ? 'bg-brand-soft' : ''">
                <span class="grid size-5 shrink-0 place-items-center rounded-full border-2 transition"
                      :class="{{ $selected }} ? 'border-brand bg-brand text-white' : 'border-line bg-white text-transparent'" aria-hidden="true">
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.8 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                </span>
                <span class="flex-1 font-semibold text-ink" x-text="{{ $label }}"></span>
                <span class="font-bold text-brand" x-text="{{ $meta }}"></span>
            </li>
        </template>
    </ul>
</div>
