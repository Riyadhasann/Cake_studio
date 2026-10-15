@props(['item', 'type', 'ratio', 'fit' => 'cover', 'moveUp' => null, 'moveDown' => null, 'drawings' => []])

<div class="panel p-5">
    <div class="flex items-baseline justify-between gap-3">
        <div class="flex items-baseline gap-2">
            {{-- Menu order: each button posts the whole list with this flavor swapped one place. --}}
            @foreach (['up' => $moveUp, 'down' => $moveDown] as $direction => $ids)
                @if ($ids)
                    <form action="{{ route('admin.cake-visuals.reorder', $type) }}" method="POST">
                        @csrf
                        @foreach ($ids as $id)
                            <input type="hidden" name="ids[]" value="{{ $id }}">
                        @endforeach
                        @php
                            $focusKey = "{$type}-{$item->id}-{$direction}";
                            // After a move, focus this arrow again; at the top or bottom, the other arrow.
                            $refocus = in_array(session('focus'), [$focusKey, "{$type}-{$item->id}-".($direction === 'up' ? 'down' : 'up')], true)
                                && (session('focus') === $focusKey || ! ($direction === 'up' ? $moveDown : $moveUp));
                        @endphp
                        <input type="hidden" name="focus" value="{{ $focusKey }}">
                        <button type="submit" class="text-xs text-ink/60 hover:text-brand" aria-label="Move {{ $item->name }} {{ $direction }}" title="Move {{ $direction }}" @if ($refocus) autofocus @endif>{{ $direction === 'up' ? '▲' : '▼' }}</button>
                    </form>
                @endif
            @endforeach
            <h3 class="font-display text-2xl font-medium leading-snug">{{ $item->name }}</h3>
        </div>
        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $item->layer_image ? 'bg-sage-soft text-sage' : 'bg-petal text-ink/70' }}">
            {{ $item->layer_image ? 'Your image' : 'Default drawing' }}
        </span>
    </div>

    {{-- Same shape as the slot in the cake, so what you see here is how it will be cropped. The checkerboard shows transparency. --}}
    <div class="mt-4 overflow-hidden rounded-xl border border-line"
         style="aspect-ratio: {{ $ratio }}; background: repeating-conic-gradient(#f3e9e5 0% 25%, #ffffff 0% 50%) 50% / 16px 16px;">
        @if ($item->layer_image)
            <img src="{{ asset($item->layer_image) }}" alt="{{ $item->name }}" class="h-full w-full" style="object-fit: {{ $fit }};">
        @elseif ($item->color)
            <div class="h-full w-full" style="background-color: {{ $item->color }};"></div>
        @endif
    </div>

    <form action="{{ route('admin.cake-visuals.update', [$type, $item->id]) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
        @csrf
        <input type="file" name="image" accept="{{ $type === 'part' ? 'image/png,image/jpeg,image/webp' : 'image/png,image/jpeg,image/webp,image/svg+xml,.svg' }}" required
               class="w-full text-sm text-ink/80 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
        <button type="submit" class="btn btn-sm w-full">{{ $item->layer_image ? 'Replace image' : 'Upload image' }}</button>
    </form>

    @if ($item->layer_image)
        <form action="{{ route('admin.cake-visuals.destroy', [$type, $item->id]) }}" method="POST"
              onsubmit="return confirm('Remove this image and go back to the default drawing?')" class="mt-3 text-center">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-ink/70 underline underline-offset-4 hover:text-clay">Remove image</button>
        </form>
    @endif

    {{-- Sponges and creams only: a square tile repeated across the slots of the cake drawing. --}}
    @if ($type !== 'part')
        @php $savedTexture = $item->texture_image ? asset($item->texture_image) : null; @endphp
        {{-- url: the file chosen in the form (before saving), else the saved texture --}}
        <div class="mt-4 border-t border-line pt-4" x-data="{ url: @js($savedTexture), saved: @js($savedTexture) }">
            <div class="flex items-center gap-3">
                <div class="size-14 shrink-0 rounded-lg border border-line"
                     style="{{ $item->texture_image ? "background: url('" . asset($item->texture_image) . "') 0 0 / 28px 28px repeat;" : 'background-color: ' . ($item->color ?? '#ffffff') . ';' }}"></div>
                <p class="text-xs leading-5 text-ink/70">
                    {{ $item->texture_image ? 'Texture uploaded (shown tiled).' : 'No texture yet.' }}
                    Square PNG or WebP that tiles seamlessly, 256 to 1024 px, up to 2 MB.
                </p>
            </div>

            <form action="{{ route('admin.cake-visuals.texture.update', [$type, $item->id]) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3">
                @csrf
                <input type="file" name="texture" accept="image/png,image/webp" required
                       @change="if (url && url !== saved) URL.revokeObjectURL(url); url = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : saved"
                       class="w-full text-sm text-ink/80 file:mr-3 file:rounded-full file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
                <button type="submit" class="btn-outline btn-sm w-full">{{ $item->texture_image ? 'Replace texture' : 'Upload texture' }}</button>
            </form>

            @if ($drawings)
                <x-texture-preview :drawings="$drawings" :type="$type" :id="$type.'-'.$item->id" :color="$item->color" />
            @endif

            @if ($item->texture_image)
                <form action="{{ route('admin.cake-visuals.texture.destroy', [$type, $item->id]) }}" method="POST"
                      onsubmit="return confirm('Remove this texture?')" class="mt-3 text-center">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-ink/70 underline underline-offset-4 hover:text-clay">Remove texture</button>
                </form>
            @endif
        </div>
    @endif

    {{-- Sponges and creams only: the flavor every layer starts with in the design page. --}}
    @if ($type !== 'part')
        <div class="mt-4 border-t border-line pt-4 text-center">
            @if ($item->is_default)
                <span class="inline-block rounded-full bg-brand px-3 py-1 text-xs font-medium text-white">Default in the cake preview</span>
            @else
                <form action="{{ route('admin.cake-visuals.default', [$type, $item->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-outline btn-sm w-full">Use as default {{ $type }}</button>
                </form>
            @endif
        </div>
    @endif
</div>
