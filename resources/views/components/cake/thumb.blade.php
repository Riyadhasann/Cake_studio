@props(['layers' => [], 'image' => null])

{{-- A small picture of one cake on the shop's table scene: the design's photo, else its layers as stripes in their flavor colors (top layer on top) --}}
<span {{ $attributes->merge(['class' => 'cake-scene relative block shrink-0 overflow-hidden rounded-lg border border-line shadow-box']) }} aria-hidden="true">
    @if ($image)
        <img src="{{ asset($image) }}" alt="" class="cake-scene-item" loading="lazy">
    @else
        <span class="absolute bottom-[19%] left-1/2 flex w-1/2 -translate-x-1/2 flex-col-reverse overflow-hidden rounded-sm shadow-[0_4px_8px_-4px_rgb(63_31_16/0.5)]">
            @foreach ($layers as $layer)
                <span class="block h-2.5" style="background-color: {{ $layer['sponge_color'] ?? '#c98b5b' }}"></span>
                <span class="block h-1" style="background-color: {{ $layer['cream_color'] ?? '#f6e3cf' }}"></span>
            @endforeach
        </span>
    @endif
</span>
