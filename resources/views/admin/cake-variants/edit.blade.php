<x-studio-layout :title="'Edit '.$variant->layers.'-layer cake'" :admin="true">
    <x-back-link :href="route('admin.cake-variants.index')" label="Back to Cake shapes" />

    <h1 class="heading mt-6 text-5xl">{{ $variant->layers }}-layer cake</h1>
    <p class="mt-3 max-w-3xl text-sm leading-7 text-ink/75">
        Draw in SVG units inside the view box. Layer 1 is the bottom layer. Give each slot an SVG path
        (the <code>d</code> of a <code>&lt;path&gt;</code>) or a rectangle with rounded corners. An empty slot is removed.
        The base SVG is drawn above the slots (outline, decoration) and only keeps shapes:
        scripts, links, styles and event handlers are removed when you save.
    </p>

    @include('admin.cake-variants._alerts')

    @php
        $problems = $variant->publishProblems();

        // Top layer first, cream above its sponge, like the cake itself.
        $slots = [];
        foreach (range($variant->layers, 1) as $layerNo) {
            foreach (['cream', 'sponge'] as $type) {
                $saved = $variant->slots->first(fn ($s) => $s->layer_no === $layerNo && $s->type === $type);
                $old = "slots.{$layerNo}.{$type}";
                $slots[] = [
                    'key' => "{$layerNo}-{$type}",
                    'layer' => $layerNo,
                    'type' => $type,
                    'mode' => old("{$old}.mode", 'path'),
                    'd' => (string) old("{$old}.d", $saved?->svg_path ?? ''),
                    'x' => old("{$old}.x", ''),
                    'y' => old("{$old}.y", ''),
                    'width' => old("{$old}.width", ''),
                    'height' => old("{$old}.height", ''),
                    'radius' => old("{$old}.radius", ''),
                ];
            }
        }
    @endphp

    <div x-data="variantEditor({
            width: @js((int) old('view_box_width', $variant->view_box_width)),
            height: @js((int) old('view_box_height', $variant->view_box_height)),
            baseSvg: @js((string) old('base_svg', $variant->base_svg ?? '')),
            slots: @js($slots),
            elements: @js($allowedElements),
            attributes: @js($allowedAttributes),
         })"
         class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">

        {{-- Live preview --}}
        <section class="panel p-6 lg:sticky lg:top-28">
            <div class="flex items-center justify-between gap-3">
                <h2 class="heading text-3xl">Preview</h2>
                @if ($variant->is_published)
                    <span class="rounded-full bg-sage-soft px-3 py-1 text-xs font-medium text-sage">Published</span>
                @else
                    <span class="rounded-full bg-petal px-3 py-1 text-xs font-medium text-ink/70">Not published</span>
                @endif
            </div>

            <div class="mt-5 rounded-2xl bg-petal p-4">
                <svg x-ref="preview" x-effect="render()" xmlns="http://www.w3.org/2000/svg" class="mx-auto max-h-[28rem] w-full"
                     role="img" aria-label="Preview of the cake shape"></svg>
            </div>

            <div class="mt-4 flex flex-wrap gap-4 text-xs text-ink/70">
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm border border-brand bg-[#e9c9a1]"></span>Sponge</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm border border-brand bg-[#f6dfe4]"></span>Cream</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm border-2 border-[#2f6fde]"></span>Slot you are editing</span>
            </div>
            <p x-show="baseError" x-cloak class="mt-3 text-sm text-clay" x-text="baseError"></p>

            <div class="mt-6 border-t border-line pt-5">
                <h3 class="text-sm font-medium text-ink">Publishing (saved version)</h3>
                @if ($problems === [])
                    <p class="mt-2 text-sm text-ink/70">All {{ 2 * $variant->layers }} slots are set.</p>
                @else
                    <ul class="mt-2 list-inside list-disc text-sm text-clay">
                        @foreach ($problems as $problem)
                            <li>{{ $problem }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-4">
                    @if ($variant->is_published)
                        <form action="{{ route('admin.cake-variants.unpublish', $variant) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-outline btn-sm">Unpublish</button>
                        </form>
                    @else
                        <form action="{{ route('admin.cake-variants.publish', $variant) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm">Publish</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        {{-- Form --}}
        <form action="{{ route('admin.cake-variants.update', $variant) }}" method="POST" class="panel space-y-6 p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="view_box_width" class="label">View box width</label>
                    <input id="view_box_width" type="number" name="view_box_width" x-model.number="width" required min="1" max="5000" class="field">
                </div>
                <div>
                    <label for="view_box_height" class="label">View box height</label>
                    <input id="view_box_height" type="number" name="view_box_height" x-model.number="height" required min="1" max="5000" class="field">
                </div>
            </div>

            <div>
                <label for="base_svg" class="label">Base SVG (optional)</label>
                <textarea id="base_svg" name="base_svg" x-model="baseSvg" rows="5" maxlength="20000" spellcheck="false"
                          placeholder='e.g. <path d="M0 0H700" stroke="#75483f" fill="none" />'
                          class="field font-mono text-xs"></textarea>
            </div>

            <div class="space-y-4">
                <h2 class="heading text-3xl">Slots</h2>

                <template x-for="slot in slots" :key="slot.key">
                    <div role="group" :aria-label="`Layer ${slot.layer} ${slot.type}`" class="rounded-2xl border p-4 transition-colors"
                              :class="active === slot.key ? 'border-[#2f6fde]' : 'border-line'"
                              @focusin="active = slot.key">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-sm font-medium text-ink" x-text="`Layer ${slot.layer} ${slot.type}`"></span>
                            <div class="flex gap-4 text-xs text-ink/70">
                                <label class="flex items-center gap-1.5">
                                    <input type="radio" value="path" x-model="slot.mode" :name="`slots[${slot.layer}][${slot.type}][mode]`"> Path
                                </label>
                                <label class="flex items-center gap-1.5">
                                    <input type="radio" value="rect" x-model="slot.mode" :name="`slots[${slot.layer}][${slot.type}][mode]`"> Rectangle
                                </label>
                            </div>
                        </div>

                        <div x-show="slot.mode === 'path'" class="mt-3">
                            <textarea x-model="slot.d" :name="`slots[${slot.layer}][${slot.type}][d]`" :disabled="slot.mode !== 'path'"
                                      :aria-label="`Layer ${slot.layer} ${slot.type} path`"
                                      rows="2" maxlength="10000" spellcheck="false" placeholder="M20 252H680V392H20Z"
                                      class="field font-mono text-xs"></textarea>
                        </div>

                        <div x-show="slot.mode === 'rect'" x-cloak class="mt-3 grid grid-cols-5 gap-2">
                            <template x-for="field in ['x', 'y', 'width', 'height', 'radius']" :key="field">
                                <label class="text-xs text-ink/70">
                                    <span x-text="field === 'radius' ? 'corner' : field"></span>
                                    <input type="number" step="any" min="0" x-model="slot[field]"
                                           :name="`slots[${slot.layer}][${slot.type}][${field}]`" :disabled="slot.mode !== 'rect'"
                                           class="field mt-1 px-2 py-2">
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <button type="submit" class="btn">Save cake shape</button>
        </form>
    </div>

    <script>
        function variantEditor(config) {
            const svgNs = 'http://www.w3.org/2000/svg';
            const fills = { sponge: '#e9c9a1', cream: '#f6dfe4' };

            // Same as CakeVariantSlot::roundedRectPath(), so the preview matches what is saved.
            const fmt = (v) => String(Number(v.toFixed(2)));
            const rectPath = (x, y, w, h, radius) => {
                const r = Math.max(0, Math.min(radius, w / 2, h / 2));
                if (r === 0) {
                    return `M${fmt(x)} ${fmt(y)}H${fmt(x + w)}V${fmt(y + h)}H${fmt(x)}Z`;
                }
                const a = `A${fmt(r)} ${fmt(r)} 0 0 1`;
                return `M${fmt(x + r)} ${fmt(y)}H${fmt(x + w - r)}${a} ${fmt(x + w)} ${fmt(y + r)}`
                    + `V${fmt(y + h - r)}${a} ${fmt(x + w - r)} ${fmt(y + h)}`
                    + `H${fmt(x + r)}${a} ${fmt(x)} ${fmt(y + h - r)}`
                    + `V${fmt(y + r)}${a} ${fmt(x + r)} ${fmt(y)}Z`;
            };

            return {
                ...config,
                active: null,
                baseError: '',

                pathFor(slot) {
                    if (slot.mode === 'rect') {
                        const [x, y, w, h, r] = ['x', 'y', 'width', 'height', 'radius'].map((f) => parseFloat(slot[f]) || 0);
                        return w > 0 && h > 0 ? rectPath(x, y, w, h, r) : '';
                    }
                    return slot.d.trim();
                },

                // The base SVG with only the shapes the server keeps; never inserted as raw HTML.
                cleanBase() {
                    this.baseError = '';
                    if (this.baseSvg.trim() === '') {
                        return [];
                    }
                    const doc = new DOMParser().parseFromString(`<svg xmlns="${svgNs}">${this.baseSvg}</svg>`, 'image/svg+xml');
                    if (doc.getElementsByTagName('parsererror').length) {
                        this.baseError = 'The base SVG is not valid markup yet. Check that every tag is closed.';
                        return [];
                    }
                    const clean = (el) => {
                        [...el.children].forEach((child) => {
                            if (child.namespaceURI !== svgNs || ! this.elements.includes(child.localName)) {
                                child.remove();
                                return;
                            }
                            [...child.attributes].forEach((attr) => {
                                const value = attr.value;
                                const ok = attr.namespaceURI === null && this.attributes.includes(attr.localName)
                                    && (value.toLowerCase().includes('url(')
                                        ? /^\s*url\(\s*#[A-Za-z_][\w.-]*\s*\)\s*$/.test(value)
                                        : ! /javascript:|data:|expression\s*\(|[<>]/i.test(value));
                                if (! ok) {
                                    child.removeAttributeNode(attr);
                                }
                            });
                            clean(child);
                        });
                    };
                    clean(doc.documentElement);
                    return [...doc.documentElement.children];
                },

                render() {
                    const svg = this.$refs.preview;
                    const w = Math.max(1, this.width || 1);
                    const h = Math.max(1, this.height || 1);
                    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
                    svg.replaceChildren();

                    const outline = document.createElementNS(svgNs, 'rect');
                    Object.entries({ x: 0, y: 0, width: w, height: h, fill: 'none', stroke: '#90595433', 'stroke-dasharray': '6 6' })
                        .forEach(([k, v]) => outline.setAttribute(k, v));
                    svg.appendChild(outline);

                    this.slots.forEach((slot) => {
                        const d = this.pathFor(slot);
                        if (! d) {
                            return;
                        }
                        const path = document.createElementNS(svgNs, 'path');
                        path.setAttribute('d', d);
                        path.setAttribute('fill', fills[slot.type]);
                        path.setAttribute('stroke', this.active === slot.key ? '#2f6fde' : '#905954');
                        path.setAttribute('stroke-width', this.active === slot.key ? 4 : 2);
                        svg.appendChild(path);
                    });

                    const base = document.createElementNS(svgNs, 'g');
                    this.cleanBase().forEach((node) => base.appendChild(document.importNode(node, true)));
                    svg.appendChild(base);
                },
            };
        }
    </script>
</x-studio-layout>
