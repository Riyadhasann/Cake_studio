{{-- One sponge, cream or fixed part on the Cake images page: its picture, upload and remove, and for flavors the menu order. --}}
@props(['item', 'type', 'ratio', 'fit' => 'contain', 'moveUp' => null, 'moveDown' => null])

<div class="rounded-2xl border border-line bg-white p-4">
    <div class="flex items-center justify-between gap-2">
        <div class="flex min-w-0 items-center gap-2">
            @if ($item->color)
                <span class="size-3 shrink-0 rounded-full border border-black/15" style="background-color: {{ $item->color }}"></span>
            @endif
            <h3 class="truncate font-display text-xl font-medium">{{ $item->name }}</h3>
            @if ($type !== 'part' && $item->is_default)
                <span class="shrink-0 rounded-full bg-brand px-2 py-0.5 text-[0.65rem] font-medium text-white">Default</span>
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
                        <button type="submit" class="flex size-7 items-center justify-center rounded-full text-xs text-ink/60 hover:bg-petal hover:text-brand"
                                aria-label="Move {{ $item->name }} {{ $direction }}" title="Move {{ $direction }}" @if ($refocus) autofocus @endif>{{ $direction === 'up' ? '▲' : '▼' }}</button>
                    </form>
                @endif
            @endforeach
        </div>
    </div>

    {{-- The checkerboard shows the see-through parts of the picture. --}}
    <div class="mt-3 overflow-hidden rounded-xl border border-line"
         style="aspect-ratio: {{ $ratio }}; background: repeating-conic-gradient(#f3e9e5 0% 25%, #ffffff 0% 50%) 50% / 16px 16px;">
        @if ($item->layer_image)
            <img src="{{ asset($item->layer_image) }}" alt="{{ $item->name }}" class="h-full w-full" style="object-fit: {{ $fit }};">
        @else
            <div class="flex h-full w-full items-center justify-center text-xs text-ink/60" @if ($item->color) style="background-color: {{ $item->color }};" @endif>
                <span class="rounded-full bg-white/85 px-2.5 py-1">No picture: {{ $type === 'part' ? 'drawn' : 'its color' }} is used</span>
            </div>
        @endif
    </div>

    <form action="{{ route('admin.cake-visuals.update', [$type, $item->id]) }}" method="POST" enctype="multipart/form-data" class="mt-3 flex items-center gap-2">
        @csrf
        <label for="image-{{ $type }}-{{ $item->id }}" class="sr-only">Picture for {{ $item->name }}</label>
        <input id="image-{{ $type }}-{{ $item->id }}" type="file" name="image" required
               accept="{{ $type === 'part' ? 'image/png,image/jpeg,image/webp' : 'image/png,image/jpeg,image/webp,image/svg+xml,.svg' }}"
               class="min-w-0 grow text-xs text-ink/80 file:mr-2 file:rounded-full file:border-0 file:bg-petal file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
        <button type="submit" class="btn btn-sm shrink-0 px-4">{{ $item->layer_image ? 'Replace' : 'Upload' }}</button>
    </form>

    @if ($item->layer_image)
        <form action="{{ route('admin.cake-visuals.destroy', [$type, $item->id]) }}" method="POST"
              onsubmit="return confirm({{ Js::from('Remove the picture of '.$item->name.'?') }})" class="mt-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-ink/60 underline underline-offset-4 hover:text-clay">Remove picture</button>
        </form>
    @endif
</div>
