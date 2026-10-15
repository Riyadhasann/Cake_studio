{{--
    The texture in `url` (from the enclosing Alpine scope) drawn on one slot of a cake drawing, tiled at the
    same size as on the design page, so the admin sees it before saving. The other slots stay neutral.
    $drawings: CakeDrawings::all(), keyed by layer count. $type: sponge | cream. $id: unique per card.
--}}
@props(['drawings', 'type', 'id', 'color' => null])
@php
    $tile = \App\Models\CakeVariant::TEXTURE_TILE;
    $patternId = "texture-preview-{$id}";
    $firstLayers = array_key_first($drawings);
@endphp

<div class="mt-4 rounded-xl bg-petal p-3" x-data="{ layers: {{ $firstLayers }}, layerNo: 1 }">
    <div class="flex flex-wrap items-center gap-2 text-xs text-ink/70">
        <span>Preview on</span>
        <label class="sr-only" for="{{ $patternId }}-layers">Cake drawing</label>
        <select id="{{ $patternId }}-layers" x-model.number="layers" @change="layerNo = Math.min(layerNo, layers)"
                class="rounded-lg border border-line bg-white px-2 py-1">
            @foreach ($drawings as $layers => $drawing)
                <option value="{{ $layers }}">{{ $layers }} layers</option>
            @endforeach
        </select>
        <label class="sr-only" for="{{ $patternId }}-layer">Layer</label>
        <select id="{{ $patternId }}-layer" x-model.number="layerNo" class="rounded-lg border border-line bg-white px-2 py-1">
            @foreach (range(1, max(array_keys($drawings))) as $layerNo)
                <option value="{{ $layerNo }}" x-show="{{ $layerNo }} <= layers">Layer {{ $layerNo }} {{ $type }}</option>
            @endforeach
        </select>
        <span x-show="!url" class="text-ink/50">(no texture yet: showing the color)</span>
    </div>

    <svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
        <defs>
            <pattern id="{{ $patternId }}" patternUnits="userSpaceOnUse" width="{{ $tile }}" height="{{ $tile }}">
                <image :href="url ?? ''" width="{{ $tile }}" height="{{ $tile }}" preserveAspectRatio="xMidYMid slice" />
            </pattern>
        </defs>
    </svg>

    @foreach ($drawings as $layers => $drawing)
        <svg x-show="layers === {{ $layers }}" @if ($layers !== $firstLayers) x-cloak @endif
             viewBox="0 0 {{ $drawing['width'] }} {{ $drawing['height'] }}" class="mx-auto mt-3 block max-h-56 w-full"
             role="img" aria-label="The texture on a {{ $layers }}-layer cake">
            @if ($drawing['base_image'])
                <image href="{{ $drawing['base_image']['url'] }}" x="0" y="0" width="{{ $drawing['width'] }}" height="{{ $drawing['height'] }}" preserveAspectRatio="none" />
            @endif
            @foreach ($drawing['slots'] as $slot)
                @if ($slot['type'] === $type)
                    <path d="{{ $slot['path'] }}"
                          :fill="layerNo === {{ $slot['layer_no'] }} ? (url ? 'url(#{{ $patternId }})' : @js($color ?? '#d9c9b8')) : '#eadfd8'"
                          :stroke="layerNo === {{ $slot['layer_no'] }} ? '#905954' : 'none'" stroke-width="2" vector-effect="non-scaling-stroke" />
                @else
                    <path d="{{ $slot['path'] }}" fill="#fbf6f3" stroke="#eadfd8" vector-effect="non-scaling-stroke" />
                @endif
            @endforeach
            <g>{!! $drawing['base_svg'] !!}</g>
        </svg>
    @endforeach
</div>
