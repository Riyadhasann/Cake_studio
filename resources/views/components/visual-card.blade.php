{{-- One sponge, cream or fixed part on the Cake images page: its picture, upload and remove, and for flavors the menu order. --}}
@props(['item', 'type', 'ratio', 'fit' => 'contain', 'moveUp' => null, 'moveDown' => null])

<div class="rounded-xl border border-line bg-white p-4 {{ $type !== 'part' && ! $item->is_active ? 'border-dashed bg-bg-soft' : '' }}">
    <div class="flex items-center justify-between gap-2">
        <div class="flex min-w-0 items-center gap-2">
            @if ($item->color)
                <span class="size-3 shrink-0 rounded-full border border-black/15" style="background-color: {{ $item->color }}"></span>
            @endif
            <h3 class="truncate text-base font-bold">{{ $item->name }}</h3>
            @if ($type !== 'part' && $item->is_default)
                <span class="shrink-0 rounded-full bg-brand px-2 py-0.5 text-[0.65rem] font-medium text-cream">Default</span>
            @endif
            @if ($type !== 'part' && ! $item->is_active)
                <span class="shrink-0 rounded-full bg-petal px-2 py-0.5 text-[0.65rem] font-semibold text-text-secondary">Hidden</span>
            @endif
        </div>

        {{-- Menu order: each button posts the whole list with this flavor swapped one place. --}}
        <div class="flex shrink-0 items-center gap-1">
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
                        <button type="submit" class="flex size-7 items-center justify-center rounded-full text-xs text-ink/80 hover:bg-petal hover:text-brand"
                                aria-label="Move {{ $item->name }} {{ $direction }}" title="Move {{ $direction }}" @if ($refocus) autofocus @endif>{{ $direction === 'up' ? '▲' : '▼' }}</button>
                    </form>
                @endif
            @endforeach
        </div>
    </div>

    @if ($type !== 'part')
        <p class="mt-1 text-xs text-text-secondary">{{ (float) $item->extra_price > 0 ? '+৳'.number_format((float) $item->extra_price, 2) : 'No extra price' }}</p>
    @endif

    {{-- The checkerboard shows the see-through parts of the picture. --}}
    <div class="mt-3 overflow-hidden rounded-xl border border-line"
         style="aspect-ratio: {{ $ratio }}; background: repeating-conic-gradient(#f3e9e5 0% 25%, #ffffff 0% 50%) 50% / 16px 16px;">
        @if ($item->layer_image)
            <img src="{{ asset($item->layer_image) }}" alt="{{ $item->name }}" class="h-full w-full" style="object-fit: {{ $fit }};">
        @else
            <div class="flex h-full w-full items-center justify-center text-xs text-ink/80" @if ($item->color) style="background-color: {{ $item->color }};" @endif>
                <span class="rounded-full bg-white/85 px-2.5 py-1">No picture: {{ $type === 'part' ? 'drawn' : 'its color' }} is used</span>
            </div>
        @endif
    </div>

    <form action="{{ route('admin.cake-visuals.update', [$type, $item->id]) }}" method="POST" enctype="multipart/form-data" class="mt-3 flex items-center gap-2">
        @csrf
        <label for="image-{{ $type }}-{{ $item->id }}" class="sr-only">Picture for {{ $item->name }}</label>
        <input id="image-{{ $type }}-{{ $item->id }}" type="file" name="image" required
               accept="{{ $type === 'part' ? 'image/png,image/jpeg,image/webp' : 'image/png,image/jpeg,image/webp,image/svg+xml,.svg' }}"
               class="min-w-0 grow text-xs text-ink/90 file:mr-2 file:rounded-full file:border-0 file:bg-petal file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
        <button type="submit" class="btn btn-sm shrink-0 px-4">{{ $item->layer_image ? 'Replace' : 'Upload' }}</button>
    </form>

    @if ($item->layer_image)
        <form action="{{ route('admin.cake-visuals.destroy', [$type, $item->id]) }}" method="POST"
              onsubmit="return confirm({{ Js::from('Remove the picture of '.$item->name.'?') }})" class="mt-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-ink/80 underline underline-offset-4 hover:text-clay">Remove picture</button>
        </form>
    @endif

    {{-- Sponges and creams: name, price, color, on the menu or hidden, delete --}}
    @if ($type !== 'part')
        <details class="mt-4 border-t border-line pt-3" @if (old('edit_key') === "{$type}-{$item->id}") open @endif>
            <summary class="cursor-pointer text-sm font-semibold text-brand">Edit details</summary>
            <form action="{{ route('admin.flavors.update', [$type, $item->id]) }}" method="POST" class="mt-3 space-y-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_key" value="{{ $type }}-{{ $item->id }}">
                <div>
                    <label for="name-{{ $type }}-{{ $item->id }}" class="mb-1 block text-xs font-medium">Name</label>
                    <input id="name-{{ $type }}-{{ $item->id }}" name="name" value="{{ $item->name }}" required maxlength="60" class="field">
                </div>
                <div class="grid grid-cols-[1fr_5rem] gap-3">
                    <div>
                        <label for="price-{{ $type }}-{{ $item->id }}" class="mb-1 block text-xs font-medium">Extra price (৳)</label>
                        <input id="price-{{ $type }}-{{ $item->id }}" name="extra_price" type="number" min="0" step="0.01" value="{{ $item->extra_price }}" required class="field">
                    </div>
                    <div>
                        <label for="color-{{ $type }}-{{ $item->id }}" class="mb-1 block text-xs font-medium">Color</label>
                        <input id="color-{{ $type }}-{{ $item->id }}" name="color" type="color" value="{{ $item->color ?: '#e8cfa4' }}" class="h-[2.4rem] w-full cursor-pointer rounded-lg border border-line bg-white p-1">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($item->is_active) class="size-4 accent-brand">
                    On the menu (customers can pick it)
                </label>
                <button type="submit" class="btn btn-sm">Save details</button>
            </form>
            <form action="{{ route('admin.flavors.destroy', [$type, $item->id]) }}" method="POST" class="mt-3"
                  onsubmit="return confirm({{ Js::from('Delete '.$item->name.' and its pictures? This cannot be undone.') }})">
                @csrf
                @method('DELETE')
                <input type="hidden" name="edit_key" value="{{ $type }}-{{ $item->id }}">
                <button type="submit" class="text-xs text-danger underline underline-offset-4">Delete flavor</button>
            </form>
        </details>
    @endif

    {{-- Sponges and creams: the close-up picture the 3D cake wraps around the layer --}}
    @if ($type !== 'part')
        @php
            $key = "{$type}-{$item->id}";
            $side = $item->side_texture ? asset($item->side_texture) : null;
            $top = $type === 'cream' && $item->top_texture ? asset($item->top_texture) : null;
            $failed = $errors->hasAny(['side', 'top', 'tile_scale']) && old('surface_key') === $key;
        @endphp
        <details class="mt-4 border-t border-line pt-3" @if ($failed) open @endif
                 x-data="{ key: @js($key), scale: @js((float) (old('surface_key') === $key ? old('tile_scale', $item->tile_scale) : $item->tile_scale)), side: null, top: null, previewing: false }"
                 x-effect="if (previewing) $dispatch('cake-3d-preview', { key, type: @js($type), name: @js($item->name), color: @js($item->color), side: side ?? @js($side), top: top ?? @js($top), scale })"
                 @cake-3d-preview.window="previewing = $event.detail.key === key"
                 @cake-3d-preview-closed.window="previewing = false">
            <summary class="cursor-pointer text-sm font-semibold text-brand">
                3D look <span class="font-normal text-ink/80">· {{ $side ? 'picture saved' : 'drawn from its color' }}</span>
            </summary>

            <p class="mt-2 text-xs leading-5 text-ink/80">
                A close-up photo of the {{ $type === 'sponge' ? 'crumb' : 'cream surface' }} that fills the whole picture: no plate,
                background, knife or spoon, in even daylight. JPG, PNG or WebP, 512 to 2000 px. A very wide strip (like 3072 × 96)
                is wrapped once around the cake instead of repeated.
            </p>

            @if ($side || $top)
                <div class="mt-3 flex gap-2">
                    @foreach (array_filter(['Side' => $side, 'Top' => $top]) as $label => $url)
                        <figure class="text-center text-[0.65rem] text-ink/80">
                            <img src="{{ $url }}" alt="{{ $label }} picture of {{ $item->name }}" class="size-16 rounded-lg border border-line object-cover">
                            <figcaption class="mt-1">{{ $label }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif

            @if ($failed)
                <ul class="mt-3 space-y-1 text-xs text-clay" role="alert">
                    @foreach (['side', 'top', 'tile_scale'] as $field)
                        @error($field) <li>{{ $message }}</li> @enderror
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('admin.cake-visuals.surface.update', [$type, $item->id]) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3">
                @csrf
                <input type="hidden" name="surface_key" value="{{ $key }}">
                <div>
                    <label for="side-{{ $key }}" class="mb-1 block text-xs font-medium">Side picture</label>
                    <input id="side-{{ $key }}" type="file" name="side" accept="image/png,image/jpeg,image/webp"
                           @change="side = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                           class="w-full text-xs text-ink/90 file:mr-2 file:rounded-full file:border-0 file:bg-petal file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
                </div>
                @if ($type === 'cream')
                    <div>
                        <label for="top-{{ $key }}" class="mb-1 block text-xs font-medium">Top picture <span class="font-normal text-ink/80">(optional, square, shown once on top)</span></label>
                        <input id="top-{{ $key }}" type="file" name="top" accept="image/png,image/jpeg,image/webp"
                               @change="top = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                               class="w-full text-xs text-ink/90 file:mr-2 file:rounded-full file:border-0 file:bg-petal file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
                    </div>
                @endif
                <div>
                    <label for="scale-{{ $key }}" class="mb-1 flex justify-between text-xs font-medium">
                        Grain size <span class="font-normal text-ink/80" x-text="scale.toFixed(2) + '×'"></span>
                    </label>
                    <input id="scale-{{ $key }}" type="range" name="tile_scale" min="0.25" max="4" step="0.05" x-model.number="scale" class="w-full accent-brand">
                </div>
                <label class="flex items-center gap-2 text-xs">
                    <input type="hidden" name="seamless" value="0">
                    <input type="checkbox" name="seamless" value="1" checked class="accent-brand">
                    Make it seamless automatically
                </label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="previewing = true" class="btn-outline btn-sm px-4">Preview in 3D</button>
                    <button type="submit" class="btn btn-sm px-4">Save 3D look</button>
                </div>
            </form>

            @if ($side || $top)
                <form action="{{ route('admin.cake-visuals.surface.destroy', [$type, $item->id]) }}" method="POST"
                      onsubmit="return confirm({{ Js::from('Remove the 3D pictures of '.$item->name.'?') }})" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-ink/80 underline underline-offset-4 hover:text-clay">Remove 3D pictures</button>
                </form>
            @endif
        </details>
    @endif
</div>
