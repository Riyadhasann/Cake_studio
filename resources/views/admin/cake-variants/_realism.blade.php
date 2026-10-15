{{-- Optional realism for $variant: a shading image over the whole drawing and flavor photos per slot. --}}
@php
    $flavorNames = collect($flavors)->map(fn ($list) => $list->pluck('name', 'id'));
    $photosBySlot = $variant->flavorImages->groupBy(fn ($photo) => "{$photo->layer_no}-{$photo->flavor_type}");
    $fileInput = 'w-full text-sm text-ink/80 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft';
@endphp

<div class="mt-12 grid gap-8 lg:grid-cols-[minmax(0,26rem)_minmax(0,1fr)]">
    <section class="panel p-6">
        <h2 class="heading text-3xl">Shading</h2>
        <p class="mt-2 text-sm leading-6 text-ink/70">
            Optional. A grayscale PNG or WebP with the drawing's proportions ({{ $variant->view_box_width }} × {{ $variant->view_box_height }}),
            multiplied over the shapes: white changes nothing, gray darkens. It replaces the built-in soft shading. Up to 2 MB.
        </p>

        @if ($variant->shading_image)
            <img src="{{ asset($variant->shading_image) }}" alt="Current shading image" class="mt-4 max-h-48 rounded-xl border border-line bg-white">
        @endif

        <form action="{{ route('admin.cake-variants.shading.update', $variant) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <label for="shading" class="sr-only">Shading image</label>
            <input id="shading" type="file" name="shading" accept="image/png,image/webp" required class="{{ $fileInput }}">
            <button type="submit" class="btn-outline btn-sm w-full">{{ $variant->shading_image ? 'Replace shading' : 'Upload shading' }}</button>
        </form>

        @if ($variant->shading_image)
            <form action="{{ route('admin.cake-variants.shading.destroy', $variant) }}" method="POST"
                  onsubmit="return confirm('Remove the shading image?')" class="mt-3 text-center">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-ink/70 underline underline-offset-4 hover:text-clay">Remove shading</button>
            </form>
        @endif
    </section>

    <section class="panel p-6" x-data="{ slot: '1-sponge' }">
        <h2 class="heading text-3xl">Flavor photos</h2>
        <p class="mt-2 text-sm leading-6 text-ink/70">
            Optional. A photo of one flavor for one shape replaces that flavor's texture there, cropped from the center to fit the shape.
            PNG, JPG or WebP, up to 2 MB. Uploading again for the same shape and flavor replaces the photo.
        </p>

        <form action="{{ route('admin.cake-variants.photos.store', $variant) }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label for="photo-slot" class="label">Shape</label>
                <select id="photo-slot" x-model="slot" class="field">
                    @foreach (range($variant->layers, 1) as $layerNo)
                        @foreach (\App\Models\CakeVariantSlot::TYPES as $type)
                            <option value="{{ $layerNo }}-{{ $type }}">Layer {{ $layerNo }} {{ $type }}</option>
                        @endforeach
                    @endforeach
                </select>
                <input type="hidden" name="layer_no" :value="slot.split('-')[0]" value="1">
                <input type="hidden" name="flavor_type" :value="slot.split('-')[1]" value="sponge">
            </div>
            <div>
                @foreach ($flavors as $type => $list)
                    {{-- Only the list for the chosen shape's type is enabled, so only it is sent. --}}
                    <div x-show="slot.endsWith('-{{ $type }}')" @if ($type !== 'sponge') x-cloak @endif>
                        <label for="photo-flavor-{{ $type }}" class="label">{{ ucfirst($type) }} flavor</label>
                        <select id="photo-flavor-{{ $type }}" name="flavor_id" class="field"
                                :disabled="!slot.endsWith('-{{ $type }}')" @if ($type !== 'sponge') disabled @endif>
                            @foreach ($list->where('is_active', true) as $flavor)
                                <option value="{{ $flavor->id }}">{{ $flavor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <div class="sm:col-span-2">
                <label for="photo" class="sr-only">Photo</label>
                <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" required class="{{ $fileInput }}">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-sm">Upload photo</button>
            </div>
        </form>

        <div class="mt-6 divide-y divide-line border-t border-line">
            @forelse ($photosBySlot as $key => $photos)
                @php [$layerNo, $type] = explode('-', $key); @endphp
                <div class="py-4">
                    <p class="text-sm font-medium capitalize">Layer {{ $layerNo }} {{ $type }}</p>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @foreach ($photos as $photo)
                            <figure class="w-36">
                                <img src="{{ asset($photo->image) }}" alt="" class="h-16 w-36 rounded-lg border border-line object-cover">
                                <figcaption class="mt-1 flex items-center justify-between gap-2 text-xs text-ink/70">
                                    <span class="truncate">{{ $flavorNames[$type][$photo->flavor_id] ?? 'Deleted flavor' }}</span>
                                    <form action="{{ route('admin.cake-variants.photos.destroy', [$variant, $photo]) }}" method="POST"
                                          onsubmit="return confirm('Remove this photo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="underline underline-offset-4 hover:text-clay">Remove</button>
                                    </form>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="py-4 text-sm text-ink/60">No photos yet: every shape uses the flavor's texture, image or color.</p>
            @endforelse
        </div>
    </section>
</div>
