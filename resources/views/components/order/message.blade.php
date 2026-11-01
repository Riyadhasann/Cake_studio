@props(['message', 'color' => null])

@php
    // The icing color's name from the shop's palette (an order from before colors existed has none).
    $colorName = $color ? array_search(strtolower($color), array_map('strtolower', config('studio.message_colors')), true) : false;
@endphp

@if (filled($message))
    <span {{ $attributes }}>
        &ldquo;{{ $message }}&rdquo;
        @if ($color)
            <span class="ml-1 inline-flex items-center gap-1 whitespace-nowrap text-xs text-ink/70">
                <span class="inline-block size-3 rounded-full border border-line" style="background-color: {{ $color }}" aria-hidden="true"></span>
                {{ $colorName !== false ? $colorName : $color }} icing
            </span>
        @endif
    </span>
@endif
