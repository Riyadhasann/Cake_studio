{{-- The cake photo under $variant's shapes: the whole cake with an empty cut face that the flavors fill. --}}
@php
    $fileInput = 'w-full text-sm text-ink/90 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft';
@endphp

<section class="panel mt-5 grid gap-4 p-4 md:grid-cols-[minmax(0,16rem)_minmax(0,1fr)]">
    <div>
        @if ($variant->base_image)
            <img src="{{ asset($variant->base_image) }}" alt="Current cake photo" class="w-full rounded-xl border border-line bg-white">
            <p class="mt-2 text-xs text-ink/80">{{ $variant->view_box_width }} × {{ $variant->view_box_height }} px</p>
        @else
            <div class="flex aspect-square items-center justify-center rounded-xl border border-dashed border-line bg-petal px-4 text-center text-xs text-ink/80">
                No cake photo yet: the shapes are drawn on their own.
            </div>
        @endif
    </div>

    <div>
        <h2 class="text-lg font-bold">Cake photo</h2>
        <p class="mt-2 text-sm leading-6 text-ink/80">
            The whole cake with a slice cut out and the cut left empty (see-through, or a gray checkerboard). The cut is
            found automatically and the layers are fitted into it. PNG, JPG or WebP, up to 4 MB. Usually set once for
            all layer counts on the <a href="{{ route('admin.cake-visuals.index') }}" class="underline">Cake images</a> page.
        </p>

        <form action="{{ route('admin.cake-variants.base.update', $variant) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <label for="base" class="sr-only">Cake photo</label>
            <input id="base" type="file" name="base" accept="image/png,image/jpeg,image/webp" required class="{{ $fileInput }}">
            <label class="flex items-center gap-2 text-sm text-ink/90">
                <input type="hidden" name="all_layers" value="0">
                <input type="checkbox" name="all_layers" value="1" checked class="accent-brand">
                Use this photo for every layer count (2, 3 and 4)
            </label>
            @error('base')
                <p class="text-xs text-clay">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn btn-sm">{{ $variant->base_image ? 'Replace photo' : 'Upload photo' }}</button>
        </form>

        @if ($variant->base_image)
            <form action="{{ route('admin.cake-variants.base.destroy', $variant) }}" method="POST"
                  onsubmit="return confirm('Remove the cake photo? The drawing is unpublished until its shapes are drawn again.')" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-ink/80 underline underline-offset-4 hover:text-clay">Remove photo</button>
            </form>
        @endif
    </div>
</section>
