<x-studio-layout :title="'Edit '.$variant->layers.'-layer shape'" :admin="true">
    <x-back-link :href="route('admin.cake-variants.index')" label="Back to Cake shapes" />

    @php
        // One row per slot, bottom layer first (the drawing order); values from a failed save win.
        $slotRows = [];
        foreach (range(1, $variant->layers) as $layerNo) {
            foreach (\App\Models\CakeVariantSlot::TYPES as $type) {
                $saved = $variant->slots->first(fn ($slot) => $slot->layer_no === $layerNo && $slot->type === $type);
                $old = old("slots.$layerNo.$type", []);
                $slotRows[] = [
                    'layer' => $layerNo,
                    'type' => $type,
                    'mode' => $old['mode'] ?? 'path',
                    'path' => $old['path'] ?? ($saved?->svg_path ?? ''),
                    'x' => $old['x'] ?? '',
                    'y' => $old['y'] ?? '',
                    'width' => $old['width'] ?? '',
                    'height' => $old['height'] ?? '',
                    'radius' => $old['radius'] ?? '0',
                ];
            }
        }
        $problems = $variant->publishProblems();
    @endphp

    <div class="mt-6 flex flex-wrap items-end justify-between gap-6">
        <div>
            <h1 class="heading text-5xl">{{ $variant->layers }}-layer shape</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/90">
                Where the {{ $variant->layers }} sponge and cream layers sit in the cake. With a cake photo they are fitted into
                its cut automatically; this page is only needed to adjust them.
            </p>
        </div>
        <div class="flex items-center gap-4">
            @if ($variant->is_published)
                <span class="rounded-full bg-sage-soft px-3 py-1 text-xs text-sage">Published</span>
            @else
                <span class="rounded-full bg-petal px-3 py-1 text-xs text-ink/80">Draft</span>
            @endif
            @include('admin.cake-variants._publish', ['variant' => $variant, 'problems' => $problems])
        </div>
    </div>

    @include('admin.cake-variants._alerts')

    @if ($problems)
        <div class="mt-4 rounded-2xl border border-line bg-petal px-5 py-4 text-sm text-ink/90">
            <p class="font-medium">Before this shape can be published:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($problems as $problem)
                    <li>{{ $problem }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('admin.cake-variants._base')

    @php
        // Six points of the cut face for the editor: top left, middle, right, then bottom left, middle, right.
        $cut = old('cut_face', $cutFace);
        $cutPoints = [];
        foreach (['top', 'bottom'] as $edge) {
            foreach (range(0, 2) as $i) {
                $point = $cut[$edge][$i] ?? null;
                $cutPoints[] = ['x' => $point[0] ?? '', 'y' => $point[1] ?? ''];
            }
        }
    @endphp

    <form action="{{ route('admin.cake-variants.update', $variant) }}" method="POST"
          x-data="cakeVariantEditor({{ Js::from([
              'width' => (int) old('view_box_width', $variant->view_box_width),
              'height' => (int) old('view_box_height', $variant->view_box_height),
              'baseSvg' => old('base_svg', $variant->base_svg ?? ''),
              'baseImage' => $variant->base_image ? asset($variant->base_image) : null,
              'layers' => $variant->layers,
              'cutPoints' => $cutPoints,
              'spongeShare' => (float) ($cut['sponge_share'] ?? 0.72),
              'slots' => $slotRows,
              'elements' => $allowedElements,
              'attributes' => $allowedAttributes,
          ]) }})"
          class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)]">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            @php $handDrawn = ! $variant->base_image || $errors->hasAny(['view_box_width', 'view_box_height', 'base_svg', 'slots.*']); @endphp
            @if ($variant->base_image)
                @include('admin.cake-variants._cut-face')
            @endif

            {{-- Drawing the shapes by hand: needed without a photo, rarely with one --}}
            <details class="panel p-6" @if ($handDrawn) open @endif>
                <summary class="cursor-pointer font-display text-2xl text-ink">
                    {{ $variant->base_image ? 'Advanced: draw the shapes by hand' : 'Draw the shapes' }}
                </summary>
                <p class="mt-2 text-sm leading-6 text-ink/80">
                    One sponge and one cream shape per layer (layer 1 is the bottom), in view box units. The base SVG is drawn
                    on top of the shapes: keep outlines and decorations there, never sponge or cream. Scripts, links, images
                    and styles are removed when you save.
                </p>
                <div class="mt-6 space-y-8">
            <section class="space-y-5">
                <h2 class="heading text-3xl">Drawing</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="view_box_width" class="label">View box width</label>
                        <input id="view_box_width" type="number" name="view_box_width" x-model.number="width" required min="1" max="5000" step="1" class="field" @readonly($variant->base_image)>
                    </div>
                    <div>
                        <label for="view_box_height" class="label">View box height</label>
                        <input id="view_box_height" type="number" name="view_box_height" x-model.number="height" required min="1" max="5000" step="1" class="field" @readonly($variant->base_image)>
                    </div>
                </div>
                @if ($variant->base_image)
                    <p class="-mt-2 text-xs text-ink/80">Set by the cake photo: its size in pixels.</p>
                @endif
                <div>
                    <label for="base_svg" class="label">Base SVG (outline and decorations, drawn on top)</label>
                    <textarea id="base_svg" name="base_svg" x-model="baseSvg" rows="8" maxlength="20000" spellcheck="false"
                              placeholder='e.g. &lt;path d="M0 0H700" stroke="#2b3f39" fill="none"/&gt;'
                              class="field font-mono text-xs"></textarea>
                    <p class="mt-2 text-xs text-ink/80" x-show="!baseError">
                        Allowed: {{ implode(', ', $allowedElements) }}. Colors may refer to gradients defined here, e.g. <code>url(#shine)</code>.
                    </p>
                    <p class="mt-2 text-xs text-clay" x-show="baseError" x-cloak>The markup is not well-formed: check that every tag is closed.</p>
                </div>
            </section>


            <section>
                <h2 class="heading text-3xl">Shapes</h2>
                <p class="mt-2 text-sm text-ink/80">Enter an SVG path, or a rectangle that is turned into a path when you save. Leave a path empty to remove that shape.</p>

                <div class="mt-6 divide-y divide-line">
                    @foreach (array_reverse($slotRows, true) as $i => $row)
                        @php $key = "slots.{$row['layer']}.{$row['type']}"; $name = "slots[{$row['layer']}][{$row['type']}]"; @endphp
                        <fieldset class="space-y-3 py-5" @focusin="active = {{ $i }}" @mouseenter="active = {{ $i }}" @mouseleave="active = null">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <legend class="text-sm font-medium capitalize text-ink">
                                    <span class="mr-2 inline-block size-3 rounded-full align-middle {{ $row['type'] === 'sponge' ? 'bg-[#e9c79f]' : 'border border-[#d8bfa5] bg-[#fff7ec]' }}"></span>
                                    Layer {{ $row['layer'] }} {{ $row['type'] }}
                                </legend>
                                <div class="flex gap-4 text-xs text-ink/80">
                                    <label class="flex items-center gap-1.5"><input type="radio" name="{{ $name }}[mode]" value="path" x-model="slots[{{ $i }}].mode"> Path</label>
                                    <label class="flex items-center gap-1.5"><input type="radio" name="{{ $name }}[mode]" value="rect" x-model="slots[{{ $i }}].mode"> Rectangle</label>
                                </div>
                            </div>

                            <div x-show="slots[{{ $i }}].mode === 'path'">
                                <label for="slot-{{ $i }}-path" class="sr-only">Path for layer {{ $row['layer'] }} {{ $row['type'] }}</label>
                                <textarea id="slot-{{ $i }}-path" name="{{ $name }}[path]" x-model="slots[{{ $i }}].path" :disabled="slots[{{ $i }}].mode !== 'path'"
                                          rows="2" maxlength="10000" spellcheck="false" placeholder="M20 252H680V392H20Z" class="field font-mono text-xs"></textarea>
                            </div>

                            <div x-show="slots[{{ $i }}].mode === 'rect'" x-cloak class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                                @foreach (['x' => 'X', 'y' => 'Y', 'width' => 'Width', 'height' => 'Height', 'radius' => 'Corner radius'] as $field => $fieldLabel)
                                    <div>
                                        <label for="slot-{{ $i }}-{{ $field }}" class="mb-1 block text-xs text-ink/80">{{ $fieldLabel }}</label>
                                        <input id="slot-{{ $i }}-{{ $field }}" type="number" step="any" name="{{ $name }}[{{ $field }}]"
                                               x-model="slots[{{ $i }}].{{ $field }}" :disabled="slots[{{ $i }}].mode !== 'rect'" class="field px-3 py-2">
                                    </div>
                                @endforeach
                            </div>

                            @foreach (['path', 'x', 'y', 'width', 'height', 'radius'] as $field)
                                @error("$key.$field")
                                    <p class="text-xs text-clay">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
            </section>
                </div>
            </details>

            <button type="submit" class="btn">Save</button>
        </div>

        <aside class="lg:sticky lg:top-6 lg:self-start">
            <div class="panel p-6">
                <h2 class="heading text-3xl">Preview</h2>
                <p class="mt-1 text-xs text-ink/80">Sponge shapes are tan, cream shapes are light. Hover or edit a shape to highlight it.</p>
                <div class="mt-4 rounded-2xl bg-petal p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $variant->view_box_width }} {{ $variant->view_box_height }}"
                         :viewBox="`0 0 ${Math.max(1, width || 1)} ${Math.max(1, height || 1)}`"
                         class="mx-auto block h-auto max-h-[70vh] w-full" :class="placing !== null && 'cursor-crosshair'"
                         @click="placePoint($event)" role="img" aria-label="Preview of the {{ $variant->layers }}-layer cake shape">
                        <rect x="0" y="0" :width="width" :height="height" fill="none" stroke="#f1e2d6" stroke-dasharray="6 6" vector-effect="non-scaling-stroke" />
                        @if ($variant->base_image)
                            <image href="{{ asset($variant->base_image) }}" x="0" y="0" :width="width" :height="height" preserveAspectRatio="none" />
                        @endif
                        @foreach ($slotRows as $i => $row)
                            <path :d="pathOf(slots[{{ $i }}])"
                                  fill="{{ $row['type'] === 'sponge' ? '#e9c79f' : '#fff7ec' }}" @if ($variant->base_image) fill-opacity=".85" @endif
                                  :stroke="active === {{ $i }} ? '#b5562a' : '#d8bfa5'"
                                  :stroke-width="active === {{ $i }} ? 3 : 1"
                                  vector-effect="non-scaling-stroke" />
                        @endforeach
                        @if ($variant->shading_image)
                            <clipPath id="editor-slots">
                                @foreach ($slotRows as $i => $row)
                                    <path :d="pathOf(slots[{{ $i }}])" />
                                @endforeach
                            </clipPath>
                            <image href="{{ asset($variant->shading_image) }}" x="0" y="0" :width="width" :height="height" preserveAspectRatio="none"
                                   clip-path="url(#editor-slots)" style="mix-blend-mode: multiply" pointer-events="none" />
                        @endif
                        <g x-html="safeBase"></g>
                        @if ($variant->base_image)
                            {{-- The marked cut face and its six points --}}
                            <polygon :points="cutOutline" fill="none" stroke="#b5562a" stroke-width="2" stroke-dasharray="8 5" vector-effect="non-scaling-stroke" pointer-events="none" />
                            @foreach (range(0, 5) as $p)
                                <circle :cx="cutPoints[{{ $p }}].x" :cy="cutPoints[{{ $p }}].y" r="7" x-show="pointSet({{ $p }})"
                                        :fill="placing === {{ $p }} ? '#b5562a' : '#fff'" stroke="#b5562a" stroke-width="2" vector-effect="non-scaling-stroke" pointer-events="none" />
                            @endforeach
                        @endif
                    </svg>
                </div>
            </div>
        </aside>
    </form>

    <details class="mt-12">
        <summary class="cursor-pointer font-display text-2xl text-ink">Extra realism (optional): shading and per-layer photos</summary>
        @include('admin.cake-variants._realism')
    </details>

    <script>
        function cakeVariantEditor(config) {
            const SVG_NS = 'http://www.w3.org/2000/svg';

            // Same shape as CakeVariantSlot::roundedRectPath, so the preview matches what is saved.
            const fmt = (value) => String(Number(value.toFixed(2)));
            const roundedRectPath = (x, y, w, h, radius) => {
                const r = Math.max(0, Math.min(radius, w / 2, h / 2));
                if (r === 0) {
                    return `M${fmt(x)} ${fmt(y)}H${fmt(x + w)}V${fmt(y + h)}H${fmt(x)}Z`;
                }
                return `M${fmt(x + r)} ${fmt(y)}`
                    + `H${fmt(x + w - r)}A${fmt(r)} ${fmt(r)} 0 0 1 ${fmt(x + w)} ${fmt(y + r)}`
                    + `V${fmt(y + h - r)}A${fmt(r)} ${fmt(r)} 0 0 1 ${fmt(x + w - r)} ${fmt(y + h)}`
                    + `H${fmt(x + r)}A${fmt(r)} ${fmt(r)} 0 0 1 ${fmt(x)} ${fmt(y + h - r)}`
                    + `V${fmt(y + r)}A${fmt(r)} ${fmt(r)} 0 0 1 ${fmt(x + r)} ${fmt(y)}Z`;
            };

            // Same geometry as CakeVariantSlot::cutFacePaths, so the preview matches what the seeder makes.
            const fitBands = (cut, layers) => {
                const share = Math.max(0.05, Math.min(0.95, Number(cut.sponge_share)));
                const overlap = 0.002;
                const at = (depth) => cut.top.map((top, i) => [
                    top[0] + depth * (cut.bottom[i][0] - top[0]),
                    top[1] + depth * (cut.bottom[i][1] - top[1]),
                ]);
                const band = (from, to) => {
                    const points = [...at(Math.max(0, from - overlap)), ...at(Math.min(1, to + overlap)).reverse()];
                    return 'M' + points.map(([x, y]) => `${fmt(x)} ${fmt(y)}`).join('L') + 'Z';
                };
                const paths = {};
                for (let layerNo = 1; layerNo <= layers; layerNo++) {
                    const top = 1 - layerNo / layers;
                    const creamEnd = top + (1 - share) / layers;
                    paths[layerNo] = { sponge: band(creamEnd, 1 - (layerNo - 1) / layers), cream: band(top, creamEnd) };
                }
                return paths;
            };

            return {
                ...config,
                active: null,
                baseError: false,
                placing: null, // index of the cut face point the next click on the preview sets
                fitted: false,
                pointNames: ['top left', 'top middle', 'top right', 'bottom left', 'bottom middle', 'bottom right'],

                pointSet(p) {
                    const point = this.cutPoints[p];
                    return point.x !== '' && point.y !== '' && Number.isFinite(Number(point.x)) && Number.isFinite(Number(point.y));
                },
                get cutComplete() {
                    return this.cutPoints.every((_, p) => this.pointSet(p));
                },
                // The face's outline: along the top edge, then back along the bottom edge.
                get cutOutline() {
                    if (!this.cutComplete) return '';
                    const points = [...this.cutPoints.slice(0, 3), ...this.cutPoints.slice(3).reverse()];
                    return points.map((point) => `${point.x},${point.y}`).join(' ');
                },
                placePoint(event) {
                    if (this.placing === null || !this.baseImage) return;
                    const svg = event.currentTarget;
                    const point = new DOMPoint(event.clientX, event.clientY).matrixTransform(svg.getScreenCTM().inverse());
                    this.cutPoints[this.placing] = { x: Math.round(point.x), y: Math.round(point.y) };
                    this.placing = this.placing < 5 ? this.placing + 1 : null;
                },
                fitShapes() {
                    if (!this.cutComplete) return;
                    const xy = (point) => [Number(point.x), Number(point.y)];
                    const paths = fitBands({
                        top: this.cutPoints.slice(0, 3).map(xy),
                        bottom: this.cutPoints.slice(3).map(xy),
                        sponge_share: this.spongeShare,
                    }, this.layers);
                    this.slots.forEach((slot) => {
                        slot.mode = 'path';
                        slot.path = paths[slot.layer]?.[slot.type] ?? slot.path;
                    });
                    this.fitted = true;
                },

                pathOf(slot) {
                    if (slot.mode !== 'rect') {
                        return /^\s*[Mm][MmLlHhVvCcSsQqTtAaZz0-9eE\s,.+\-]*$/.test(slot.path) ? slot.path : '';
                    }
                    const [x, y, w, h, r] = [slot.x, slot.y, slot.width, slot.height, slot.radius || 0].map(Number);
                    return [x, y, w, h, r].every(Number.isFinite) && w > 0 && h > 0 ? roundedRectPath(x, y, w, h, r) : '';
                },

                // A browser-side copy of the server's allow-list, so the live preview never runs pasted markup.
                get safeBase() {
                    if (!this.baseSvg.trim()) {
                        this.baseError = false;
                        return '';
                    }
                    const doc = new DOMParser().parseFromString(`<svg xmlns="${SVG_NS}">${this.baseSvg}</svg>`, 'image/svg+xml');
                    if (doc.getElementsByTagName('parsererror').length) {
                        this.baseError = true;
                        return '';
                    }
                    this.baseError = false;

                    const unsafe = (value) => {
                        const compact = value.replace(/\s+/g, '').toLowerCase();
                        return /javascript:|data:|expression\(/.test(compact)
                            || (compact.match(/url\(([^)]*)\)/g) || []).some((ref) => !/^url\(["']?#[a-z0-9_-]+["']?\)$/.test(ref))
                            || (compact.match(/url\(/g) || []).length !== (compact.match(/url\([^)]*\)/g) || []).length;
                    };
                    const clean = (parent) => {
                        [...parent.childNodes].forEach((node) => {
                            const allowed = node.nodeType === Node.ELEMENT_NODE && node.namespaceURI === SVG_NS
                                && !node.prefix && this.elements.includes(node.localName);
                            if (allowed) {
                                [...node.attributes].forEach((attr) => {
                                    if (attr.namespaceURI || !this.attributes.includes(attr.name) || unsafe(attr.value)) {
                                        node.removeAttributeNode(attr);
                                    }
                                });
                                clean(node);
                            } else if (node.nodeType !== Node.TEXT_NODE || node.nodeValue.trim()) {
                                node.remove();
                            }
                        });
                    };
                    clean(doc.documentElement);

                    const serializer = new XMLSerializer();
                    return [...doc.documentElement.childNodes].map((node) => serializer.serializeToString(node)).join('');
                },
            };
        }
    </script>
</x-studio-layout>
