{{--
    One published drawing (from CakeDrawings::published) inside the cakeStudio() Alpine scope: the cake photo
    if it has one, every slot filled from the customer's picks (the defaults first), the shading, then the base
    SVG on top. Only each slot's fill is bound, so changing a pick repaints just that slot.
--}}
@props(['layers', 'variant'])

@php
    $call = fn (array $slot): string => (int) $slot['layer_no'].", '".e($slot['type'])."'"; // e.g. 1, 'sponge'
    $band = fn (int $layerNo, string $type): ?array => collect($variant['slots'])->first(fn ($s) => $s['layer_no'] === $layerNo && $s['type'] === $type)['band'] ?? null;
    $points = fn (array ...$p) => implode(' ', array_map(fn ($q) => $q[0].','.$q[1], $p));
    // A cake photo whose layers were fitted into its cut: every slot is a band across the cut.
    $fitted = $variant['base_image'] && collect($variant['slots'])->every(fn ($slot) => $slot['band']) && $band($layers, 'cream') && $band(1, 'sponge');
@endphp

<svg {{ $attributes->class(['cake-svg', 'cake-photo' => (bool) $variant['base_image']]) }}
     viewBox="0 0 {{ $variant['width'] }} {{ $variant['height'] }}" data-layers="{{ $layers }}" aria-hidden="true" focusable="false">
    @if ($variant['base_image'])
        {{-- The cake with an empty cut face; the slots below fill the cut --}}
        <image href="{{ $variant['base_image']['url'] }}" x="0" y="0" width="{{ $variant['width'] }}" height="{{ $variant['height'] }}" preserveAspectRatio="none" />
    @endif

    @if ($fitted)
        {{--
            Layers in the cut of the photo, drawn like a real slice: a layer image keeps its own wavy or ragged edges,
            crumbs and shading. The layers are drawn from the top of the cake down, so each one's top edge (the crumbly
            or wavy one in a sliced layer) lies over the clean bottom of the layer above it. Behind every band is the
            color of the layer above, so the dips of a wavy edge read as that layer, never as a straight line.
            Only the outline of the whole cut clips the art, so nothing spills onto the cake around it.
        --}}
        @php
            [$top, $bottom] = [$band($layers, 'cream'), $band(1, 'sponge')];
            $cutId = "cake-cut-{$layers}";
            // Top of the cake first; in each layer the cream sits above the sponge.
            $topDown = collect($variant['slots'])->sortBy([['layer_no', 'desc'], fn ($a, $b) => ($a['type'] === 'cream' ? 0 : 1) <=> ($b['type'] === 'cream' ? 0 : 1)])->values();
            // The slot just above each one: a sponge's own cream, a cream's next sponge up; the top cream has none.
            $above = fn (array $slot): string => match (true) {
                $slot['type'] === 'sponge' => $slot['layer_no'].", 'cream'",
                $slot['layer_no'] < $layers => ($slot['layer_no'] + 1).", 'sponge'",
                default => $call($slot),
            };
        @endphp
        {{-- The two arms of the cut, overlapping by a unit at the apex so no seam shows there --}}
        <clipPath id="{{ $cutId }}-0"><polygon points="{{ $points($top[0], [$top[1][0] + 1, $top[1][1]], [$bottom[4][0] + 1, $bottom[4][1]], $bottom[5]) }}" /></clipPath>
        <clipPath id="{{ $cutId }}-1"><polygon points="{{ $points([$top[1][0] - 1, $top[1][1]], $top[2], $bottom[3], [$bottom[4][0] - 1, $bottom[4][1]]) }}" /></clipPath>

        @foreach ($topDown as $slot)
            <path d="{{ $slot['path'] }}" class="cake-slot" :fill="slotColor({!! $above($slot) !!})" />
        @endforeach

        @foreach ($topDown as $slot)
            {{-- Without a strip image: the slot's photo, cropped image or color, in its band --}}
            <path d="{{ $slot['path'] }}" class="cake-slot cake-slot-{{ $slot['type'] }}" :fill="slotFill({!! $call($slot) !!})" x-show="!slotStrip({!! $call($slot) !!})" />
            @if ($slot['type'] === 'sponge')
                <path d="{{ $slot['path'] }}" fill="url(#cake-crumbs)" pointer-events="none" x-show="!slotHasArt({!! $call($slot) !!})" />
            @endif
            {{-- With one: each arm of the strip mapped by its average edges onto the same arm of the band --}}
            <g x-show="slotStrip({!! $call($slot) !!})" pointer-events="none">
                @foreach ([0, 1] as $arm)
                    {{-- Clipped on a wrapper: a clip on the image itself would be moved by the image's transform --}}
                    <g clip-path="url(#{{ $cutId }}-{{ $arm }})">
                        <image preserveAspectRatio="none"
                               :href="slotStrip({!! $call($slot) !!})?.url" :width="slotStrip({!! $call($slot) !!})?.width ?? 0" :height="slotStrip({!! $call($slot) !!})?.height ?? 0"
                               :transform="armTransform({!! $call($slot) !!}, {{ $arm }}, @js($slot['band']))" />
                    </g>
                @endforeach
            </g>
        @endforeach
    @else
        @foreach ($variant['slots'] as $slot)
            <path d="{{ $slot['path'] }}" class="cake-slot cake-slot-{{ $slot['type'] }}" :fill="slotFill({!! $call($slot) !!})" />
            @if ($slot['type'] === 'sponge')
                <path d="{{ $slot['path'] }}" fill="url(#cake-crumbs)" pointer-events="none" x-show="!slotHasArt({!! $call($slot) !!})" />
            @endif
            {{-- Built-in light and shadow per band; not on a photo, where it would darken every joint between layers --}}
            @unless ($variant['shading'] || $variant['base_image'])
                <path d="{{ $slot['path'] }}" fill="url(#cake-shade-{{ $slot['type'] }})" pointer-events="none" />
            @endunless
        @endforeach
    @endif

    @if ($variant['shading'])
        {{-- The admin's grayscale light and shadow, multiplied over the slots only (white changes nothing) --}}
        <clipPath id="cake-slots-{{ $layers }}">
            @foreach ($variant['slots'] as $slot)
                <path d="{{ $slot['path'] }}" />
            @endforeach
        </clipPath>
        <image href="{{ $variant['shading']['url'] }}" x="0" y="0" width="{{ $variant['width'] }}" height="{{ $variant['height'] }}" preserveAspectRatio="none"
               clip-path="url(#cake-slots-{{ $layers }})" style="mix-blend-mode: multiply" pointer-events="none" />
    @endif
    {{-- Outline and decorations last, so the slots never cover them. Sanitized by CakeDrawings. --}}
    <g>{!! $variant['base_svg'] !!}</g>
</svg>
