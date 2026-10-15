<x-studio-layout title="Design Your Cake" :step="1">

    <div
        x-data="cakeStudio({
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

        <x-page-heading class="mt-6" eyebrow="Cake Studio" script="Make it yours" title="Design Your Cake">
            Follow the steps: pick a size, the number of layers, then a sponge and a cream for each layer. The picture shows your cake as you choose.
        </x-page-heading>

        @if (session('success'))
            <div class="mx-auto mt-8 max-w-2xl rounded-xl border border-sage/30 bg-sage-soft px-5 py-4 text-sm text-ink">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-10 grid items-start gap-6 md:grid-cols-12 lg:gap-10">

            {{-- Left: the cake as chosen so far, and (on wider screens) a summary with the price and the cart button --}}
            <aside class="space-y-4 md:col-span-5 md:sticky md:top-28">
                {{-- The admin's SVG drawing for this layer count, or one element per sponge and per cream --}}
                <div class="mx-auto max-w-sm md:max-w-none">
                    <x-cake-preview :sponges="$sponges" :creams="$creams" :variants="$variants" />
                </div>

                <div class="panel hidden p-5 md:block">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="heading text-xl">Your cake</h2>
                        <span class="text-sm text-ink/60" x-text="(selectedSize?.label ?? '') + ' · ' + layerCount + ' layers'"></span>
                    </div>

                    {{-- Listed top to bottom, like the cake in the picture --}}
                    <ol class="mt-3 divide-y divide-line text-sm" aria-label="Layers, from the top">
                        <template x-for="row in layersTopDown" :key="row.index">
                            <li class="flex flex-wrap items-center gap-x-3 gap-y-1 py-2">
                                <span class="w-16 shrink-0 text-xs font-semibold uppercase tracking-wide text-ink/50" x-text="'Layer ' + (row.index + 1)"></span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-3 w-3 rounded-sm border border-black/10" :style="{ backgroundColor: flavorColor(sponges, row.layer.sponge_id) }"></span>
                                    <span x-text="flavorName(sponges, row.layer.sponge_id)"></span>
                                </span>
                                <span class="text-ink/30" aria-hidden="true">+</span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-3 w-3 rounded-sm border border-black/10" :style="{ backgroundColor: flavorColor(creams, row.layer.cream_id) }"></span>
                                    <span x-text="flavorName(creams, row.layer.cream_id)"></span>
                                </span>
                            </li>
                        </template>
                    </ol>

                    <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert"
                       class="mt-3 rounded-lg border border-clay/30 bg-clay-soft px-3 py-2 text-sm text-clay"></p>

                    <div class="mt-4 flex items-end justify-between gap-4 border-t border-line pt-4">
                        <div aria-live="polite" aria-atomic="true">
                            <p class="text-xs text-ink/60" x-text="quantity + ' × ৳' + format(unitPrice)"></p>
                            <p class="mt-0.5 font-display text-3xl font-semibold leading-none">৳<span x-text="format(totalPrice)"></span></p>
                        </div>
                        <button type="button" @click="openConfirm()" class="btn">Add to Cart</button>
                    </div>
                </div>
            </aside>

            {{-- Right: the choices, as numbered steps --}}
            <div class="space-y-5 md:col-span-7">

                <section class="panel p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand text-sm font-semibold text-white" aria-hidden="true">1</span>
                        <div>
                            <h2 id="size-heading" class="heading text-xl">Choose a size</h2>
                            <p class="text-sm text-ink/60">The size sets the starting price.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2 sm:gap-3" role="group" aria-labelledby="size-heading">
                        <template x-for="size in sizes" :key="size.id">
                            <button type="button" @click="sizeId = size.id" :aria-pressed="sizeId === size.id" class="choice px-4 py-3">
                                <span class="block font-semibold" x-text="size.label"></span>
                                <span class="mt-0.5 block text-ink/60" x-text="'৳' + Number(size.price).toLocaleString('en-US')"></span>
                                <svg x-show="sizeId === size.id" x-cloak class="absolute right-2.5 top-2.5 h-5 w-5 text-brand" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.86a.75.75 0 0 0-1.22-.88l-3.24 4.5-1.6-1.6a.75.75 0 1 0-1.06 1.06l2.22 2.22a.75.75 0 0 0 1.14-.09l3.76-5.21Z" clip-rule="evenodd"/></svg>
                            </button>
                        </template>
                    </div>
                </section>

                <section class="panel p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand text-sm font-semibold text-white" aria-hidden="true">2</span>
                        <div>
                            <h2 class="heading text-xl">How many layers?</h2>
                            <p class="text-sm text-ink/60">More layers make a taller cake. The price stays the same.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3" role="group" aria-label="Number of layers">
                        <template x-for="n in layerOptions" :key="n">
                            <button type="button" @click="setLayerCount(n)" :aria-pressed="layerCount === n" :aria-label="n + ' layers'"
                                    class="choice flex flex-col items-center gap-2 px-3 py-3 text-center">
                                {{-- A tiny cake with n layers --}}
                                <span class="flex h-7 w-10 flex-col-reverse gap-0.5" aria-hidden="true">
                                    <template x-for="i in n" :key="i">
                                        <span class="h-1.5 rounded-sm" :class="layerCount === n ? 'bg-brand' : 'bg-ink/25'"></span>
                                    </template>
                                </span>
                                <span class="font-semibold" x-text="n + ' layers'"></span>
                            </button>
                        </template>
                    </div>
                </section>

                <section class="panel p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand text-sm font-semibold text-white" aria-hidden="true">3</span>
                        <div>
                            <h2 class="heading text-xl">Pick the flavors</h2>
                            <p class="text-sm text-ink/60">A sponge and a cream for each layer. Layer 1 is at the bottom.</p>
                        </div>
                    </div>

                    {{-- One card per active layer. Removed layers disappear from here and from the data. --}}
                    <div class="mt-4 space-y-4">
                        <template x-for="(layer, index) in layers" :key="index">
                            <div class="rounded-xl border border-line bg-paper p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="font-display text-lg font-semibold" x-text="layerTitle(index)"></h3>
                                    <button type="button" x-show="layers.length > 1" @click="copyToAll(index)"
                                            class="rounded-md px-2 py-1 text-xs font-semibold text-brand hover:bg-brand-soft">
                                        Use these on all layers
                                    </button>
                                </div>

                                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-ink/50" aria-hidden="true">Sponge</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3" role="group" :aria-label="layerTitle(index) + ': sponge'">
                                    <template x-for="sponge in sponges" :key="sponge.id">
                                        <button type="button" @click="setSponge(index, sponge.id)" :aria-pressed="layer.sponge_id === sponge.id"
                                                class="choice flex items-center gap-2 px-3 py-2.5">
                                            <span class="h-5 w-5 shrink-0 rounded-md border border-black/10" :style="{ backgroundColor: flavorColor(sponges, sponge.id) }"></span>
                                            <span class="min-w-0 flex-1 leading-tight">
                                                <span class="block font-medium" x-text="sponge.name"></span>
                                                <span x-show="Number(sponge.extra_price) > 0" class="block text-xs text-ink/60" x-text="'+৳' + Number(sponge.extra_price)"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>

                                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-ink/50" aria-hidden="true">Cream</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3" role="group" :aria-label="layerTitle(index) + ': cream'">
                                    <template x-for="cream in creams" :key="cream.id">
                                        <button type="button" @click="setCream(index, cream.id)" :aria-pressed="layer.cream_id === cream.id"
                                                class="choice flex items-center gap-2 px-3 py-2.5">
                                            <span class="h-5 w-5 shrink-0 rounded-md border border-black/10" :style="{ backgroundColor: flavorColor(creams, cream.id) }"></span>
                                            <span class="min-w-0 flex-1 leading-tight">
                                                <span class="block font-medium" x-text="cream.name"></span>
                                                <span x-show="Number(cream.extra_price) > 0" class="block text-xs text-ink/60" x-text="'+৳' + Number(cream.extra_price)"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="panel p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand text-sm font-semibold text-white" aria-hidden="true">4</span>
                        <div class="flex-1">
                            <div class="flex items-baseline justify-between gap-4">
                                <label for="message" class="heading text-xl">Message on the cake</label>
                                <span class="text-xs text-ink/60" x-text="message.length + '/255'"></span>
                            </div>
                            <p class="text-sm text-ink/60">Optional. Leave it empty for no writing.</p>
                        </div>
                    </div>
                    <textarea id="message" x-model="message" maxlength="255" rows="2" placeholder="e.g. Happy Birthday, Mina!" class="field mt-4"></textarea>
                </section>

                <section class="panel p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand text-sm font-semibold text-white" aria-hidden="true">5</span>
                            <h2 id="quantity-heading" class="heading text-xl">How many cakes?</h2>
                        </div>
                        <div class="inline-flex items-center rounded-lg border border-line bg-white" role="group" aria-labelledby="quantity-heading">
                            <button type="button" @click="decrement()" :disabled="quantity <= 1" aria-label="Decrease quantity"
                                class="h-11 w-11 rounded-l-lg text-xl text-brand hover:bg-brand-soft disabled:text-ink/30 disabled:hover:bg-transparent">&minus;</button>
                            <span class="w-10 text-center font-semibold" x-text="quantity" aria-live="polite"></span>
                            <button type="button" @click="increment()" :disabled="quantity >= maxQuantity" aria-label="Increase quantity"
                                class="h-11 w-11 rounded-r-lg text-xl text-brand hover:bg-brand-soft disabled:text-ink/30 disabled:hover:bg-transparent">+</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        {{-- Phones: the price and the cart button stay at the bottom of the screen --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 px-4 py-3 shadow-[0_-4px_16px_rgba(59,42,39,0.08)] backdrop-blur md:hidden">
            <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert" class="mb-2 text-sm text-clay"></p>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-ink/60" x-text="(selectedSize?.label ?? '') + ' · ' + layerCount + ' layers · ' + quantity + ' × ৳' + format(unitPrice)"></p>
                    <p class="font-display text-2xl font-semibold leading-tight">৳<span x-text="format(totalPrice)"></span></p>
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
            <div x-ref="dialog" class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-paper p-6 text-center shadow-xl sm:p-8">
                <p class="eyebrow">Almost done</p>
                <h2 id="confirm-title" class="heading mt-2 text-3xl">Add this cake to your cart?</h2>

                <div class="mt-6 rounded-xl border border-line bg-white p-5 text-left text-sm">
                    <p class="font-display text-lg font-semibold" x-text="quantity + ' × ' + selectedSize?.label + ', ' + layerCount + ' layers'"></p>
                    <ol class="mt-2 space-y-1 text-ink/80">
                        <template x-for="(layer, index) in layers" :key="index">
                            <li>
                                <span class="font-medium text-ink" x-text="layerTitle(index) + ':'"></span>
                                <span x-text="flavorName(sponges, layer.sponge_id) + ' sponge with ' + flavorName(creams, layer.cream_id)"></span>
                            </li>
                        </template>
                    </ol>
                    <p x-show="message.trim()" class="mt-2 text-ink/70">Message: &ldquo;<span x-text="message"></span>&rdquo;</p>
                    <p class="mt-3 flex items-baseline justify-between border-t border-line pt-3 font-semibold">
                        Total <span class="font-display text-2xl">৳<span x-text="format(totalPrice)"></span></span>
                    </p>
                </div>

                <p class="mt-4 text-xs text-ink/70">You can still review everything in your cart before you order.</p>

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

            return {
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

                // The number of layers is simply how many layers exist. One source of truth.
                get layerCount() {
                    return this.layers.length;
                },
                get selectedSize() {
                    return this.sizes.find(s => s.id === this.sizeId);
                },
                get unitPrice() {
                    const base = parseFloat(this.selectedSize?.price) || 0;
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
                // The layers from the top of the cake down, with their index, for the summary.
                get layersTopDown() {
                    return this.layers.map((layer, index) => ({ layer, index })).reverse();
                },
                get previewLabel() {
                    const parts = this.layers.map((layer, i) =>
                        `layer ${i + 1}: ${this.flavorName(this.sponges, layer.sponge_id)} sponge with ${this.flavorName(this.creams, layer.cream_id)}`);
                    return `${this.layerCount}-layer cake, from the bottom. ${parts.join('; ')}`;
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
