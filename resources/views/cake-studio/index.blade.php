<x-studio-layout title="Design your cake" :step="1" description="Design a custom cake: pick the size, 2 to 4 layers, a sponge and cream for each, a finished design and a message. Live preview and price.">
    @vite('resources/js/cake-3d.js')


    <div
        x-data="cakeStudio({
            designs: @js($designs),
            sizes: @js($sizes),
            sponges: @js($sponges),
            creams: @js($creams),
            parts: @js($parts),
            variantLayers: @js(array_keys($variants)),
            variantBases: @js(collect($variants)->map(fn ($variant) => $variant['base_image']['url'] ?? null)->filter()),
            variantPhotos: @js(collect($variants)->map(fn ($variant) => collect($variant['photos'])->map(fn ($photo) => ['id' => $photo['id'], 'url' => $photo['url']]))),
            defaultSpongeId: @js($defaultSpongeId),
            defaultCreamId: @js($defaultCreamId),
            addUrl: @js(route('cart.add')),
            cartUrl: @js(route('cart.index')),
            initialDesignId: @js(request()->integer('design') ?: null),
        })"
        @keydown.escape.window="showModal = false"
        class="pb-24 md:pb-0"
    >
        {{-- Hero: what this page does, and what there is to choose from --}}
        <div class="title-banner" data-reveal>
            <div class="mx-auto max-w-6xl px-4 sm:px-6"><div class="max-w-2xl">
                <p class="eyebrow">Cake Studio</p>
                <h1 class="heading mt-3 text-4xl sm:text-5xl">Design your <span class="text-brand"> cake</span></h1>
                <p class="mt-4 text-base font-medium leading-7 text-ink/80">
                    Stack 2 to 4 layers, choose a sponge and a cream for each, add a design and a message, then pick a size. The cake changes as you choose.
                </p>
            </div></div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-6" role="status">{{ session('success') }}</div>
        @endif

        @if ($placed = session('order_placed'))
            <div class="alert alert-success mt-6 flex flex-wrap items-center justify-between gap-3" role="status">
                <p>
                    <span class="font-bold">Order #{{ $placed['id'] }} placed.</span>
                    Total ৳{{ number_format($placed['total'], 2) }}{{ $placed['delivery_date'] ? ', delivery '.$placed['delivery_date'] : '' }}.
                    Keep your order number to check its status.
                </p>
                <a href="{{ route('orders.track', ['order_id' => $placed['id']]) }}" class="btn-outline btn-sm">Track order</a>
            </div>
        @endif

        <div class="mt-8 grid grid-cols-1 items-start gap-5 lg:grid-cols-12 lg:gap-6">

            {{-- The cake as chosen so far, and (tablets and up) a summary with the price and the cart button: stacked on phones, side by side on tablets, a sticky left column on laptops --}}
            <aside class="min-w-0 space-y-5 md:grid md:grid-cols-2 md:items-start md:gap-5 md:space-y-0 lg:sticky lg:top-20 lg:col-span-5 lg:block lg:space-y-5">
                <div class="panel mx-auto w-full max-w-sm p-4 md:max-w-none">
                    <div class="mb-3 flex items-center justify-between gap-3 px-1">
                        <p class="flex items-center gap-2 text-sm font-bold text-ink">
                            <span class="relative flex size-2.5" aria-hidden="true">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand/50 motion-reduce:animate-none"></span>
                                <span class="relative inline-flex size-2.5 rounded-full bg-brand"></span>
                            </span>
                            Live preview
                        </p>

                        {{-- Photo, or 3D (when this browser can draw 3D; a picked design is shown there). Without 3D, a picked design's photo. --}}
                        <div x-show="can3d || selectedDesign" x-cloak class="inline-flex rounded-lg border border-line bg-petal p-0.5 text-xs font-semibold" role="group" aria-label="Cake preview">
                            <button type="button" x-show="selectedDesign && !can3d" @click="view = 'design'" :aria-pressed="view === 'design'"
                                    class="rounded-md px-3 py-1 transition" :class="view === 'design' ? 'bg-white text-brand shadow-sm' : 'text-ink/75 hover:text-brand'">Design</button>
                            <button type="button" @click="show2d()" :aria-pressed="view === '2d'"
                                    class="rounded-md px-3 py-1 transition" :class="view === '2d' ? 'bg-white text-brand shadow-sm' : 'text-ink/75 hover:text-brand'">Photo</button>
                            <button type="button" x-show="can3d" @click="show3d()" :aria-pressed="view === '3d'"
                                    class="rounded-md px-3 py-1 transition" :class="view === '3d' ? 'bg-white text-brand shadow-sm' : 'text-ink/75 hover:text-brand'">3D</button>
                        </div>
                    </div>

                    {{-- Without 3D: the picked design, exactly as photographed. With a full turn of photos, dragging (or the arrow keys) spins it. --}}
                    <div x-show="view === 'design' && selectedDesign" x-cloak class="cake-scene relative overflow-hidden rounded-lg border border-line shadow-box" style="aspect-ratio: 1;">
                        <img :src="designFrameUrl" :alt="selectedDesign ? selectedDesign.name + ' cake design' : ''" draggable="false"
                             class="cake-scene-item select-none" :class="spinnable && 'cursor-grab active:cursor-grabbing'"
                             :tabindex="spinnable ? 0 : -1" style="touch-action: pan-y;"
                             @pointerdown="startSpin($event)" @pointermove="spin($event)" @pointerup="endSpin($event)" @pointercancel="endSpin($event)"
                             @keydown.arrow-left.prevent="turnDesign(-1)" @keydown.arrow-right.prevent="turnDesign(1)">
                    </div>

                    {{-- The admin's SVG drawing for this layer count, or one element per sponge and per cream --}}
                    <div x-show="view === '2d'" class="cake-float">
                        <div data-tilt>
                            <x-cake-preview :sponges="$sponges" :creams="$creams" :variants="$variants" />
                        </div>
                    </div>

                    {{-- The 3D cake: drag (or use the arrow keys) to turn it. A picked design is wrapped round it and turns by itself. --}}
                    <div x-show="view === '3d'" x-cloak class="stage-3d relative overflow-hidden rounded-lg border border-line shadow-box"
                         style="aspect-ratio: 4 / 5;">
                        <div x-ref="cake3d" class="absolute inset-0 cursor-grab focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand active:cursor-grabbing"
                             tabindex="0" role="group" aria-roledescription="3D cake" :aria-label="previewLabel + '. Drag, or use the arrow keys, to turn it.'"
                             x-effect="if (view === '3d' && ready3d) render3d(spec3d)"></div>
                        <p x-show="loading3d" class="pointer-events-none absolute inset-0 grid place-items-center text-sm font-semibold text-ink">Loading the 3D cake…</p>
                        <div x-show="!selectedDesign" class="absolute inset-x-0 bottom-3 flex justify-center gap-2" role="group" aria-label="Camera">
                            @foreach (['front' => 'Front', 'angle' => 'Angle', 'top' => 'Top'] as $name => $label)
                                <button type="button" @click="setView3d(@js($name))"
                                        class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-ink shadow-sm transition hover:text-brand">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <p x-show="error3d" x-text="error3d" x-cloak role="alert" class="mt-3 text-center text-xs font-medium text-clay"></p>
                </div>

                <div class="panel hidden p-5 md:block">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold">Your cake</h2>
                        <div class="flex gap-1.5 text-xs font-bold">
                            <span class="chip" x-text="selectedSize?.label ?? ''"></span>
                            <span class="chip" x-text="layerCount + ' layers'"></span>
                        </div>
                    </div>

                    {{-- The picked design --}}
                    <div class="mt-4 flex items-center gap-3 border-y border-line py-2.5 text-sm">
                        <span class="w-16 shrink-0 text-xs font-semibold text-brown">Design</span>
                        <template x-if="selectedDesign">
                            <img :src="selectedDesign.frames[0]" alt="" class="cake-scene size-8 rounded-md border border-line object-contain px-1 pt-0.5 pb-1">
                        </template>
                        <span class="font-medium" x-text="selectedDesign?.name ?? 'Plain'"></span>
                    </div>

                    {{-- Listed top to bottom, like the cake in the picture --}}
                    <ol class="divide-y divide-line text-sm" aria-label="Layers, from the top">
                        <template x-for="row in layersTopDown" :key="row.index">
                            <li class="flex flex-wrap items-center gap-x-3 gap-y-1 py-2.5">
                                <span class="w-16 shrink-0 text-xs font-semibold text-brown" x-text="'Layer ' + (row.index + 1)"></span>
                                <span class="inline-flex items-center gap-1.5 font-medium">
                                    <span class="size-3.5 rounded-full border border-black/10" :style="{ backgroundColor: flavorColor(sponges, row.layer.sponge_id) }"></span>
                                    <span x-text="flavorName(sponges, row.layer.sponge_id)"></span>
                                </span>
                                <span class="text-ink/50" aria-hidden="true">+</span>
                                <span class="inline-flex items-center gap-1.5 font-medium">
                                    <span class="size-3.5 rounded-full border border-black/10" :style="{ backgroundColor: flavorColor(creams, row.layer.cream_id) }"></span>
                                    <span x-text="flavorName(creams, row.layer.cream_id)"></span>
                                </span>
                            </li>
                        </template>
                    </ol>

                    <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert"
                       class="alert alert-error mt-4"></p>

                    <div class="inset-box mt-4 flex items-end justify-between gap-4 px-4 py-3" aria-live="polite" aria-atomic="true">
                        <div>
                            <p class="text-xs font-semibold text-brown">Total</p>
                            <p class="mt-1 text-2xl font-extrabold leading-none text-ink">৳<span x-effect="countTo($el, totalPrice, format)"></span></p>
                        </div>
                        <p class="text-sm font-semibold text-ink/80" x-text="quantity + ' × ৳' + format(unitPrice)"></p>
                    </div>

                    {{-- How many cakes, then add them --}}
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-brown">Cakes</span>
                        <div class="mr-auto inline-flex shrink-0 items-center rounded-lg border-2 border-brand bg-brand-soft p-0.5 shadow-brand" role="group" aria-label="How many cakes">
                            <button type="button" @click="decrement()" :disabled="quantity <= 1" aria-label="Decrease quantity"
                                class="grid size-8 place-items-center rounded-md bg-white text-lg font-bold text-brand-dark shadow-box transition hover:bg-brand hover:text-white disabled:bg-transparent disabled:text-ink/30 disabled:shadow-none disabled:hover:text-ink/30">&minus;</button>
                            <span class="w-8 text-center text-base font-extrabold text-brand-dark" x-text="quantity" aria-live="polite"></span>
                            <button type="button" @click="increment()" :disabled="quantity >= maxQuantity" aria-label="Increase quantity"
                                class="grid size-8 place-items-center rounded-md bg-white text-lg font-bold text-brand-dark shadow-box transition hover:bg-brand hover:text-white disabled:bg-transparent disabled:text-ink/30 disabled:shadow-none disabled:hover:text-ink/30">+</button>
                        </div>
                        <button type="button" @click="openConfirm()" class="btn btn-sm">
                            Add to Cart
                            <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                    <p class="mt-3 text-center text-xs text-ink/70">You can still review everything in your cart before you order.</p>
                </div>
            </aside>

            {{-- Right: the choices, as numbered steps --}}
            <div class="min-w-0 lg:col-span-7">
                <div class="space-y-5">
                    <section id="step-layers" class="panel scroll-mt-20 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-5 shrink-0 place-items-center rounded-full bg-cocoa text-[0.65rem] font-bold text-white" aria-hidden="true">1</span>
                                    <h2 id="layers-heading" class="text-base font-bold leading-none">How many layers?</h2>
                                </div>
                                <p class="mt-1.5 text-sm text-ink/70">More layers make a taller cake. The price stays the same.</p>
                            </div>
                            <span class="chip shrink-0" x-text="layerCount + ' layers'"></span>
                        </div>
                        <div class="mt-4">
                            <x-studio-dropdown id="layers-picker" labelledby="layers-heading" icon="layers"
                                               options="layerOptions" selected="layerCount === o" pick="setLayerCount(o)"
                                               label="o + ' layers'" meta="['', '', 'Classic', 'Tall', 'Extra tall'][o]"
                                               current="layerCount + ' layers'" current-meta="['', '', 'Classic', 'Tall', 'Extra tall'][layerCount]" />
                        </div>
                    </section>

                    <section id="step-flavors" class="panel scroll-mt-20 p-5">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="step-dot grid size-5 shrink-0 place-items-center rounded-full bg-cocoa text-[0.65rem] font-bold text-white" aria-hidden="true">2</span>
                                <h2 class="text-base font-bold leading-none">Pick the flavors</h2>
                            </div>
                            <p class="mt-1.5 text-sm text-ink/70">A sponge and a cream for each layer. Layer 1 is at the bottom.</p>
                        </div>

                        {{-- One tab per layer, with its two flavors as dots --}}
                        <div class="mt-4 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Layers">
                            <template x-for="(layer, index) in layers" :key="index">
                                <button type="button" role="tab" :id="'layer-tab-' + index" :aria-selected="activeLayer === index" :aria-controls="'layer-panel-' + index"
                                        :tabindex="activeLayer === index ? 0 : -1" @click="activeLayer = index"
                                        @keydown.arrow-right.prevent="focusLayerTab(index + 1)" @keydown.arrow-left.prevent="focusLayerTab(index - 1)"
                                        class="flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                                        :class="activeLayer === index ? 'border-brand bg-brand text-white' : 'border-line bg-white text-ink hover:border-brand/50 hover:text-brand'">
                                    <span x-text="layerTitle(index)"></span>
                                    <span class="flex -space-x-1" aria-hidden="true">
                                        <span class="size-3.5 rounded-full border-2 border-white" :style="{ backgroundColor: flavorColor(sponges, layer.sponge_id) }"></span>
                                        <span class="size-3.5 rounded-full border-2 border-white" :style="{ backgroundColor: flavorColor(creams, layer.cream_id) }"></span>
                                    </span>
                                </button>
                            </template>
                        </div>

                        {{-- The picked layer's sponge and cream. Removed layers disappear from here and from the data. --}}
                        <template x-for="(layer, index) in layers" :key="index">
                            <div x-show="activeLayer === index" role="tabpanel" :id="'layer-panel-' + index" :aria-labelledby="'layer-tab-' + index"
                                 class="inset-box mt-3 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-ink">
                                        <span x-text="flavorName(sponges, layer.sponge_id)"></span> sponge
                                        <span class="text-ink/80">with</span>
                                        <span x-text="flavorName(creams, layer.cream_id)"></span>
                                    </p>
                                    <button type="button" x-show="layers.length > 1" @click="copyToAll(index)"
                                            class="btn-outline btn-sm">
                                        Use these on all layers
                                    </button>
                                </div>

                                <p class="mt-4 text-xs font-bold text-brown" aria-hidden="true">Sponge</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3" role="group" :aria-label="layerTitle(index) + ': sponge'">
                                    <template x-for="sponge in sponges" :key="sponge.id">
                                        <button type="button" @click="setSponge(index, sponge.id)" :aria-pressed="layer.sponge_id === sponge.id"
                                                class="choice flex items-center gap-2 px-2.5 py-1.5">
                                            <span class="size-3.5 shrink-0 rounded-full border-2 border-white shadow-[0_0_0_1px_color-mix(in_srgb,var(--color-text)_20%,transparent)]" :style="{ backgroundColor: flavorColor(sponges, sponge.id) }"></span>
                                            <span class="min-w-0 flex-1 truncate leading-tight font-semibold" x-text="sponge.name"></span>
                                        </button>
                                    </template>
                                </div>

                                <p class="mt-5 text-xs font-bold text-brown" aria-hidden="true">Cream</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3" role="group" :aria-label="layerTitle(index) + ': cream'">
                                    <template x-for="cream in creams" :key="cream.id">
                                        <button type="button" @click="setCream(index, cream.id)" :aria-pressed="layer.cream_id === cream.id"
                                                class="choice flex items-center gap-2 px-2.5 py-1.5">
                                            <span class="size-3.5 shrink-0 rounded-full border-2 border-white shadow-[0_0_0_1px_color-mix(in_srgb,var(--color-text)_20%,transparent)]" :style="{ backgroundColor: flavorColor(creams, cream.id) }"></span>
                                            <span class="min-w-0 flex-1 truncate leading-tight font-semibold" x-text="cream.name"></span>
                                        </button>
                                    </template>
                                </div>

                                <div x-show="index < layers.length - 1" class="mt-4 flex justify-end">
                                    <button type="button" @click="focusLayerTab(index + 1)" class="btn-outline btn-sm">
                                        Next: <span x-text="layerTitle(index + 1)"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </section>

                    <section id="step-design" class="panel scroll-mt-20 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-5 shrink-0 place-items-center rounded-full bg-cocoa text-[0.65rem] font-bold text-white" aria-hidden="true">3</span>
                                    <h2 id="design-heading" class="text-base font-bold leading-none">Pick a design</h2>
                                </div>
                                <p class="mt-1.5 text-sm text-ink/70">The finished look outside. You still choose the layers inside.</p>
                            </div>
                            <span class="chip shrink-0" x-text="selectedDesign?.name ?? 'Plain'"></span>
                        </div>

                        <div class="relative mt-4" x-data="{ atStart: true, atEnd: false, update() { const el = this.$refs.track; this.atStart = el.scrollLeft < 4; this.atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4; }, slide(dir) { this.$refs.track.scrollBy({ left: dir * this.$refs.track.clientWidth * 0.8, behavior: 'smooth' }); } }" x-init="$nextTick(() => update())">
                            <div x-ref="track" @scroll.debounce.50ms="update()" class="slider flex gap-3 overflow-x-auto pb-1" role="group" aria-labelledby="design-heading">
                                {{-- No design: the plain layered cake --}}
                                <button type="button" @click="selectDesign(null)" :aria-pressed="designId === null" class="choice flex w-32 shrink-0 flex-col p-1.5 text-center">
                                    <span class="cake-scene relative block aspect-square w-full overflow-hidden rounded-md" aria-hidden="true">
                                        <span class="absolute bottom-[19%] left-1/2 flex w-[42%] -translate-x-1/2 flex-col-reverse gap-0.5">
                                            <template x-for="i in 3" :key="i">
                                                <span class="flex flex-col-reverse gap-px"><span class="h-2 rounded-sm bg-brown/60"></span><span class="h-1 rounded-full bg-gold"></span></span>
                                            </template>
                                        </span>
                                    </span>
                                    <span class="mt-1.5 block truncate text-xs font-semibold">Plain</span>
                                    <span class="block text-xs font-normal text-ink/65">Just your layers</span>
                                </button>
                                <template x-for="design in designs" :key="design.id">
                                    <button type="button" @click="selectDesign(design.id)" :aria-pressed="designId === design.id" class="choice flex w-32 shrink-0 flex-col p-1.5 text-center">
                                        <span class="cake-scene relative block aspect-square w-full overflow-hidden rounded-md">
                                            <img :src="design.frames[0]" :alt="design.name" class="cake-scene-item" loading="lazy">
                                            <span x-show="design.frames.length > 1" class="absolute top-1 right-1 rounded bg-white/95 px-1.5 py-0.5 text-[0.6rem] font-bold text-ink shadow-sm">360°</span>
                                        </span>
                                        <span class="mt-1.5 block truncate text-xs font-semibold" x-text="design.name" :title="design.name"></span>
                                        <span class="block text-xs font-normal text-ink/65" x-text="Number(design.extra_price) > 0 ? '+৳' + Number(design.extra_price).toLocaleString('en-US') : 'Included'"></span>
                                    </button>
                                </template>
                            </div>
                            <button type="button" x-show="!atStart" x-transition.opacity @click="slide(-1)" aria-label="Previous designs"
                                    class="absolute top-1/2 -left-2 grid size-7 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-md hover:text-brand">
                                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.8 4.2a1 1 0 0 1 0 1.4L8.4 10l4.4 4.4a1 1 0 0 1-1.4 1.4l-5.1-5.1a1 1 0 0 1 0-1.4l5.1-5.1a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </button>
                            <button type="button" x-show="!atEnd" x-transition.opacity @click="slide(1)" aria-label="More designs"
                                    class="absolute top-1/2 -right-2 grid size-7 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-md hover:text-brand">
                                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.2 15.8a1 1 0 0 1 0-1.4l4.4-4.4-4.4-4.4a1 1 0 1 1 1.4-1.4l5.1 5.1a1 1 0 0 1 0 1.4l-5.1 5.1a1 1 0 0 1-1.4 0Z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </section>

                    <section id="step-message" class="panel scroll-mt-20 p-5">
                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-5 shrink-0 place-items-center rounded-full bg-cocoa text-[0.65rem] font-bold text-white" aria-hidden="true">4</span>
                                    <label for="message" class="text-base font-bold leading-none">Message on the cake</label>
                                </div>
                                <span class="text-xs text-ink/65" x-text="message.length + '/255'"></span>
                            </div>
                            <p class="mt-1.5 text-sm text-ink/70">Optional. Leave it empty for no writing.</p>
                        </div>
                        <textarea id="message" x-model="message" maxlength="255" rows="2" placeholder="e.g. Happy Birthday, Mina!" class="field mt-4"></textarea>
                        <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Message ideas">
                            @foreach (['Happy Birthday!', 'Congratulations!', 'Happy Anniversary!', 'With love'] as $idea)
                                <button type="button" @click="message = @js($idea)"
                                        class="btn-outline btn-sm">{{ $idea }}</button>
                            @endforeach
                        </div>
                    </section>

                    <section id="step-size" class="panel scroll-mt-20 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-5 shrink-0 place-items-center rounded-full bg-cocoa text-[0.65rem] font-bold text-white" aria-hidden="true">5</span>
                                    <h2 id="size-heading" class="text-base font-bold leading-none">Choose a size</h2>
                                </div>
                                <p class="mt-1.5 text-sm text-ink/70">The size sets the starting price.</p>
                            </div>
                            <span class="chip shrink-0" x-text="selectedSize?.label ?? ''"></span>
                        </div>
                        <div class="mt-4" role="group" aria-labelledby="size-heading">
                            <x-studio-dropdown id="size-picker" labelledby="size-heading" icon="size"
                                               options="sizes" selected="sizeId === o.id" pick="sizeId = o.id"
                                               label="o.label" meta="'৳' + Number(o.price).toLocaleString('en-US')"
                                               current="selectedSize?.label ?? 'Choose a size'"
                                               current-meta="selectedSize ? 'From ৳' + Number(selectedSize.price).toLocaleString('en-US') : ''" />
                        </div>
                    </section>
                </div>
            </div>
        </div>

        {{-- Phones: the price and the cart button stay at the bottom of the screen --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 px-4 py-3 shadow-[0_-8px_24px_-10px_color-mix(in_srgb,var(--color-text)_30%,transparent)] backdrop-blur md:hidden">
            <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert" class="mb-2 text-sm font-medium text-clay"></p>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-ink/80" x-text="(selectedSize?.label ?? '') + ' · ' + layerCount + ' layers · ' + quantity + ' × ৳' + format(unitPrice)"></p>
                    <p class="text-xl font-extrabold leading-tight text-ink">৳<span x-effect="countTo($el, totalPrice, format)"></span></p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <div class="inline-flex shrink-0 items-center rounded-lg border-2 border-brand bg-brand-soft p-0.5 shadow-brand" role="group" aria-label="How many cakes">
                        <button type="button" @click="decrement()" :disabled="quantity <= 1" aria-label="Decrease quantity"
                            class="grid size-8 place-items-center rounded-md bg-white text-lg font-bold text-brand-dark shadow-box transition hover:bg-brand hover:text-white disabled:bg-transparent disabled:text-ink/30 disabled:shadow-none disabled:hover:text-ink/30">&minus;</button>
                        <span class="w-8 text-center text-base font-extrabold text-brand-dark" x-text="quantity" aria-live="polite"></span>
                        <button type="button" @click="increment()" :disabled="quantity >= maxQuantity" aria-label="Increase quantity"
                            class="grid size-8 place-items-center rounded-md bg-white text-lg font-bold text-brand-dark shadow-box transition hover:bg-brand hover:text-white disabled:bg-transparent disabled:text-ink/30 disabled:shadow-none disabled:hover:text-ink/30">+</button>
                    </div>
                    <button type="button" @click="openConfirm()" class="btn">Add to Cart</button>
                </div>
            </div>
        </div>

        {{-- Confirmation modal --}}
        <div
            x-show="showModal"
            x-cloak
            x-transition.opacity
            @click.self="showModal = false"
            class="fixed inset-0 z-50 grid place-items-center bg-ink/60 p-4"
            @keydown.tab="trapFocus($event)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-title"
        >
            <div x-ref="dialog" class="dialog-pop max-h-[90vh] w-full max-w-sm overflow-y-auto rounded-xl bg-white p-5 shadow-xl">
                                <h2 id="confirm-title" class="text-lg font-bold">Add this cake to your cart?</h2>

                <div class="inset-box mt-4 p-4 text-left text-sm">
                    <p class="font-bold" x-text="quantity + ' × ' + selectedSize?.label + ', ' + layerCount + ' layers'"></p>
                    <p class="mt-1 font-medium text-ink" x-text="'Design: ' + (selectedDesign?.name ?? 'Plain')"></p>
                    <ol class="mt-2 space-y-1 text-ink/90">
                        <template x-for="(layer, index) in layers" :key="index">
                            <li>
                                <span class="font-medium text-ink" x-text="layerTitle(index) + ':'"></span>
                                <span x-text="flavorName(sponges, layer.sponge_id) + ' sponge with ' + flavorName(creams, layer.cream_id)"></span>
                            </li>
                        </template>
                    </ol>
                    <p x-show="message.trim()" class="mt-2 text-ink/80">Message: &ldquo;<span x-text="message"></span>&rdquo;</p>
                    <p class="mt-3 flex items-baseline justify-between border-t border-line pt-3 font-semibold">
                        Total <span class="text-xl font-extrabold text-ink">৳<span x-text="format(totalPrice)"></span></span>
                    </p>
                </div>

                <p class="mt-3 text-xs text-ink/65">You can still review everything in your cart before you order.</p>

                <div class="mt-5 flex flex-col gap-2 sm:flex-row-reverse sm:justify-start">
                    <button type="button" x-ref="confirmButton" @click="confirmAndCheckout()" :disabled="isSaving" class="btn">
                        <span x-text="isSaving ? 'Adding…' : 'Add to cart'"></span>
                    </button>
                    <button type="button" @click="showModal = false" :disabled="isSaving" class="btn-outline">
                        Keep editing
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cakeStudio(config) {
            const minLayers = 2;
            const maxLayers = 4;

            // A brand-new layer with the admin's default flavors. Always a NEW object, never shared.
            const makeLayer = () => ({
                sponge_id: config.defaultSpongeId ?? config.sponges[0]?.id ?? null,
                cream_id: config.defaultCreamId ?? config.creams[0]?.id ?? null,
            });

            // The 3D viewer lives outside Alpine's data: Alpine would wrap every three.js object in a proxy.
            let viewer3d = null;
            // A drag across the design photo: where it started and which photo was showing then.
            let spinDrag = null;
            const SPIN_STEP_PX = 10; // drag this far to move one photo around the cake
            const rememberView = (view) => { try { localStorage.setItem('cake-preview-view', view); } catch (e) {} };
            const rememberedView = () => { try { return localStorage.getItem('cake-preview-view'); } catch (e) { return null; } };

            return {
                designs: config.designs ?? [], // [{id, name, extra_price, frames: [url, ...], fit, toppings}]
                designId: null, // null = a plain cake
                designFrame: 0, // which photo of the design is showing (a turn around the cake)
                sizes: config.sizes,
                sponges: config.sponges,
                creams: config.creams,
                addUrl: config.addUrl,
                cartUrl: config.cartUrl,
                parts: config.parts ?? {},
                variantLayers: config.variantLayers ?? [], // layer counts with a published SVG drawing
                variantPhotos: config.variantPhotos ?? {}, // {layers: {"sponge-3-1": {id, url}}}: a flavor's photo for one slot
                variantBases: config.variantBases ?? {}, // {layers: url}: the cake photo a drawing is drawn on
                broken: [], // image URLs that failed to load, so we fall back to the drawing

                minLayers: minLayers,
                maxLayers: maxLayers,
                layerOptions: [2, 3, 4],
                newLayer: makeLayer,

                sizeId: config.sizes[0]?.id ?? null,
                layers: Array.from({ length: minLayers }, makeLayer), // bottom layer first
                message: '',
                quantity: 1,
                maxQuantity: 20,
                showModal: false,
                lastFocus: null, // the element that opened the dialog
                isSaving: false,
                errorMessage: '',
                activeLayer: 0, // the layer whose flavors are shown (one tab per layer)

                view: '2d', // 'design' (the picked design's photos), '2d' (the photo or drawing of the layers) or '3d'
                can3d: false,
                ready3d: false,
                loading3d: false,
                error3d: '',

                // The number of layers is simply how many layers exist. One source of truth.
                get layerCount() {
                    return this.layers.length;
                },
                get selectedSize() {
                    return this.sizes.find(s => s.id === this.sizeId);
                },
                get selectedDesign() {
                    return this.designs.find(d => d.id === this.designId) ?? null;
                },
                get designFrameUrl() {
                    return this.selectedDesign?.frames[this.designFrame] ?? this.selectedDesign?.frames[0] ?? '';
                },
                // A full turn of photos was uploaded, so the design can be spun.
                get spinnable() {
                    return (this.selectedDesign?.frames.length ?? 0) > 1;
                },
                get unitPrice() {
                    const base = (parseFloat(this.selectedSize?.price) || 0) + (parseFloat(this.selectedDesign?.extra_price) || 0);
                    return this.layers.reduce((sum, layer) =>
                        sum
                        + (parseFloat(this.flavor(this.sponges, layer.sponge_id)?.extra_price) || 0)
                        + (parseFloat(this.flavor(this.creams, layer.cream_id)?.extra_price) || 0),
                        base);
                },
                // A published SVG drawing exists for the chosen layer count; otherwise the layered preview is used.
                get hasVariant() {
                    return this.variantLayers.includes(this.layerCount);
                },
                // That drawing is on a cake photo, which is then the whole cake (no drawn plate or frosting).
                get hasPhotoBase() {
                    return this.hasVariant && Boolean(this.variantBases[this.layerCount]);
                },
                get totalPrice() {
                    return this.unitPrice * this.quantity;
                },
                // What the 3D cake draws: the size, and per layer (bottom first) each flavor's color and pictures.
                get spec3d() {
                    const surface = (list, id) => {
                        const flavor = this.flavor(list, id);
                        return { color: this.flavorColor(list, id), side: flavor?.surface?.side ?? null, top: flavor?.surface?.top ?? null, scale: flavor?.surface?.scale ?? 1 };
                    };
                    return {
                        kg: Number(this.selectedSize?.weight_kg) || 2,
                        layers: this.layers.map(layer => ({ sponge: surface(this.sponges, layer.sponge_id), cream: surface(this.creams, layer.cream_id) })),
                        // A picked design is drawn instead of the layers' outside: its cover photo, where the cake sits in it, its toppings.
                        design: this.selectedDesign ? { image: this.selectedDesign.frames[0], fit: this.selectedDesign.fit, toppings: this.selectedDesign.toppings ?? [] } : null,
                    };
                },
                // The layers from the top of the cake down, with their index, for the summary.
                get layersTopDown() {
                    return this.layers.map((layer, index) => ({ layer, index })).reverse();
                },
                get previewLabel() {
                    const parts = this.layers.map((layer, i) =>
                        `layer ${i + 1}: ${this.flavorName(this.sponges, layer.sponge_id)} sponge with ${this.flavorName(this.creams, layer.cream_id)}`);
                    const design = this.selectedDesign ? `, ${this.selectedDesign.name} design` : '';
                    return `${this.layerCount}-layer cake${design}, from the bottom. ${parts.join('; ')}`;
                },
                // Only the active layers exist in the array, so only they are ever sent.
                get payloadLayers() {
                    return this.layers.map(layer => ({
                        sponge_flavor_id: layer.sponge_id,
                        cream_flavor_id: layer.cream_id,
                    }));
                },

                // Layer count: drop layers from the top, or add default layers on top.
                // Layers that stay keep their own sponge and cream.
                setLayerCount(n) {
                    const target = Math.min(maxLayers, Math.max(minLayers, Number(n) || minLayers));

                    if (target < this.layers.length) {
                        this.layers.splice(target);
                    }
                    this.activeLayer = Math.min(this.activeLayer, target - 1);
                    while (this.layers.length < target) {
                        this.layers.push(this.newLayer());
                    }
                },

                // Each setter writes exactly one field of exactly one layer.
                setSponge(index, spongeId) {
                    if (this.layers[index]) this.layers[index].sponge_id = spongeId;
                },
                setCream(index, creamId) {
                    if (this.layers[index]) this.layers[index].cream_id = creamId;
                },
                // Gives every layer the sponge and cream of one layer (each layer keeps its own object).
                copyToAll(index) {
                    const source = this.layers[index];
                    if (!source) return;
                    this.layers.forEach(layer => {
                        layer.sponge_id = source.sponge_id;
                        layer.cream_id = source.cream_id;
                    });
                },

                // Moves to another layer's tab (arrow keys, or the "Next" button) and puts focus on it.
                focusLayerTab(index) {
                    if (index < 0 || index >= this.layers.length) return;
                    this.activeLayer = index;
                    this.$nextTick(() => document.getElementById('layer-tab-' + index)?.focus());
                },

                layerTitle(index) {
                    if (index === 0) return 'Layer 1 (bottom)';
                    if (index === this.layers.length - 1) return `Layer ${index + 1} (top)`;
                    return `Layer ${index + 1}`;
                },

                flavor(list, id) {
                    return list.find(f => f.id === id);
                },
                flavorName(list, id) {
                    return this.flavor(list, id)?.name ?? '';
                },
                flavorColor(list, id) {
                    const f = this.flavor(list, id);
                    return f?.color || this.hashColor(f?.name ?? String(id));
                },
                flavorImage(list, id) {
                    const url = this.flavor(list, id)?.image ?? null;
                    return url && !this.broken.includes(url) ? url : null;
                },
                // The fill of one slot in the SVG drawing: the flavor's photo for this slot, else its layer image
                // (a strip mapped onto the band, or cropped to it), else its color.
                slotFill(layerNo, type) {
                    const [list, id] = this.slotFlavor(layerNo, type);
                    const photo = this.slotPhoto(layerNo, type);
                    if (photo) return `url(#cake-photo-${photo.id})`;
                    if (this.slotStrip(layerNo, type)) return this.flavorColor(list, id); // the strip is drawn over it
                    if (this.flavorImage(list, id)) return `url(#cake-image-${type}-${id})`;
                    return this.flavorColor(list, id);
                },
                // The flavor's layer image as a strip mapped onto the band, on a cake photo when nothing more specific
                // (a photo for this slot) applies: {url, width, height, top, bottom}, else null.
                slotStrip(layerNo, type) {
                    if (!this.hasPhotoBase || this.slotPhoto(layerNo, type)) return null;
                    const [list, id] = this.slotFlavor(layerNo, type);
                    const strip = this.flavor(list, id)?.strip;
                    const url = this.flavorImage(list, id);
                    return strip && url ? { url, ...strip } : null;
                },
                // The affine map from one arm of the strip in the image (0 = left, 1 = right) onto the same arm of
                // the band [top left, top middle, top right, bottom right, bottom middle, bottom left].
                armTransform(layerNo, type, arm, band) {
                    const strip = this.slotStrip(layerNo, type);
                    if (!strip) return null;
                    const [l0, a0, r0, , a1, l1] = band;
                    const [o, u, v] = arm === 0 ? [strip.top[0], strip.top[1], strip.bottom[0]] : [strip.top[1], strip.top[2], strip.bottom[1]];
                    const [o2, u2, v2] = arm === 0 ? [l0, a0, l1] : [a0, r0, a1];
                    // Along the arm (U) and across it (V), in the image and in the band.
                    const [ux, uy, vx, vy] = [u[0] - o[0], u[1] - o[1], v[0] - o[0], v[1] - o[1]];
                    const [Ux, Uy, Vx, Vy] = [u2[0] - o2[0], u2[1] - o2[1], v2[0] - o2[0], v2[1] - o2[1]];
                    const det = ux * vy - vx * uy;
                    if (!det) return null;
                    const a = (Ux * vy - Vx * uy) / det;
                    const c = (Vx * ux - Ux * vx) / det;
                    const b = (Uy * vy - Vy * uy) / det;
                    const d = (Vy * ux - Uy * vx) / det;
                    const e = o2[0] - (a * o[0] + c * o[1]);
                    const f = o2[1] - (b * o[0] + d * o[1]);
                    return `matrix(${[a, b, c, d, e, f].map(n => +n.toFixed(5)).join(' ')})`;
                },
                slotColor(layerNo, type) {
                    const [list, id] = this.slotFlavor(layerNo, type);
                    return this.flavorColor(list, id);
                },
                slotHasArt(layerNo, type) {
                    const [list, id] = this.slotFlavor(layerNo, type);
                    return Boolean(this.slotPhoto(layerNo, type) || this.flavorImage(list, id));
                },
                slotPhoto(layerNo, type) {
                    const [, id] = this.slotFlavor(layerNo, type);
                    const photo = this.variantPhotos[this.layerCount]?.[`${type}-${id}-${layerNo}`];
                    return photo && !this.broken.includes(photo.url) ? photo : null;
                },
                slotFlavor(layerNo, type) {
                    const layer = this.layers[layerNo - 1];
                    return type === 'sponge' ? [this.sponges, layer?.sponge_id] : [this.creams, layer?.cream_id];
                },
                partImage(key) {
                    const url = this.parts[key] ?? null;
                    return url && !this.broken.includes(url) ? url : null;
                },
                markBroken(url) {
                    if (url && !this.broken.includes(url)) this.broken.push(url);
                },

                // Picks a design (or null for plain) and shows it turning in 3D, or as its photos where 3D can't be drawn
                // (their other photos then load in the background). A plain cake stays in the view it is in.
                selectDesign(id) {
                    this.designId = id;
                    this.designFrame = 0;
                    if (this.selectedDesign && this.can3d) {
                        this.show3d();
                    } else if (this.selectedDesign) {
                        this.view = 'design';
                        this.selectedDesign.frames.slice(1).forEach(url => { new Image().src = url; });
                    } else if (this.view === 'design') {
                        this.view = '2d';
                    }
                },
                // Moves around the cake by some photos (negative = the other way), wrapping after a full turn.
                turnDesign(steps) {
                    const count = this.selectedDesign?.frames.length ?? 0;
                    if (count > 1) this.designFrame = ((this.designFrame + steps) % count + count) % count;
                },
                startSpin(event) {
                    if (!this.spinnable) return;
                    spinDrag = { x: event.clientX, frame: this.designFrame };
                    event.target.setPointerCapture?.(event.pointerId);
                },
                spin(event) {
                    if (!spinDrag) return;
                    const count = this.selectedDesign.frames.length;
                    const steps = Math.round((spinDrag.x - event.clientX) / SPIN_STEP_PX);
                    this.designFrame = ((spinDrag.frame + steps) % count + count) % count;
                },
                endSpin(event) {
                    spinDrag = null;
                    event.target.releasePointerCapture?.(event.pointerId);
                },

                show2d() {
                    this.view = '2d';
                    rememberView('2d');
                },
                // Opens the 3D cake, downloading the viewer the first time. Falls back to the photo if it can't start.
                async show3d() {
                    this.view = '3d';
                    rememberView('3d');
                    if (viewer3d || this.loading3d) return;

                    this.loading3d = true;
                    this.error3d = '';
                    try {
                        const factory = await window.whenCake3d();
                        await this.$nextTick(); // the stage is shown, so the viewer gets its size
                        viewer3d = await factory.create(this.$refs.cake3d);
                        this.ready3d = true; // x-effect on the stage now draws the cake, and redraws on every pick
                    } catch (error) {
                        this.can3d = false; // a picked design is then shown as its photos
                        this.view = this.selectedDesign ? 'design' : '2d';
                        this.error3d = 'The 3D cake could not start in this browser, so the photo is shown.';
                    } finally {
                        this.loading3d = false;
                    }
                },
                render3d(spec) {
                    viewer3d?.render(spec);
                },
                setView3d(name) {
                    viewer3d?.setView(name);
                },
                // Flavors without a saved color still get a stable, distinct one.
                hashColor(text) {
                    let h = 0;
                    for (const ch of text) h = (h * 31 + ch.charCodeAt(0)) % 360;
                    return `hsl(${h} 45% 70%)`;
                },

                // Warm the cache once the page has loaded: every flavor's layer image, and the photos of the drawing
                // in use (again whenever the layer count changes), so a pick never waits for an image.
                init() {
                    // The confirm dialog gives focus back to whatever opened it.
                    this.$watch('showModal', (open) => {
                        if (!open) this.$nextTick(() => this.lastFocus?.focus());
                    });

                    // Open the 3D cake again for customers who picked it last time.
                    this.can3d = window.canDraw3d();
                    if (this.can3d && rememberedView() === '3d') this.show3d();

                    // A design picked on the home page (?design=ID) starts selected.
                    if (this.designs.some(d => d.id === config.initialDesignId)) this.selectDesign(config.initialDesignId);

                    if (!this.variantLayers.length) return;
                    const preload = (urls) => urls.forEach(url => { new Image().src = url; });
                    const photosOf = (layers) => [
                        this.variantBases[layers],
                        ...Object.values(this.variantPhotos[layers] ?? {}).map(photo => photo.url),
                    ].filter(Boolean);
                    const whenLoaded = (fn) => document.readyState === 'complete' ? fn() : window.addEventListener('load', fn, { once: true });

                    whenLoaded(() => preload([
                        ...[...this.sponges, ...this.creams].map(f => f.image).filter(Boolean),
                        ...photosOf(this.layerCount),
                    ]));
                    this.$watch('layerCount', (layers) => whenLoaded(() => preload(photosOf(layers))));
                },

                format(value) {
                    return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                increment() {
                    if (this.quantity < this.maxQuantity) this.quantity++;
                },
                decrement() {
                    if (this.quantity > 1) this.quantity--;
                },

                // Tab and Shift+Tab cycle through the dialog's buttons instead of leaving it.
                trapFocus(event) {
                    const buttons = [...this.$refs.dialog.querySelectorAll('button:not([disabled])')];
                    if (!buttons.length) return;
                    const first = buttons[0];
                    const last = buttons[buttons.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                },

                openConfirm() {
                    this.errorMessage = '';
                    const complete = this.layers.every(l => l.sponge_id && l.cream_id);
                    if (!this.selectedSize || !complete) {
                        this.errorMessage = 'Please choose a size and a sponge and cream for every layer.';
                        return;
                    }
                    this.lastFocus = document.activeElement;
                    this.showModal = true;
                    this.$nextTick(() => this.$refs.confirmButton.focus());
                },

                async confirmAndCheckout() {
                    this.isSaving = true;
                    this.errorMessage = '';

                    try {
                        const response = await fetch(this.addUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                cake_size_id: this.sizeId,
                                cake_design_id: this.designId,
                                layers: this.payloadLayers,
                                message: this.message,
                                quantity: this.quantity,
                            }),
                        });

                        if (!response.ok) {
                            const data = await response.json().catch(() => ({}));
                            const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                            this.showModal = false;
                            this.isSaving = false;
                            this.errorMessage = firstError ?? data.message ?? 'Something went wrong. Please try again.';
                            return;
                        }

                        window.location.href = this.cartUrl;
                    } catch (error) {
                        this.showModal = false;
                        this.isSaving = false;
                        this.errorMessage = 'Could not reach the server. Please check your connection.';
                    }
                },
            };
        }
    </script>
</x-studio-layout>
