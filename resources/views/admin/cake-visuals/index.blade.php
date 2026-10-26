<x-studio-layout title="Cake images" :admin="true">
    @vite('resources/js/cake-3d.js')
    <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

    @php
        $defaultSponge = $sponges->firstWhere('is_default', true) ?? $sponges->first();
        $defaultCream = $creams->firstWhere('is_default', true) ?? $creams->first();

        // The full ID list with the item at $index swapped with its neighbour, or null at the edge.
        $swapped = function ($items, int $index, int $offset) {
            $ids = $items->pluck('id')->all();
            $other = $index + $offset;
            if (! isset($ids[$other])) {
                return null;
            }
            [$ids[$index], $ids[$other]] = [$ids[$other], $ids[$index]];

            return $ids;
        };

        $fileInput = 'w-full text-sm text-ink/90 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft';
        $step = 'flex size-7 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-bold text-cream';
    @endphp

    <div class="mt-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="heading text-3xl">Cake images</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/90">
                What customers see in the cake preview, set up in four steps.
            </p>
        </div>
        <a href="{{ route('cake-studio.index') }}" target="_blank" class="btn-outline btn-sm">Open the design page ↗</a>
    </div>

    @include('admin.cake-variants._alerts')

    {{-- 1. The cake photo --}}
    <section class="panel mt-6 p-4">
        <div class="flex items-center gap-3">
            <span class="{{ $step }}">1</span>
            <h2 class="heading text-xl">Cake photo</h2>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-[minmax(0,18rem)_minmax(0,1fr)]">
            @if ($photo)
                <img src="{{ asset($photo) }}" alt="The cake photo" class="w-full rounded-xl border border-line bg-white">
            @else
                <div class="flex aspect-[4/3] items-center justify-center rounded-xl border border-dashed border-line bg-petal px-6 text-center text-sm text-ink/80">
                    No photo yet
                </div>
            @endif

            <div class="space-y-4">
                <p class="text-sm leading-7 text-ink/90">
                    A photo of the whole cake with a slice cut out. Leave the cut <strong class="text-ink">empty</strong>
                    (see-through, or a gray checkerboard): the sponge and cream layers are filled into it automatically,
                    for 2, 3 and 4 layers. PNG, JPG or WebP, up to 4 MB.
                </p>

                @if ($photo)
                    @if ($photoLive)
                        <p class="inline-flex rounded-full bg-sage-soft px-3 py-1 text-xs font-medium text-sage">Customers see this photo now</p>
                    @else
                        <p class="inline-flex rounded-full bg-clay-soft px-3 py-1 text-xs font-medium text-clay">
                            Not shown yet: the cut needs marking under <a href="{{ route('admin.cake-variants.index') }}" class="ml-1 underline">Cake shapes</a>
                        </p>
                    @endif
                @endif

                <form action="{{ route('admin.cake-photo.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <label for="cake-photo" class="sr-only">Cake photo</label>
                    <input id="cake-photo" type="file" name="base" accept="image/png,image/jpeg,image/webp" required class="{{ $fileInput }} max-w-sm">
                    <button type="submit" class="btn btn-sm">{{ $photo ? 'Replace photo' : 'Upload photo' }}</button>
                </form>

                @if ($photo)
                    <div class="flex flex-wrap gap-5 text-xs">
                        <a href="{{ route('admin.cake-variants.index') }}" class="text-ink/80 underline underline-offset-4 hover:text-brand">Adjust the cut by hand</a>
                        <form action="{{ route('admin.cake-photo.destroy') }}" method="POST"
                              onsubmit="return confirm('Remove the cake photo? The design page goes back to the drawn cake.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-ink/80 underline underline-offset-4 hover:text-clay">Remove photo</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- 2. The flavors every layer starts with --}}
    <section class="panel mt-6 p-4">
        <div class="flex items-center gap-3">
            <span class="{{ $step }}">2</span>
            <h2 class="heading text-xl">Default cake</h2>
        </div>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/90">
            The design page opens with every layer in these flavors. When a customer picks another one, only that layer changes.
        </p>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            @foreach (['sponge' => [$sponges, $defaultSponge], 'cream' => [$creams, $defaultCream]] as $type => [$flavors, $default])
                <form method="POST" x-data="{ id: @js($default?->id) }"
                      :action="@js(route('admin.cake-visuals.default', [$type, '__ID__'])).replace('__ID__', id)" class="flex items-end gap-3">
                    @csrf
                    <div class="grow">
                        <label for="default-{{ $type }}" class="label">Default {{ $type }}</label>
                        <select id="default-{{ $type }}" x-model.number="id" class="field">
                            @foreach ($flavors as $flavor)
                                <option value="{{ $flavor->id }}" @selected($flavor->id === $default?->id)>{{ $flavor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-outline btn-sm" :disabled="id === @js($default?->id)">Save</button>
                </form>
            @endforeach
        </div>
    </section>

    {{-- 3 and 4. A picture per flavor --}}
    @foreach (['sponge' => ['3', 'Sponge pictures', $sponges], 'cream' => ['4', 'Cream pictures', $creams]] as $type => [$number, $title, $flavors])
        <section class="panel mt-6 p-4">
            <div class="flex items-center gap-3">
                <span class="{{ $step }}">{{ $number }}</span>
                <h2 class="heading text-xl">{{ $title }}</h2>
            </div>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/90">
                One picture per {{ $type }} flavor, shaped like that layer in the cut: for a cake with a slice cut out, a
                V-shaped strip on a see-through background. It is stretched to fit each layer. Without a picture, the layer
                is drawn in the flavor's color. The arrows set the order on the design page.
            </p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($flavors as $flavor)
                    <x-visual-card :item="$flavor" :type="$type" ratio="2.6 / 1" fit="contain"
                                   :move-up="$swapped($flavors, $loop->index, -1)" :move-down="$swapped($flavors, $loop->index, 1)" />
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- Only for the drawn cake, used while there is no photo --}}
    <details class="panel mt-6 p-4" @if (! $photo) open @endif>
        <summary class="cursor-pointer font-display text-lg text-ink">Frosting and plate <span class="text-base text-ink/80">(only used without a cake photo)</span></summary>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/90">
            The drawn cake has frosting on top and a plate under it, the same for every flavor. Transparent PNGs work best:
            frosting about 1400 × 400 px, plate about 1800 × 160 px.
        </p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ($parts as $part)
                <x-visual-card :item="$part" type="part" :ratio="$part->key === 'plate' ? '11.4 / 1' : '3 / 1'" fit="contain" />
            @endforeach
        </div>
    </details>

    {{-- Live 3D preview of one flavor's 3D look, opened from its card. It stays open while the grain size is moved. --}}
    @php
        $surfaceOf = fn ($flavor, string $type) => $flavor ? [
            'color' => $flavor->color,
            'side' => $flavor->side_texture ? asset($flavor->side_texture) : null,
            'top' => $type === 'cream' && $flavor->top_texture ? asset($flavor->top_texture) : null,
            'scale' => (float) $flavor->tile_scale,
        ] : [];
    @endphp
    <aside x-data="cakeSurfacePreview({ sponge: @js($surfaceOf($defaultSponge, 'sponge')), cream: @js($surfaceOf($defaultCream, 'cream')) })"
           @cake-3d-preview.window="open($event.detail)" @keydown.escape.window="close()"
           x-show="flavor" x-cloak x-transition
           class="fixed bottom-4 right-4 z-50 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-line bg-white shadow-xl"
           aria-label="3D preview">
        <div class="flex items-center justify-between gap-2 border-b border-line px-4 py-2.5">
            <p class="truncate text-sm font-semibold text-brand" x-text="flavor ? flavor.name + ' in 3D' : ''"></p>
            <button type="button" @click="close()" class="flex size-7 items-center justify-center rounded-full text-ink/80 hover:bg-petal hover:text-brand" aria-label="Close the 3D preview">&times;</button>
        </div>
        <div class="relative" style="aspect-ratio: 1; background: radial-gradient(70% 55% at 50% 45%, #fffcf5 0%, #fbf3e4 50%, #f1e4cc 100%);">
            <div x-ref="stage" class="absolute inset-0" tabindex="0" role="group" aria-roledescription="3D cake" aria-label="3D preview, drag or use the arrow keys to turn it"></div>
            <p x-show="error" x-text="error" class="absolute inset-0 grid place-items-center p-4 text-center text-sm text-ink"></p>
        </div>
        <p class="px-4 py-2.5 text-xs text-ink/80">Seams are smoothed out when you save. Move the grain size until the crumb looks natural.</p>
    </aside>

    <script>
        function cakeSurfacePreview(defaults) {
            let viewer = null; // kept out of Alpine's data, which would wrap three.js in proxies

            return {
                flavor: null,
                error: '',

                async open(flavor) {
                    this.flavor = flavor;
                    this.error = '';
                    try {
                        if (!viewer) {
                            if (!window.canDraw3d()) throw new Error('no WebGL');
                            const factory = await window.whenCake3d();
                            await this.$nextTick();
                            viewer = await factory.create(this.$refs.stage);
                        }
                        viewer.render(this.spec(flavor));
                    } catch (e) {
                        this.error = 'This browser can’t show the 3D preview.';
                    }
                },
                close() {
                    if (!this.flavor) return;
                    this.flavor = null;
                    window.dispatchEvent(new CustomEvent('cake-3d-preview-closed'));
                },
                // A 3-layer, 2 kg cake: this flavor on every layer, with the default sponge or cream for the other part.
                spec(flavor) {
                    const own = { color: flavor.color, side: flavor.side, top: flavor.top, scale: flavor.scale };
                    const layer = flavor.type === 'sponge' ? { sponge: own, cream: defaults.cream } : { sponge: defaults.sponge, cream: own };
                    return { kg: 2, layers: [layer, layer, layer] };
                },
            };
        }
    </script>
</x-studio-layout>
