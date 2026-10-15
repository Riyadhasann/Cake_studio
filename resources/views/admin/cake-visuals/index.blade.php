<x-studio-layout title="Cake images" :admin="true">
    <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

    <h1 class="heading mt-6 text-5xl">Cake images</h1>
    <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/75">
        Upload your own image for every part of the cake. Each one is its own layer in the preview on the design page,
        so changing one flavor never changes another. Anything without an image uses the default drawing.
        Use PNG (transparent background works best), JPG or WebP, up to 5 MB each. About 500 KB is plenty and loads faster.
    </p>

    <div class="mt-8 space-y-4">
        @if (session('success'))
            <div class="rounded-2xl border border-sage/30 bg-sage-soft px-5 py-4 text-sm text-ink">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

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
    @endphp

    <section class="panel mt-12 p-6">
        <h2 class="heading text-3xl">Default cake preview</h2>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-ink/75">
            The design page opens with every layer as
            <strong class="text-ink">{{ $defaultSponge?->name ?? 'no' }} sponge</strong> with
            <strong class="text-ink">{{ $defaultCream?->name ?? 'no cream' }}</strong>.
            When a customer picks another sponge or cream, only that one layer changes.
            Use the "Use as default" button on a sponge or cream below to change this.
        </p>
    </section>

    <section class="mt-12">
        <h2 class="heading text-3xl">Frosting and plate</h2>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-ink/75">
            The frosting sits on top of the cake and the plate under it. They are the same for every flavor.
            Shown in full, never cropped. Frosting: a transparent PNG wider than tall, about 1400 x 400 px.
            Plate: a transparent PNG, wide and flat, about 1800 x 160 px.
        </p>
        <div class="mt-6 grid gap-6 sm:grid-cols-2">
            @foreach ($parts as $part)
                <x-visual-card :item="$part" type="part" :ratio="$part->key === 'plate' ? '11.4 / 1' : '3 / 1'" fit="contain" />
            @endforeach
        </div>
    </section>

    <section class="mt-14">
        <h2 class="heading text-3xl">Sponge layers</h2>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-ink/75">
            One image per sponge flavor: a wide strip of that sponge, about 1200 x 255 px, as PNG, JPG, WebP or SVG.
            If your shape is different it is cropped from the center, exactly as the preview below each name shows.
            Use the arrows next to a name to change the order flavors are listed in on the design page.
        </p>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($sponges as $sponge)
                <x-visual-card :item="$sponge" type="sponge" :drawings="$drawings" ratio="4.7 / 1" fit="cover"
                               :move-up="$swapped($sponges, $loop->index, -1)" :move-down="$swapped($sponges, $loop->index, 1)" />
            @endforeach
        </div>
    </section>

    <section class="mt-14">
        <h2 class="heading text-3xl">Cream layers</h2>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-ink/75">
            One image per cream flavor: a thin wide strip of that cream, about 1400 x 112 px, as PNG, JPG, WebP or SVG.
            It sits between the sponge layers and is slightly wider than the sponge.
        </p>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($creams as $cream)
                <x-visual-card :item="$cream" type="cream" :drawings="$drawings" ratio="12.5 / 1" fit="cover"
                               :move-up="$swapped($creams, $loop->index, -1)" :move-down="$swapped($creams, $loop->index, 1)" />
            @endforeach
        </div>
    </section>
</x-studio-layout>
