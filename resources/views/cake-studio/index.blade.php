<x-studio-layout title="Design Your Cake" :step="1">
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
        })"
        @keydown.escape.window="showModal = false"
        class="pb-24 md:pb-0"
    >
        <x-back-link :href="route('home')" label="Back to Home" />

        {{-- Hero: what this page does, and what there is to choose from --}}
        <div class="mt-6 max-w-2xl" data-reveal>
            <p class="eyebrow">Cake Studio</p>
            <h1 class="heading mt-3 text-4xl sm:text-5xl">Design your <span class="text-brand">dream cake</span></h1>
            <p class="mt-4 text-base font-medium leading-7 text-ink/80">
                Pick a size, stack 2 to 4 layers and choose a sponge and a cream for each. The cake changes as you choose.
            </p>
        </div>

        @if (session('success'))
            <div class="mt-8 rounded-2xl border border-sage/30 bg-sage-soft px-5 py-4 text-sm font-medium text-ink">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-10 grid items-start gap-6 md:grid-cols-12 lg:gap-10">

            {{-- Left: the cake as chosen so far, and (on wider screens) a summary with the price and the cart button --}}
            <aside class="space-y-4 md:col-span-5 md:sticky md:top-24">
                <div class="panel mx-auto max-w-sm p-3 sm:p-4 md:max-w-none">
                    <div class="mb-3 flex items-center justify-between gap-3 px-1">
                        <p class="flex items-center gap-2 text-sm font-bold text-ink">
                            <span class="relative flex size-2.5" aria-hidden="true">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand/50 motion-reduce:animate-none"></span>
                                <span class="relative inline-flex size-2.5 rounded-full bg-brand"></span>
                            </span>
                            Live preview
                        </p>

                        {{-- Photo, or 3D (when this browser can draw 3D; a picked design is shown there). Without 3D, a picked design's photo. --}}
                        <div x-show="can3d || selectedDesign" x-cloak class="inline-flex rounded-full bg-petal p-1 text-sm font-bold" role="group" aria-label="Cake preview">
                            <button type="button" x-show="selectedDesign && !can3d" @click="view = 'design'" :aria-pressed="view === 'design'"
                                    class="rounded-full px-4 py-1.5 transition" :class="view === 'design' ? 'bg-white text-brand shadow-sm' : 'text-ink/80 hover:text-brand'">Design</button>
                            <button type="button" @click="show2d()" :aria-pressed="view === '2d'"
                                    class="rounded-full px-4 py-1.5 transition" :class="view === '2d' ? 'bg-white text-brand shadow-sm' : 'text-ink/80 hover:text-brand'">Photo</button>
                            <button type="button" x-show="can3d" @click="show3d()" :aria-pressed="view === '3d'"
                                    class="rounded-full px-4 py-1.5 transition" :class="view === '3d' ? 'bg-white text-brand shadow-sm' : 'text-ink/80 hover:text-brand'">3D</button>
                        </div>
                    </div>

                    {{-- Without 3D: the picked design, exactly as photographed. With a full turn of photos, dragging (or the arrow keys) spins it. --}}
                    <div x-show="view === 'design' && selectedDesign" x-cloak class="relative overflow-hidden rounded-2xl border border-line bg-white shadow-[0_6px_16px_-8px_rgba(74,36,18,0.25)]" style="aspect-ratio: 1;">
                        <img :src="designFrameUrl" :alt="selectedDesign ? selectedDesign.name + ' cake design' : ''" draggable="false"
                             class="h-full w-full select-none object-contain" :class="spinnable && 'cursor-grab active:cursor-grabbing'"
                             :tabindex="spinnable ? 0 : -1" style="touch-action: pan-y;"
                             @pointerdown="startSpin($event)" @pointermove="spin($event)" @pointerup="endSpin($event)" @pointercancel="endSpin($event)"
                             @keydown.arrow-left.prevent="turnDesign(-1)" @keydown.arrow-right.prevent="turnDesign(1)">
                        <span x-show="spinnable" class="pointer-events-none absolute inset-x-0 bottom-3 mx-auto w-max rounded-full bg-cocoa/90 px-3 py-1 text-xs font-bold text-white">
                            360° · drag to spin
                        </span>
                    </div>

                    {{-- The admin's SVG drawing for this layer count, or one element per sponge and per cream --}}
                    <div x-show="view === '2d'" class="cake-float">
                        <div data-tilt>
                            <x-cake-preview :sponges="$sponges" :creams="$creams" :variants="$variants" />
                        </div>
                    </div>

                    {{-- The 3D cake: drag (or use the arrow keys) to turn it. A picked design is wrapped round it and turns by itself. --}}
                    <div x-show="view === '3d'" x-cloak class="relative overflow-hidden rounded-2xl border border-line shadow-[0_6px_16px_-8px_rgba(74,36,18,0.25)]"
                         style="aspect-ratio: 4 / 5; background: radial-gradient(70% 55% at 50% 45%, #fffcf5 0%, #fbf3e4 50%, #f1e4cc 100%);">
                        <div x-ref="cake3d" class="absolute inset-0 cursor-grab focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand active:cursor-grabbing"
                             tabindex="0" role="group" aria-roledescription="3D cake" :aria-label="previewLabel + '. Drag, or use the arrow keys, to turn it.'"
                             x-effect="if (view === '3d' && ready3d) render3d(spec3d)"></div>
                        <p x-show="loading3d" class="pointer-events-none absolute inset-0 grid place-items-center text-sm font-semibold text-ink">Loading the 3D cake…</p>
                        <span x-show="selectedDesign && !loading3d" x-transition.opacity
                              class="pointer-events-none absolute inset-x-0 top-3 mx-auto w-max rounded-full bg-cocoa/90 px-3 py-1 text-xs font-bold text-white">
                            360° · <span x-text="selectedDesign?.name"></span> · drag to turn
                        </span>
                        <div x-show="!selectedDesign" class="absolute inset-x-0 bottom-3 flex justify-center gap-2" role="group" aria-label="Camera">
                            @foreach (['front' => 'Front', 'angle' => 'Angle', 'top' => 'Top'] as $name => $label)
                                <button type="button" @click="setView3d(@js($name))"
                                        class="rounded-full bg-white/95 px-3.5 py-1 text-xs font-bold text-ink shadow-sm backdrop-blur transition hover:text-brand">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <p x-show="error3d" x-text="error3d" x-cloak role="alert" class="mt-2 text-center text-xs font-medium text-clay"></p>
                </div>

                <div class="panel hidden p-5 md:block">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="heading text-xl">Your cake</h2>
                        <div class="flex gap-1.5 text-xs font-bold">
                            <span class="rounded-full bg-gold-soft px-2.5 py-1 text-ink" x-text="selectedSize?.label ?? ''"></span>
                            <span class="rounded-full bg-brand-soft px-2.5 py-1 text-brand-dark" x-text="layerCount + ' layers'"></span>
                        </div>
                    </div>

                    {{-- The picked design --}}
                    <div class="mt-3 flex items-center gap-3 border-b border-line pb-3 text-sm">
                        <span class="w-16 shrink-0 text-xs font-bold uppercase tracking-wide text-brown">Design</span>
                        <template x-if="selectedDesign">
                            <img :src="selectedDesign.frames[0]" alt="" class="size-8 rounded-lg border border-line bg-white object-contain">
                        </template>
                        <span class="font-medium" x-text="selectedDesign?.name ?? 'Plain'"></span>
                    </div>

                    {{-- Listed top to bottom, like the cake in the picture --}}
                    <ol class="divide-y divide-line text-sm" aria-label="Layers, from the top">
                        <template x-for="row in layersTopDown" :key="row.index">
                            <li class="flex flex-wrap items-center gap-x-3 gap-y-1 py-2.5">
                                <span class="w-16 shrink-0 text-xs font-bold uppercase tracking-wide text-brown" x-text="'Layer ' + (row.index + 1)"></span>
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
                       class="mt-3 rounded-xl border border-clay/30 bg-clay-soft px-3 py-2 text-sm font-medium text-clay"></p>

                    <div class="mt-4 flex items-end justify-between gap-4 rounded-xl border border-line bg-petal px-4 py-3.5" aria-live="polite" aria-atomic="true">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brown">Total</p>
                            <p class="mt-1 text-3xl font-extrabold leading-none text-ink">৳<span x-effect="countTo($el, totalPrice, format)"></span></p>
                        </div>
                        <p class="text-sm font-semibold text-ink/80" x-text="quantity + ' × ৳' + format(unitPrice)"></p>
                    </div>

                    <button type="button" @click="openConfirm()" class="btn mt-4 w-full">
                        Add to Cart
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </button>
                    <p class="mt-3 text-center text-xs font-medium text-ink/80">You can still review everything in your cart before you order.</p>
                </div>
            </aside>

            {{-- Right: the choices, as numbered steps --}}
            <div class="md:col-span-7">
                <div class="space-y-5">
                    <section id="step-size" class="panel scroll-mt-40 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-6 shrink-0 place-items-center rounded-full bg-cocoa text-xs font-bold text-white" aria-hidden="true">1</span>
                                    <h2 id="size-heading" class="heading text-xl leading-none">Choose a size</h2>
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-ink/80">The size sets the starting price.</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-gold-soft px-3 py-1 text-xs font-bold text-ink" x-text="selectedSize?.label ?? ''"></span>
                        </div>
                        <div class="mt-5" role="group" aria-labelledby="size-heading">
                            <x-studio-dropdown id="size-picker" labelledby="size-heading" icon="size"
                                               options="sizes" selected="sizeId === o.id" pick="sizeId = o.id"
                                               label="o.label" meta="'৳' + Number(o.price).toLocaleString('en-US')"
                                               current="selectedSize?.label ?? 'Choose a size'"
                                               current-meta="selectedSize ? 'From ৳' + Number(selectedSize.price).toLocaleString('en-US') : ''" />
                        </div>
                    </section>

                    <section id="step-layers" class="panel scroll-mt-40 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-6 shrink-0 place-items-center rounded-full bg-cocoa text-xs font-bold text-white" aria-hidden="true">2</span>
                                    <h2 id="layers-heading" class="heading text-xl leading-none">How many layers?</h2>
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-ink/80">More layers make a taller cake. The price stays the same.</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-brand-soft px-3 py-1 text-xs font-bold text-brand-dark" x-text="layerCount + ' layers'"></span>
                        </div>
                        <div class="mt-5">
                            <x-studio-dropdown id="layers-picker" labelledby="layers-heading" icon="layers"
                                               options="layerOptions" selected="layerCount === o" pick="setLayerCount(o)"
                                               label="o + ' layers'" meta="['', '', 'Classic', 'Tall', 'Extra tall'][o]"
                                               current="layerCount + ' layers'" current-meta="['', '', 'Classic', 'Tall', 'Extra tall'][layerCount]" />
                        </div>
                    </section>

                    <section id="step-flavors" class="panel scroll-mt-40 p-5 sm:p-6">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="step-dot grid size-6 shrink-0 place-items-center rounded-full bg-cocoa text-xs font-bold text-white" aria-hidden="true">3</span>
                                <h2 class="heading text-xl leading-none">Pick the flavors</h2>
                            </div>
                            <p class="mt-1.5 text-sm font-medium text-ink/80">A sponge and a cream for each layer. Layer 1 is at the bottom.</p>
                        </div>

                        {{-- One tab per layer, with its two flavors as dots --}}
                        <div class="mt-5 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Layers">
                            <template x-for="(layer, index) in layers" :key="index">
                                <button type="button" role="tab" :id="'layer-tab-' + index" :aria-selected="activeLayer === index" :aria-controls="'layer-panel-' + index"
                                        :tabindex="activeLayer === index ? 0 : -1" @click="activeLayer = index"
                                        @keydown.arrow-right.prevent="focusLayerTab(index + 1)" @keydown.arrow-left.prevent="focusLayerTab(index - 1)"
                                        class="flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                                        :class="activeLayer === index ? 'border-brand bg-brand text-white shadow-[0_8px_18px_-10px_rgba(181,86,42,0.6)]' : 'border-line bg-white text-ink shadow-sm hover:border-brand/50 hover:text-brand'">
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
                                 class="mt-4 rounded-2xl border border-line bg-paper p-4 shadow-[inset_0_1px_4px_rgba(74,36,18,0.07)] sm:p-5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-ink">
                                        <span x-text="flavorName(sponges, layer.sponge_id)"></span> sponge
                                        <span class="text-ink/80">with</span>
                                        <span x-text="flavorName(creams, layer.cream_id)"></span>
                                    </p>
                                    <button type="button" x-show="layers.length > 1" @click="copyToAll(index)"
                                            class="rounded-full px-3 py-1.5 text-xs font-bold text-brand transition hover:bg-brand-soft">
                                        Use these on all layers
                                    </button>
                                </div>

                                <p class="mt-4 text-xs font-bold uppercase tracking-[0.16em] text-brown" aria-hidden="true">Sponge</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3" role="group" :aria-label="layerTitle(index) + ': sponge'">
                                    <template x-for="sponge in sponges" :key="sponge.id">
                                        <button type="button" @click="setSponge(index, sponge.id)" :aria-pressed="layer.sponge_id === sponge.id"
                                                class="choice flex items-center gap-2.5 px-3 py-2.5">
                                            <span class="size-5 shrink-0 rounded-full border-2 border-white shadow-[0_0_0_1px_rgba(59,38,34,0.15)]" :style="{ backgroundColor: flavorColor(sponges, sponge.id) }"></span>
                                            <span class="min-w-0 flex-1 leading-tight">
                                                <span class="block font-semibold" x-text="sponge.name"></span>
                                                <span class="block text-xs font-medium text-ink/80" x-text="Number(sponge.extra_price) > 0 ? '+৳' + Number(sponge.extra_price) : 'Included'"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>

                                <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-brown" aria-hidden="true">Cream</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3" role="group" :aria-label="layerTitle(index) + ': cream'">
                                    <template x-for="cream in creams" :key="cream.id">
                                        <button type="button" @click="setCream(index, cream.id)" :aria-pressed="layer.cream_id === cream.id"
                                                class="choice flex items-center gap-2.5 px-3 py-2.5">
                                            <span class="size-5 shrink-0 rounded-full border-2 border-white shadow-[0_0_0_1px_rgba(59,38,34,0.15)]" :style="{ backgroundColor: flavorColor(creams, cream.id) }"></span>
                                            <span class="min-w-0 flex-1 leading-tight">
                                                <span class="block font-semibold" x-text="cream.name"></span>
                                                <span class="block text-xs font-medium text-ink/80" x-text="Number(cream.extra_price) > 0 ? '+৳' + Number(cream.extra_price) : 'Included'"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>

                                <div x-show="index < layers.length - 1" class="mt-5 flex justify-end">
                                    <button type="button" @click="focusLayerTab(index + 1)" class="btn-outline btn-sm">
                                        Next: <span x-text="layerTitle(index + 1)"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </section>

                    <section id="step-design" class="panel scroll-mt-40 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-6 shrink-0 place-items-center rounded-full bg-cocoa text-xs font-bold text-white" aria-hidden="true">4</span>
                                    <h2 id="design-heading" class="heading text-xl leading-none">Pick a design</h2>
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-ink/80">The finished look outside. You still choose the layers inside.</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-gold-soft px-3 py-1 text-xs font-bold text-ink" x-text="selectedDesign?.name ?? 'Plain'"></span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3" role="group" aria-labelledby="design-heading">
                            {{-- No design: the plain layered cake --}}
                            <button type="button" @click="selectDesign(null)" :aria-pressed="designId === null" class="choice flex flex-col p-2 text-center">
                                <span class="grid aspect-square w-full place-items-center rounded-xl bg-white" aria-hidden="true">
                                    <span class="flex w-14 flex-col-reverse gap-0.5">
                                        <template x-for="i in 3" :key="i">
                                            <span class="flex flex-col-reverse gap-px"><span class="h-2.5 rounded-sm bg-brown/60"></span><span class="h-1 rounded-full bg-gold"></span></span>
                                        </template>
                                    </span>
                                </span>
                                <span class="mt-2 block font-semibold">Plain</span>
                                <span class="block text-xs font-medium text-ink/80">Just your layers</span>
                            </button>
                            <template x-for="design in designs" :key="design.id">
                                <button type="button" @click="selectDesign(design.id)" :aria-pressed="designId === design.id" class="choice flex flex-col p-2 text-center">
                                    <span class="relative block aspect-square w-full overflow-hidden rounded-xl bg-white">
                                        <img :src="design.frames[0]" :alt="design.name" class="h-full w-full object-contain" loading="lazy">
                                        <span x-show="design.frames.length > 1" class="absolute right-1.5 top-1.5 rounded-full bg-cocoa/90 px-2 py-0.5 text-[0.65rem] font-bold text-white">360°</span>
                                    </span>
                                    <span class="mt-2 block font-semibold" x-text="design.name"></span>
                                    <span class="block text-xs font-medium text-ink/80" x-text="Number(design.extra_price) > 0 ? '+৳' + Number(design.extra_price).toLocaleString('en-US') : 'Included'"></span>
                                </button>
                            </template>
                        </div>
                    </section>

                    <section id="step-message" class="panel scroll-mt-40 p-5 sm:p-6">
                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="step-dot grid size-6 shrink-0 place-items-center rounded-full bg-cocoa text-xs font-bold text-white" aria-hidden="true">5</span>
                                    <label for="message" class="heading text-xl leading-none">Message on the cake</label>
                                </div>
                                <span class="text-xs font-semibold text-ink/80" x-text="message.length + '/255'"></span>
                            </div>
                            <p class="mt-1.5 text-sm font-medium text-ink/80">Optional. Leave it empty for no writing.</p>
                        </div>
                        <textarea id="message" x-model="message" maxlength="255" rows="2" placeholder="e.g. Happy Birthday, Mina!" class="field mt-4"></textarea>
                        <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Message ideas">
                            @foreach (['Happy Birthday!', 'Congratulations!', 'Happy Anniversary!', 'With love'] as $idea)
                                <button type="button" @click="message = @js($idea)"
                                        class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink/80 shadow-sm transition hover:border-brand/50 hover:text-brand">{{ $idea }}</button>
                            @endforeach
                        </div>
                    </section>

                    <section id="step-quantity" class="panel scroll-mt-40 p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-2.5">
                                <span class="step-dot grid size-6 shrink-0 place-items-center rounded-full bg-cocoa text-xs font-bold text-white" aria-hidden="true">6</span>
                                <h2 id="quantity-heading" class="heading text-xl leading-none">How many cakes?</h2>
                            </div>
                            <div class="inline-flex items-center rounded-full border border-line bg-white p-1" role="group" aria-labelledby="quantity-heading">
                                <button type="button" @click="decrement()" :disabled="quantity <= 1" aria-label="Decrease quantity"
                                    class="grid size-10 place-items-center rounded-full text-xl font-bold text-brand transition hover:bg-brand-soft disabled:text-ink/30 disabled:hover:bg-transparent">&minus;</button>
                                <span class="w-10 text-center text-lg font-bold" x-text="quantity" aria-live="polite"></span>
                                <button type="button" @click="increment()" :disabled="quantity >= maxQuantity" aria-label="Increase quantity"
                                    class="grid size-10 place-items-center rounded-full text-xl font-bold text-brand transition hover:bg-brand-soft disabled:text-ink/30 disabled:hover:bg-transparent">+</button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        {{-- Phones: the price and the cart button stay at the bottom of the screen --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 px-4 py-3 shadow-[0_-8px_24px_-12px_rgba(59,38,34,0.25)] backdrop-blur md:hidden">
            <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert" class="mb-2 text-sm font-medium text-clay"></p>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-ink/80" x-text="(selectedSize?.label ?? '') + ' · ' + layerCount + ' layers · ' + quantity + ' × ৳' + format(unitPrice)"></p>
                    <p class="text-2xl font-extrabold leading-tight text-ink">৳<span x-effect="countTo($el, totalPrice, format)"></span></p>
                </div>
                <button type="button" @click="openConfirm()" class="btn">Add to Cart</button>
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
            <div x-ref="dialog" class="dialog-pop max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-paper p-6 text-center shadow-xl sm:p-8">
                <p class="eyebrow">Almost done</p>
                <h2 id="confirm-title" class="heading mt-2 text-3xl">Add this cake to your cart?</h2>

                <div class="mt-6 rounded-xl border border-line bg-white p-5 text-left text-sm">
                    <p class="font-display text-lg font-semibold" x-text="quantity + ' × ' + selectedSize?.label + ', ' + layerCount + ' layers'"></p>
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
                        Total <span class="text-2xl font-extrabold text-ink">৳<span x-text="format(totalPrice)"></span></span>
                    </p>
                </div>

                <p class="mt-4 text-xs text-ink/80">You can still review everything in your cart before you order.</p>

                <div class="mt-6 space-y-3">
                    <button type="button" x-ref="confirmButton" @click="confirmAndCheckout()" :disabled="isSaving" class="btn w-full">
                        <span x-text="isSaving ? 'Adding…' : 'Add to Cart & Go to Checkout'"></span>
                    </button>
                    <button type="button" @click="showModal = false" :disabled="isSaving" class="btn-outline w-full">
                        Keep Customizing
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
