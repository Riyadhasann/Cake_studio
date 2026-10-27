<x-studio-layout title="Cake designs" :admin="true">
    @vite('resources/js/cake-3d.js')

    @php
        $fileInput = 'w-full text-sm text-ink/90 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-bold file:text-brand hover:file:bg-brand-soft';
    @endphp

    <x-admin.header title="Cake designs" description="Finished looks customers can pick for the outside of their cake; they still choose the sponge and cream inside. Upload one photo of the cake from the front, on its stand: the 3D cake is made from it automatically." />

    <x-admin.alerts />

    {{-- Add a design --}}
    <section class="admin-card mt-6">
        <h2 class="text-lg font-bold">Add a design</h2>
        <p class="admin-hint mt-1">PNG, JPG or WebP on a plain or see-through background. Optional: a full turn of photos (numbered like 01.jpg, 02.jpg) spins where a browser can't show 3D.</p>
        <form action="{{ route('admin.designs.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-[1fr_12rem_1fr_auto] md:items-end">
            @csrf
            <div>
                <label for="new-name" class="label">Name</label>
                <input id="new-name" name="name" value="{{ old('name') }}" required maxlength="80" class="field" placeholder="e.g. Chocolate Drip">
            </div>
            <div>
                <label for="new-price" class="label">Extra price (৳)</label>
                <input id="new-price" name="extra_price" type="number" min="0" step="0.01" value="{{ old('extra_price', 0) }}" required class="field">
            </div>
            <div>
                <label for="new-frames" class="label">Photos</label>
                <input id="new-frames" name="frames[]" type="file" multiple required accept="image/png,image/jpeg,image/webp" class="{{ $fileInput }}">
            </div>
            <button type="submit" class="btn">Add design</button>
        </form>
    </section>

    {{-- The designs --}}
    <div class="mt-6 space-y-4">
        @forelse ($designs as $design)
            <section class="panel p-4">
                <div class="grid gap-4 lg:grid-cols-[14rem_1fr]">
                    <div>
                        <div class="cake-scene relative aspect-square overflow-hidden rounded-xl border border-line shadow-box">
                            @if ($design->frames->first())
                                <img src="{{ asset($design->frames->first()->image) }}" alt="{{ $design->name }}" class="cake-scene-item">
                            @endif
                        </div>
                        <p class="mt-2 text-center text-xs font-semibold text-ink/80">
                            {{ $design->frames->count() > 1 ? $design->frames->count().' photos · spins 360°' : '1 photo · no spin yet' }}
                        </p>
                    </div>

                    <div class="min-w-0">
                        <form action="{{ route('admin.designs.update', $design) }}" method="POST" class="grid gap-4 sm:grid-cols-[1fr_12rem] sm:items-end">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="name-{{ $design->id }}" class="label">Name</label>
                                <input id="name-{{ $design->id }}" name="name" value="{{ $design->name }}" required maxlength="80" class="field">
                            </div>
                            <div>
                                <label for="price-{{ $design->id }}" class="label">Extra price (৳)</label>
                                <input id="price-{{ $design->id }}" name="extra_price" type="number" min="0" step="0.01" value="{{ $design->extra_price }}" required class="field">
                            </div>
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked($design->is_active) class="size-4 accent-brand">
                                Customers can pick it
                            </label>
                            <div class="flex justify-end">
                                <button type="submit" class="btn-outline btn-sm">Save</button>
                            </div>
                        </form>

                        {{-- Photos in spin order --}}
                        <p class="mt-6 text-xs font-bold uppercase tracking-[0.16em] text-brown">Photos, in spin order</p>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($design->frames as $frame)
                                <li class="relative">
                                    <img src="{{ asset($frame->image) }}" alt="{{ $design->name }}, photo {{ $loop->iteration }}" class="cake-scene size-20 rounded-xl border border-line object-contain px-3 pt-2 pb-3">
                                    <span class="absolute left-1 top-1 rounded-full bg-cocoa px-1.5 text-[0.6rem] font-bold text-white">{{ $loop->iteration }}</span>
                                    @if ($design->frames->count() > 1)
                                        <form action="{{ route('admin.designs.frames.destroy', [$design, $frame]) }}" method="POST" class="absolute -right-1.5 -top-1.5">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="grid size-6 place-items-center rounded-full border border-line bg-white text-xs font-bold text-ink/80 shadow-sm hover:text-clay"
                                                    aria-label="Remove photo {{ $loop->iteration }} of {{ $design->name }}">&times;</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <form action="{{ route('admin.designs.frames.store', $design) }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-center gap-3">
                            @csrf
                            <label for="frames-{{ $design->id }}" class="sr-only">More photos for {{ $design->name }}</label>
                            <input id="frames-{{ $design->id }}" name="frames[]" type="file" multiple required accept="image/png,image/jpeg,image/webp" class="{{ $fileInput }} max-w-sm">
                            <button type="submit" class="btn-outline btn-sm">Add photos</button>
                        </form>

                        {{-- The 3D cake next to its photo: measured from the photo on upload, checked here, adjusted only if needed --}}
                        @if ($cover = $design->frames->first())
                            <details class="mt-6 rounded-xl border border-line bg-petal/60 p-4" @if (session('check_design') === $design->id) open @endif>
                                <summary class="cursor-pointer text-sm font-bold text-ink">
                                    3D view
                                    <span class="font-medium text-ink/80">· {{ $design->fit ? 'check it matches the photo' : 'not measured yet: press Measure from photo' }}</span>
                                </summary>
                                <form action="{{ route('admin.designs.fit', $design) }}" method="POST"
                                      x-data="designFit(@js($design->fitOrDefault()), @js(['image' => asset($cover->image), 'topImage' => $design->top_image ? asset($design->top_image) : null, 'toppings' => array_map(fn (array $topping) => [...$topping, 'image' => asset($topping['image'])], $design->toppingList())]))"
                                      class="mt-4 grid gap-4 sm:grid-cols-2">
                                    @csrf
                                    @method('PUT')
                                    <figure class="min-w-0">
                                        <figcaption class="mb-1.5 text-xs font-bold text-ink">Photo <span class="font-medium text-ink/80">· blue: where the cake was found</span></figcaption>
                                        <div class="relative overflow-hidden rounded-xl border border-line bg-white">
                                            <img src="{{ asset($cover->image) }}" alt="{{ $design->name }}" class="block h-auto w-full">
                                            <svg class="absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" fill="none">
                                                <g stroke="#0ea5e9" stroke-width="2" vector-effect="non-scaling-stroke">
                                                    <ellipse :cx="fit.x * 100" :cy="(fit.top - fit.top_curve) * 100" :rx="fit.r * 100" :ry="fit.top_curve * 100" vector-effect="non-scaling-stroke" />
                                                    <path :d="bottomArc" vector-effect="non-scaling-stroke" />
                                                    <line :x1="(fit.x - fit.r) * 100" :x2="(fit.x - fit.r) * 100" :y1="(fit.top - fit.top_curve) * 100" :y2="(fit.bottom - fit.bottom_curve) * 100" vector-effect="non-scaling-stroke" />
                                                    <line :x1="(fit.x + fit.r) * 100" :x2="(fit.x + fit.r) * 100" :y1="(fit.top - fit.top_curve) * 100" :y2="(fit.bottom - fit.bottom_curve) * 100" vector-effect="non-scaling-stroke" />
                                                </g>
                                            </svg>
                                        </div>
                                    </figure>
                                    <figure class="min-w-0">
                                        <figcaption class="mb-1.5 text-xs font-bold text-ink">3D cake <span class="font-medium text-ink/80">· as customers see it</span></figcaption>
                                        <div class="stage-3d relative aspect-square overflow-hidden rounded-xl border border-line">
                                            <div x-ref="stage" class="absolute inset-0 cursor-grab active:cursor-grabbing" tabindex="0" role="group" aria-roledescription="3D cake"
                                                 aria-label="{{ $design->name }} in 3D. Drag, or use the left and right arrow keys, to turn it."></div>
                                            <p x-show="status3d" x-text="status3d" x-cloak class="absolute inset-x-3 top-1/2 -translate-y-1/2 text-center text-xs font-semibold text-ink/80"></p>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <button type="button" @click="front()" :aria-pressed="still" class="chip" :class="still && 'ring-2 ring-brand'">Front, like the photo</button>
                                            <button type="button" @click="turn()" :aria-pressed="!still" class="chip" :class="!still && 'ring-2 ring-brand'">Turn round</button>
                                        </div>
                                    </figure>

                                    <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                                        <button type="submit" form="fit-from-photo-{{ $design->id }}" class="btn btn-sm">Measure from photo</button>
                                        <p class="min-w-0 flex-1 text-xs leading-5 text-ink/80">
                                            Done for you when a design is added: finds the cake in the cover photo, cuts out what stands on its top (cherries, curls, figures) and stands it on the 3D cake.
                                            Seen from the front, the 3D cake is the photo. Works best with the cake alone on a plain or transparent background.
                                        </p>
                                    </div>

                                    {{-- Only if the outline is off: the 3D cake follows the sliders before saving --}}
                                    <details class="rounded-xl border border-line bg-white p-3 sm:col-span-2">
                                        <summary class="cursor-pointer text-xs font-bold text-ink">Adjust by hand <span class="font-medium text-ink/80">· only if the blue outline misses the cake</span></summary>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            @foreach (['x' => ['Middle', 0, 1], 'r' => ['Half width', 0.05, 0.5], 'top' => ['Top edge (front)', 0, 1], 'bottom' => ['Bottom edge (front)', 0, 1], 'top_curve' => ['Top curve', 0, 0.3], 'bottom_curve' => ['Bottom curve', 0, 0.3]] as $key => [$label, $min, $max])
                                                <label class="block text-xs font-bold text-ink">
                                                    {{ $label }}
                                                    <input type="range" name="{{ $key }}" min="{{ $min }}" max="{{ $max }}" step="0.002" x-model.number="fit.{{ $key }}" class="mt-1 w-full accent-brand">
                                                </label>
                                            @endforeach
                                            <p class="text-xs leading-5 text-ink/80 sm:col-span-2">
                                                The ellipse is the top of the cake's side, the lower curve where it meets the board. Leave decorations on top outside the outline.
                                            </p>
                                            <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                                                <button type="submit" class="btn-outline btn-sm">Save outline</button>
                                                <button type="button" @click="fit = { ...defaults }" class="text-xs font-semibold text-ink/80 underline underline-offset-4 hover:text-brand">Usual place</button>
                                            </div>
                                        </div>
                                    </details>
                                </form>
                                <form id="fit-from-photo-{{ $design->id }}" action="{{ route('admin.designs.fit-from-photo', $design) }}" method="POST" class="hidden"
                                      @if ($design->fit) onsubmit="return confirm({{ Js::from('Measure '.$design->name.' from its photo again? This replaces its outline and toppings.') }})" @endif>
                                    @csrf
                                </form>

                                {{-- Toppings cut out of the photo, standing on the 3D cake's top so they go round with it --}}
                                @if ($design->toppingList())
                                    <div class="mt-4 rounded-xl border border-line bg-white p-3">
                                        <p class="text-xs font-bold text-ink">Toppings found in the photo <span class="font-medium text-ink/80">· remove one that isn't a topping</span></p>
                                        <ul class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($design->toppingList() as $index => $topping)
                                                <li class="relative">
                                                    <img src="{{ asset($topping['image']) }}" alt="Topping {{ $loop->iteration }} of {{ $design->name }}" class="size-20 rounded-xl border border-line bg-petal object-contain p-1">
                                                    <form action="{{ route('admin.designs.toppings.destroy', [$design, $index]) }}" method="POST" class="absolute -right-1.5 -top-1.5">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="grid size-6 place-items-center rounded-full border border-line bg-white text-xs font-bold text-ink/80 shadow-sm hover:text-clay"
                                                                aria-label="Remove topping {{ $loop->iteration }} of {{ $design->name }}">&times;</button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </details>
                        @endif

                        <div class="mt-5 flex items-center justify-between gap-4 border-t border-line pt-4 text-xs text-ink/80">
                            <span>Used in {{ $design->customizations_count }} ordered {{ str('cake')->plural($design->customizations_count) }}.</span>
                            <form action="{{ route('admin.designs.destroy', $design) }}" method="POST"
                                  onsubmit="return confirm({{ Js::from('Delete '.$design->name.' and its photos? Orders keep its name.') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="underline underline-offset-4 hover:text-clay">Delete design</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        @empty
            <p class="panel p-4 text-sm text-ink/80">No designs yet. Customers only see the plain cake until you add one.</p>
        @endforelse
    </div>

    <script>
        // The outline of the cake in a design photo, in percent of the photo, and the 3D cake it makes: both follow
        // the sliders. design: { image, toppings } as on the design page.
        function designFit(saved, design) {
            let viewer = null; // kept outside Alpine data (Alpine would proxy three.js objects)
            let redraw = null;

            return {
                fit: { ...saved },
                defaults: @js(\App\Models\CakeDesign::DEFAULT_FIT),
                still: true,
                status3d: '',
                // The front half of the bottom edge.
                get bottomArc() {
                    const { x, r, bottom, bottom_curve: b } = this.fit;
                    const cy = (bottom - b) * 100;
                    return `M ${(x - r) * 100} ${cy} A ${r * 100} ${b * 100} 0 0 0 ${(x + r) * 100} ${cy}`;
                },
                init() {
                    if (!window.canDraw3d()) {
                        this.status3d = 'The 3D preview needs a browser with WebGL.';
                        return;
                    }
                    // Started the first time the panel is open (the stage needs its size).
                    const seen = new IntersectionObserver(async ([entry]) => {
                        if (!entry.isIntersecting) return;
                        seen.disconnect();
                        try {
                            viewer = await (await window.whenCake3d()).create(this.$refs.stage);
                            await this.draw();
                        } catch (error) {
                            this.status3d = 'The 3D preview could not start in this browser.';
                        }
                    });
                    seen.observe(this.$refs.stage);
                    this.$watch('fit', () => {
                        clearTimeout(redraw);
                        redraw = setTimeout(() => this.draw(), 150);
                    });
                },
                async draw() {
                    await viewer?.render({ kg: 2, layers: [], design: { image: design.image, fit: { ...this.fit }, toppings: design.toppings, topImage: design.topImage } });
                    viewer?.showFront(this.still);
                },
                front() {
                    this.still = true;
                    viewer?.showFront(true);
                },
                turn() {
                    this.still = false;
                    viewer?.showFront(false);
                },
            };
        }
    </script>
</x-studio-layout>
