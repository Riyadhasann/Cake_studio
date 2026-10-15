{{-- The cut face marked on the cake photo, inside the editor form's cakeVariantEditor() scope. --}}
<section class="panel space-y-5 p-6">
    <div>
        <h2 class="heading text-3xl">The cut</h2>
        <p class="mt-2 text-sm leading-6 text-ink/70">
            Found in the photo when it was uploaded; the dashed line on the preview shows it. Only if the layers don't line
            up: click "Mark the cut", then click its six corners on the preview (top edge left, middle, right, then bottom
            edge left, middle, right), press "Fit the layers" and save.
        </p>
    </div>

    <details>
        <summary class="cursor-pointer text-xs text-ink/70">Exact numbers</summary>
    <div class="mt-3 grid gap-3 sm:grid-cols-3">
        @foreach (['Top left', 'Top middle', 'Top right', 'Bottom left', 'Bottom middle', 'Bottom right'] as $p => $pointLabel)
            @php $edge = $p < 3 ? 'top' : 'bottom'; $i = $p % 3; @endphp
            <fieldset class="rounded-2xl border p-3" :class="placing === {{ $p }} ? 'border-brand' : 'border-line'">
                <legend class="px-1 text-xs font-medium text-ink">{{ $pointLabel }}</legend>
                <div class="grid grid-cols-2 gap-2">
                    <label class="text-xs text-ink/70">X
                        <input type="number" step="any" x-model.number="cutPoints[{{ $p }}].x" class="field mt-1 px-3 py-2">
                    </label>
                    <label class="text-xs text-ink/70">Y
                        <input type="number" step="any" x-model.number="cutPoints[{{ $p }}].y" class="field mt-1 px-3 py-2">
                    </label>
                </div>
                {{-- Sent only once all six points are set --}}
                <input type="hidden" name="cut_face[{{ $edge }}][{{ $i }}][0]" :value="cutPoints[{{ $p }}].x" :disabled="!cutComplete">
                <input type="hidden" name="cut_face[{{ $edge }}][{{ $i }}][1]" :value="cutPoints[{{ $p }}].y" :disabled="!cutComplete">
            </fieldset>
        @endforeach
    </div>
    </details>

    <div>
        <label for="sponge_share" class="label">Sponge thickness (the rest of each layer is cream): <span x-text="Math.round(spongeShare * 100) + '%'"></span></label>
        <input id="sponge_share" type="range" min="0.3" max="0.95" step="0.01" x-model.number="spongeShare" class="w-full accent-brand">
        <input type="hidden" name="cut_face[sponge_share]" :value="spongeShare" :disabled="!cutComplete">
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="button" class="btn-outline btn-sm" @click="placing = placing === null ? 0 : null"
                x-text="placing === null ? 'Mark the cut' : 'Click the ' + pointNames[placing] + ' corner (stop)'"></button>
        <button type="button" class="btn btn-sm" @click="fitShapes()" :disabled="!cutComplete">Fit the layers</button>
    </div>
    <p class="text-xs text-ink/60" x-show="fitted" x-cloak>Layers fitted. Press Save to keep them.</p>
    @foreach (['cut_face', 'cut_face.top.*.*', 'cut_face.bottom.*.*', 'cut_face.sponge_share'] as $field)
        @error($field)
            <p class="text-xs text-clay">{{ $message }}</p>
        @enderror
    @endforeach
</section>
