{{--
    The cake preview on the design page. Lives inside the cakeStudio() Alpine scope.

    When the admin has published an SVG drawing for the chosen layer count, the cake is that drawing
    (x-cake-variant-svg): on the admin's cake photo when it has one, which then fills the stage,
    else between the plate and the frosting. Every slot is filled with the flavor's photo for that slot,
    else its layer image, else its color, then shaded (the drawing's shading image,
    or soft gradients). Otherwise the cake is stacked from one element per sponge and cream (the layered preview).
    SVG content is rendered in Blade because Alpine's <template x-for> doesn't work inside <svg>.
--}}
@props(['sponges', 'creams', 'variants'])
@php
    $flavorsByType = ['sponge' => $sponges, 'cream' => $creams];
@endphp

<style>
    /* Layered cake preview. Every sponge and every cream is its own element,
       coloured from its flavor, so changing one never touches another.
       Sizes use cqw (1% of the preview width) so the cake scales with the screen. */
    .cake-stage {
        container-type: inline-size;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        aspect-ratio: 4 / 5;
        padding-bottom: 8%;
        background: radial-gradient(120% 80% at 50% 100%, #fffaf5 0%, #fbeee2 55%, #f3dcc8 100%);
        overflow: hidden;
    }
    .cake-stack {
        display: flex;
        flex-direction: column-reverse; /* first child sits at the bottom */
        align-items: center;
        width: 66%;
    }
    .cake-layers {
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        width: 100%;
    }
    .cake-plate {
        width: 138%;
        height: 5cqw;
        margin-top: -1cqw;
        border-radius: 999px;
        background: linear-gradient(#ffffff, #e2d3cb);
        border: 1px solid rgba(59, 38, 34, .12);
        box-shadow: 0 2cqw 3.5cqw rgba(80, 45, 40, .20);
    }
    .cake-layer {
        display: flex;
        flex-direction: column-reverse; /* sponge at the bottom, its cream on top */
        width: 100%;
        animation: cake-layer-in .35s ease-out;
    }
    .cake-sponge {
        position: relative;
        height: 14cqw;
        border-radius: 2.2cqw;
        overflow: hidden;
        background-color: #d9c9b8;
        background-image:
            radial-gradient(circle at 18% 28%, rgba(0, 0, 0, .10) 1.3px, transparent 1.8px),
            radial-gradient(circle at 63% 58%, rgba(255, 255, 255, .22) 1.1px, transparent 1.7px),
            radial-gradient(circle at 82% 22%, rgba(0, 0, 0, .07) 1px, transparent 1.6px),
            radial-gradient(circle at 40% 82%, rgba(255, 255, 255, .16) 1px, transparent 1.6px);
        background-size: 17px 15px, 13px 17px, 21px 13px, 15px 19px;
        box-shadow: inset 0 -2.2cqw 2.8cqw rgba(0, 0, 0, .10), inset 0 1.4cqw 2.2cqw rgba(255, 255, 255, .14);
        transition: background-color .25s ease;
    }
    .cake-cream {
        position: relative;
        z-index: 1;
        height: 5.6cqw;
        width: calc(100% + 4cqw);
        margin: 0 -2cqw;
        border-radius: 999px;
        overflow: hidden;
        background-color: #fff3d1;
        background-image: linear-gradient(to bottom, rgba(255, 255, 255, .45), rgba(255, 255, 255, 0) 55%);
        box-shadow: 0 .6cqw 1cqw rgba(60, 30, 20, .20), inset 0 -1cqw 1.2cqw rgba(0, 0, 0, .08);
        transition: background-color .25s ease;
    }
    .cake-sponge img,
    .cake-cream img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .cake-frosting {
        display: block;
        position: relative;
        z-index: 2;
        width: 108%;
        margin-bottom: -1cqw;
        filter: drop-shadow(0 .8cqw 1cqw rgba(60, 30, 20, .16));
    }
    /* An uploaded image replaces the drawn sponge or cream. */
    .cake-sponge.has-image,
    .cake-cream.has-image {
        background-color: transparent !important;
        background-image: none;
        box-shadow: none;
        border-radius: 0;
    }
    .cake-plate-img {
        display: block;
        width: 138%;
        max-height: 8cqw;
        margin-top: -1cqw;
        object-fit: contain;
    }
    .cake-frosting-img {
        display: block;
        position: relative;
        z-index: 2;
        width: 108%;
        max-height: 24cqw;
        margin-bottom: -1cqw;
        object-fit: contain;
        object-position: center bottom;
    }
    /* SVG drawing: its full width is the cream's width, 2cqw wider than the sponge on each side. */
    .cake-defs {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }
    .cake-svg {
        display: block;
        position: relative;
        z-index: 1;
        width: calc(100% + 4cqw);
        height: auto;
        margin: 0 -2cqw;
        overflow: visible;
        animation: cake-layer-in .35s ease-out;
    }
    /* A drawing on a cake photo is the whole cake: the stage takes the photo's proportions. */
    .cake-stage.is-photo {
        display: block;
        aspect-ratio: auto;
        padding: 0;
        background: #fff;
    }
    .cake-svg.cake-photo {
        width: 100%;
        margin: 0;
    }
    /* On a photo the layers sit flush on each other: no shadow under the cream. */
    .cake-photo .cake-slot-cream {
        filter: none;
    }
    .cake-slot {
        transition: fill .25s ease;
    }
    .cake-slot-cream {
        filter: drop-shadow(0 .6cqw 1cqw rgba(60, 30, 20, .20));
    }
    @keyframes cake-layer-in {
        from { opacity: 0; transform: translateY(-3cqw); }
        to   { opacity: 1; transform: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .cake-layer, .cake-svg { animation: none; }
        .cake-sponge, .cake-cream, .cake-slot { transition: none; }
    }
</style>

<div class="overflow-hidden rounded-2xl border border-line bg-white shadow-[0_6px_16px_-8px_rgba(74,36,18,0.25)]">
    <div class="cake-stage" :class="{ 'is-photo': hasPhotoBase }" role="img" :aria-label="previewLabel">
        @if ($variants)
            {{-- Fills shared by every drawing: one image pattern per flavor that has a layer image. --}}
            <svg class="cake-defs" aria-hidden="true" focusable="false">
                <defs>
                    @foreach ($flavorsByType as $type => $flavors)
                        @foreach ($flavors as $flavor)
                            @if ($flavor['image'])
                                <x-cake-cover-pattern :id="'cake-image-'.$type.'-'.$flavor['id']" :url="$flavor['image']" :size="$flavor['image_size']" />
                            @endif
                        @endforeach
                    @endforeach

                    {{-- Photos of one flavor for one slot of one drawing --}}
                    @foreach ($variants as $variant)
                        @foreach ($variant['photos'] as $photo)
                            <x-cake-cover-pattern :id="'cake-photo-'.$photo['id']" :url="$photo['url']" :size="[$photo['width'], $photo['height']]" />
                        @endforeach
                    @endforeach

                    {{-- Light from above, like the layered preview's inset shadows --}}
                    <linearGradient id="cake-shade-sponge" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#fff" stop-opacity=".14" />
                        <stop offset=".3" stop-color="#fff" stop-opacity="0" />
                        <stop offset=".7" stop-color="#000" stop-opacity="0" />
                        <stop offset="1" stop-color="#000" stop-opacity=".12" />
                    </linearGradient>
                    <linearGradient id="cake-shade-cream" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#fff" stop-opacity=".45" />
                        <stop offset=".55" stop-color="#fff" stop-opacity="0" />
                        <stop offset="1" stop-color="#000" stop-opacity=".08" />
                    </linearGradient>
                    {{-- Crumbs for a sponge that is only a color --}}
                    <pattern id="cake-crumbs" patternUnits="userSpaceOnUse" width="34" height="30">
                        <circle cx="6" cy="8" r="1.6" fill="#000" fill-opacity=".10" />
                        <circle cx="22" cy="18" r="1.4" fill="#fff" fill-opacity=".22" />
                        <circle cx="28" cy="5" r="1.2" fill="#000" fill-opacity=".07" />
                        <circle cx="13" cy="25" r="1.2" fill="#fff" fill-opacity=".16" />
                    </pattern>
                </defs>
            </svg>
        @endif

        {{-- A drawing on a cake photo: the photo is the whole cake (plate and top included), so it fills the stage --}}
        @foreach ($variants as $layers => $variant)
            @if ($variant['base_image'])
                <template x-if="hasVariant && layerCount === {{ $layers }}">
                    <x-cake-variant-svg :layers="$layers" :variant="$variant" />
                </template>
            @endif
        @endforeach

        <div class="cake-stack" x-show="!hasPhotoBase">
            <template x-if="partImage('plate')">
                <img class="cake-plate-img" :src="partImage('plate')" alt="" @@error="markBroken($el.getAttribute('src'))">
            </template>
            <template x-if="!partImage('plate')">
                <div class="cake-plate"></div>
            </template>

            {{-- The admin's drawing for this layer count --}}
            @foreach ($variants as $layers => $variant)
                @unless ($variant['base_image'])
                    <template x-if="hasVariant && layerCount === {{ $layers }}">
                        <x-cake-variant-svg :layers="$layers" :variant="$variant" />
                    </template>
                @endunless
            @endforeach

            {{-- No drawing for this layer count: one element per sponge and per cream --}}
            <template x-if="!hasVariant">
                <div class="cake-layers">
                    <template x-for="(layer, index) in layers" :key="index">
                        <div class="cake-layer">
                            <div class="cake-sponge" :class="{ 'has-image': flavorImage(sponges, layer.sponge_id) }" :style="{ backgroundColor: flavorColor(sponges, layer.sponge_id) }">
                                <template x-if="flavorImage(sponges, layer.sponge_id)">
                                    <img :src="flavorImage(sponges, layer.sponge_id)" alt="" @@error="markBroken($el.getAttribute('src'))">
                                </template>
                            </div>
                            <div class="cake-cream" :class="{ 'has-image': flavorImage(creams, layer.cream_id) }" :style="{ backgroundColor: flavorColor(creams, layer.cream_id) }">
                                <template x-if="flavorImage(creams, layer.cream_id)">
                                    <img :src="flavorImage(creams, layer.cream_id)" alt="" @@error="markBroken($el.getAttribute('src'))">
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Frosting on top: your uploaded image, or the default drawing. Never changes with flavors. --}}
            <template x-if="partImage('frosting')">
                <img class="cake-frosting-img" :src="partImage('frosting')" alt="" @@error="markBroken($el.getAttribute('src'))">
            </template>
            <template x-if="!partImage('frosting')">
                <svg class="cake-frosting" viewBox="0 0 320 70" aria-hidden="true">
                    <g fill="#fff8f1">
                        <rect x="8" y="46" width="304" height="24" rx="12"/>
                        <circle cx="34" cy="48" r="28"/>
                        <circle cx="86" cy="42" r="32"/>
                        <circle cx="140" cy="38" r="34"/>
                        <circle cx="198" cy="38" r="34"/>
                        <circle cx="252" cy="42" r="32"/>
                        <circle cx="288" cy="48" r="28"/>
                    </g>
                </svg>
            </template>
        </div>
    </div>
</div>
