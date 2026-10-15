{{--
    One published drawing (from CakeDrawings::published) inside the cakeStudio() Alpine scope: the cake photo
    if it has one, every slot filled from the customer's picks (the defaults first), the shading, then the base
    SVG on top. Only each slot's fill is bound, so changing a pick repaints just that slot.
--}}
@props(['layers', 'variant'])

<svg {{ $attributes->class(['cake-svg', 'cake-photo' => (bool) $variant['base_image']]) }}
     viewBox="0 0 {{ $variant['width'] }} {{ $variant['height'] }}" data-layers="{{ $layers }}" aria-hidden="true" focusable="false">
    @if ($variant['base_image'])
        {{-- The cake with an empty cut face; the slots below fill the cut --}}
        <image href="{{ $variant['base_image']['url'] }}" x="0" y="0" width="{{ $variant['width'] }}" height="{{ $variant['height'] }}" preserveAspectRatio="none" />
    @endif
    @foreach ($variant['slots'] as $slot)
        @php $call = (int) $slot['layer_no'].", '".e($slot['type'])."'"; @endphp {{-- e.g. 1, 'sponge' --}}
        @if ($variant['base_image'])
            {{-- The flavor's color under its art, so a see-through layer image never shows the empty cut --}}
            <path d="{{ $slot['path'] }}" class="cake-slot" :fill="slotColor({!! $call !!})" />
        @endif
        <path d="{{ $slot['path'] }}" class="cake-slot cake-slot-{{ $slot['type'] }}" :fill="slotFill({!! $call !!})" />
        @if ($variant['base_image'] && $slot['band'])
            {{-- A layer image whose art is one strip (e.g. a V-shaped slice): each arm of the strip is mapped onto
                 the same arm of this band, so the art fills it edge to edge and meets the next layer without a gap. --}}
            @php
                [$l0, $a0, $r0, $r1, $a1, $l1] = $slot['band'];
                $armId = "cake-arm-{$layers}-{$slot['layer_no']}-{$slot['type']}";
                $points = fn (array ...$p) => implode(' ', array_map(fn ($q) => $q[0].','.$q[1], $p));
            @endphp
            {{-- The arms overlap by a unit at the apex so no seam shows there --}}
            <clipPath id="{{ $armId }}-0"><polygon points="{{ $points($l0, [$a0[0] + 1, $a0[1]], [$a1[0] + 1, $a1[1]], $l1) }}" /></clipPath>
            <clipPath id="{{ $armId }}-1"><polygon points="{{ $points([$a0[0] - 1, $a0[1]], $r0, $r1, [$a1[0] - 1, $a1[1]]) }}" /></clipPath>
            <g x-show="slotStrip({!! $call !!})" pointer-events="none">
                @foreach ([0, 1] as $arm)
                    {{-- Clipped on a wrapper: a clip on the image itself would be moved by the image's transform --}}
                    <g clip-path="url(#{{ $armId }}-{{ $arm }})">
                        <image preserveAspectRatio="none"
                               :href="slotStrip({!! $call !!})?.url" :width="slotStrip({!! $call !!})?.width ?? 0" :height="slotStrip({!! $call !!})?.height ?? 0"
                               :transform="armTransform({!! $call !!}, {{ $arm }}, @js($slot['band']))" />
                    </g>
                @endforeach
            </g>
        @endif
        @if ($slot['type'] === 'sponge')
            <path d="{{ $slot['path'] }}" fill="url(#cake-crumbs)" pointer-events="none" x-show="!slotHasArt({!! $call !!})" />
        @endif
        {{-- Built-in light and shadow per band; not on a photo, where it would darken every joint between layers --}}
        @unless ($variant['shading'] || $variant['base_image'])
            <path d="{{ $slot['path'] }}" fill="url(#cake-shade-{{ $slot['type'] }})" pointer-events="none" />
        @endunless
    @endforeach
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
