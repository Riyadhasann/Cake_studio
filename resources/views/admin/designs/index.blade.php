<x-studio-layout title="Cake designs" :admin="true">
    <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

    @php
        $fileInput = 'w-full text-sm text-ink/90 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-bold file:text-brand hover:file:bg-brand-soft';
    @endphp

    <h1 class="heading mt-6 text-3xl">Cake designs</h1>
    <p class="mt-3 max-w-3xl text-sm leading-7 text-ink/90">
        Finished looks customers can pick for the outside of their cake; they still choose the sponge and cream inside.
        Upload one photo of the design, or a full turn of photos taken around the cake (for example 24 to 36, numbered
        in order like 01.jpg, 02.jpg): with more than one, customers drag the photo to spin the cake 360°. Use the same
        camera height and distance for every photo. PNG, JPG or WebP, up to 20 photos per upload; add more in batches.
    </p>

    @include('admin.sizes._alerts')

    {{-- Add a design --}}
    <section class="panel mt-5 p-4">
        <h2 class="heading text-xl">Add a design</h2>
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

                        {{-- Where the cake sits in the cover photo: the 3D cake wears the photo from this outline --}}
                        @if ($design->frames->first())
                            <details class="mt-6 rounded-xl border border-line bg-petal/60 p-4">
                                <summary class="cursor-pointer text-sm font-bold text-ink">3D fit <span class="font-medium text-ink/80">· line the outline up with the cake's side</span></summary>
                                <form action="{{ route('admin.designs.fit', $design) }}" method="POST" x-data="designFit(@js($design->fitOrDefault()))"
                                      class="mt-4 grid gap-5 md:grid-cols-[16rem_1fr]">
                                    @csrf
                                    @method('PUT')
                                    <div class="relative self-start overflow-hidden rounded-xl border border-line bg-white">
                                        <img src="{{ asset($design->frames->first()->image) }}" alt="" class="block h-auto w-full">
                                        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" fill="none">
                                            <g stroke="#0ea5e9" stroke-width="2" vector-effect="non-scaling-stroke">
                                                <ellipse :cx="fit.x * 100" :cy="(fit.top - fit.top_curve) * 100" :rx="fit.r * 100" :ry="fit.top_curve * 100" vector-effect="non-scaling-stroke" />
                                                <path :d="bottomArc" vector-effect="non-scaling-stroke" />
                                                <line :x1="(fit.x - fit.r) * 100" :x2="(fit.x - fit.r) * 100" :y1="(fit.top - fit.top_curve) * 100" :y2="(fit.bottom - fit.bottom_curve) * 100" vector-effect="non-scaling-stroke" />
                                                <line :x1="(fit.x + fit.r) * 100" :x2="(fit.x + fit.r) * 100" :y1="(fit.top - fit.top_curve) * 100" :y2="(fit.bottom - fit.bottom_curve) * 100" vector-effect="non-scaling-stroke" />
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="grid content-start gap-3 sm:grid-cols-2">
                                        @foreach (['x' => ['Middle', 0, 1], 'r' => ['Half width', 0.05, 0.5], 'top' => ['Top edge (front)', 0, 1], 'bottom' => ['Bottom edge (front)', 0, 1], 'top_curve' => ['Top curve', 0, 0.3], 'bottom_curve' => ['Bottom curve', 0, 0.3]] as $key => [$label, $min, $max])
                                            <label class="block text-xs font-bold text-ink">
                                                {{ $label }}
                                                <input type="range" name="{{ $key }}" min="{{ $min }}" max="{{ $max }}" step="0.002" x-model.number="fit.{{ $key }}" class="mt-1 w-full accent-brand">
                                            </label>
                                        @endforeach
                                        <p class="text-xs leading-5 text-ink/80 sm:col-span-2">
                                            The ellipse is the top of the cake's side, the lower curve where it meets the board. Leave decorations on top outside the outline.
                                        </p>
                                        <div class="flex gap-3 sm:col-span-2">
                                            <button type="submit" class="btn-outline btn-sm">Save 3D fit</button>
                                            <button type="button" @click="fit = { ...defaults }" class="text-xs font-semibold text-ink/80 underline underline-offset-4 hover:text-brand">Usual place</button>
                                        </div>
                                    </div>
                                </form>

                                {{-- Toppings stood on the 3D cake's top, so they go round with it --}}
                                <div class="mt-6 border-t border-line pt-4">
                                    <p class="text-sm font-bold text-ink">Toppings</p>
                                    <p class="mt-1 text-xs leading-5 text-ink/80">
                                        A cut-out of one topping (PNG or WebP with a transparent background), placed round the top.
                                        How far in: 0 is the middle, 0.9 the edge. Width: a share of the cake's radius. First at: degrees round from the front.
                                    </p>
                                    @php
                                        $toppingFields = fn (array $values) => [
                                            ['count', 'How many', $values['count'] ?? 8, 1, 40, 1],
                                            ['ring', 'How far in', $values['ring'] ?? 0.8, 0, 0.95, 0.01],
                                            ['size', 'Width', $values['size'] ?? 0.3, 0.05, 1, 0.01],
                                            ['angle', 'First at (°)', $values['angle'] ?? 0, 0, 360, 1],
                                        ];
                                    @endphp
                                    <ul class="mt-3 space-y-3">
                                        @foreach ($design->toppingList() as $index => $topping)
                                            <li class="flex flex-wrap items-end gap-3 rounded-xl border border-line bg-white p-3">
                                                <img src="{{ asset($topping['image']) }}" alt="Topping {{ $loop->iteration }} of {{ $design->name }}" class="size-14 rounded-lg bg-petal object-contain">
                                                <form action="{{ route('admin.designs.toppings.update', [$design, $index]) }}" method="POST" class="flex flex-1 flex-wrap items-end gap-3">
                                                    @csrf
                                                    @method('PUT')
                                                    @foreach ($toppingFields($topping) as [$key, $label, $value, $min, $max, $step])
                                                        <label class="block w-24 text-xs font-bold text-ink">
                                                            {{ $label }}
                                                            <input type="number" name="{{ $key }}" value="{{ $value }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" required class="field mt-1 !py-1.5">
                                                        </label>
                                                    @endforeach
                                                    <button type="submit" class="btn-outline btn-sm">Save</button>
                                                </form>
                                                <form action="{{ route('admin.designs.toppings.destroy', [$design, $index]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold text-ink/80 underline underline-offset-4 hover:text-clay">Remove</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <form action="{{ route('admin.designs.toppings.store', $design) }}" method="POST" enctype="multipart/form-data"
                                          class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-dashed border-line p-3">
                                        @csrf
                                        <label class="block text-xs font-bold text-ink">
                                            Cut-out picture
                                            <input type="file" name="image" required accept="image/png,image/webp" class="{{ $fileInput }} mt-1 max-w-xs">
                                        </label>
                                        @foreach ($toppingFields([]) as [$key, $label, $value, $min, $max, $step])
                                            <label class="block w-24 text-xs font-bold text-ink">
                                                {{ $label }}
                                                <input type="number" name="{{ $key }}" value="{{ $value }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" required class="field mt-1 !py-1.5">
                                            </label>
                                        @endforeach
                                        <button type="submit" class="btn-outline btn-sm">Add topping</button>
                                    </form>
                                </div>
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
        // The outline of the cake in a design photo, in percent of the photo: moves with the sliders.
        function designFit(saved) {
            return {
                fit: { ...saved },
                defaults: @js(\App\Models\CakeDesign::DEFAULT_FIT),
                // The front half of the bottom edge.
                get bottomArc() {
                    const { x, r, bottom, bottom_curve: b } = this.fit;
                    const cy = (bottom - b) * 100;
                    return `M ${(x - r) * 100} ${cy} A ${r * 100} ${b * 100} 0 0 0 ${(x + r) * 100} ${cy}`;
                },
            };
        }
    </script>
</x-studio-layout>
