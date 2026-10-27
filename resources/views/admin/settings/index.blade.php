<x-studio-layout title="Shop & pictures" :admin="true">
    @php
        $fileInput = 'w-full text-sm text-ink/90 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-xs file:font-semibold file:text-brand-dark hover:file:bg-brand/20';
    @endphp

    <x-admin.header title="Shop & pictures" description="Your shop's name, contact details and every picture around the cakes. Anything you leave empty keeps the original." />

    <x-admin.alerts />

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Words --}}
        <section class="admin-card" aria-labelledby="details-heading">
            <h2 id="details-heading" class="text-lg font-bold">Shop details</h2>
            <p class="admin-hint mt-1">Shown in the browser tab, at the top of every page and in the footer.</p>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="label">Shop name</label>
                    <input id="name" name="name" value="{{ old('name', $settings->get('name')) }}" required maxlength="60" class="field">
                </div>
                <div>
                    <label for="tagline" class="label">Tagline <span class="font-normal text-text-muted">(for search engines and link previews)</span></label>
                    <input id="tagline" name="tagline" value="{{ old('tagline', $settings->get('tagline')) }}" maxlength="160" class="field">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="label">Phone</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $settings->get('phone')) }}" maxlength="40" class="field" placeholder="e.g. +880 1700 000000">
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $settings->get('email')) }}" maxlength="120" class="field" placeholder="hello@example.com">
                    </div>
                </div>

                <div class="border-t border-line pt-4">
                    <h3 class="text-sm font-bold">Design page heading</h3>
                    <p class="mt-0.5 text-xs text-text-secondary">The words on the title banner. The heading's last word is shown in green.</p>
                </div>
                <div>
                    <label for="hero_title" class="label">Heading</label>
                    <input id="hero_title" name="hero_title" value="{{ old('hero_title', $settings->has('hero_title') ? $settings->get('hero_title') : '') }}" maxlength="80" class="field" placeholder="{{ $settings->get('hero_title') }}">
                </div>
                <div>
                    <label for="hero_text" class="label">Text under the heading</label>
                    <textarea id="hero_text" name="hero_text" rows="3" maxlength="400" class="field" placeholder="Stack 2 to 4 layers, choose a sponge and a cream for each, add a design and a message, then pick a size.">{{ old('hero_text', $settings->has('hero_text') ? $settings->get('hero_text') : '') }}</textarea>
                    <p class="mt-1 text-xs text-text-muted">Leave empty to keep the original text (it always shows the right number of layers).</p>
                </div>

                <button type="submit" class="btn">Save details</button>
            </form>
        </section>

        {{-- Pictures --}}
        <section aria-labelledby="pictures-heading">
            <h2 id="pictures-heading" class="text-lg font-bold">Pictures</h2>
            <p class="admin-hint mt-1">PNG, JPG or WebP (the logo, icon and background may also be SVG). Photos are made smaller automatically.</p>

            <div class="mt-4 space-y-4">
                @foreach ($images as $key => [$label, $hint])
                    @php
                        $current = $settings->imageUrl($key);
                        $shown = $current ?? $defaults[$key];
                    @endphp
                    <article class="admin-card" aria-labelledby="picture-{{ $key }}">
                        <div class="flex items-start gap-4">
                            <div class="{{ $key === 'banner' ? 'h-16 w-32' : 'size-16' }} grid shrink-0 place-items-center overflow-hidden rounded-lg border border-line bg-bg-soft">
                                @if ($shown)
                                    <img src="{{ $shown }}" alt="{{ $label }}{{ $current ? '' : ' (original)' }}" class="size-full {{ in_array($key, ['banner', 'scene'], true) ? 'object-cover' : 'object-contain p-1' }}">
                                @else
                                    <span class="px-1 text-center text-[0.65rem] leading-tight text-text-muted">Drawn icon</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 id="picture-{{ $key }}" class="font-bold">{{ $label }}</h3>
                                    <span class="rounded-md px-1.5 py-0.5 text-[0.65rem] font-bold uppercase {{ $current ? 'bg-brand-soft text-brand-dark' : 'bg-petal text-text-secondary' }}">{{ $current ? 'Yours' : 'Original' }}</span>
                                </div>
                                <p class="mt-0.5 text-sm text-text-secondary">{{ $hint }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <form action="{{ route('admin.settings.images.update', $key) }}" method="POST" enctype="multipart/form-data" class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                                @csrf
                                <label for="image-{{ $key }}" class="sr-only">New {{ strtolower($label) }}</label>
                                <input id="image-{{ $key }}" name="image" type="file" required
                                       accept="{{ $key === 'banner' ? 'image/png,image/jpeg,image/webp' : 'image/png,image/jpeg,image/webp,image/svg+xml,.svg' }}"
                                       class="{{ $fileInput }} min-w-0 flex-1">
                                <button type="submit" class="btn btn-sm">Upload</button>
                            </form>
                            @if ($current)
                                <form action="{{ route('admin.settings.images.destroy', $key) }}" method="POST"
                                      onsubmit="return confirm('Go back to the original {{ strtolower($label) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline btn-sm">Use original</button>
                                </form>
                            @endif
                        </div>

                        {{-- Where the cakes stand on an uploaded background --}}
                        @if ($key === 'scene' && $current)
                            <form action="{{ route('admin.settings.scene') }}" method="POST" class="mt-5 border-t border-line pt-4"
                                  x-data="{ foot: {{ round($settings->sceneFoot() * 100, 1) }} }">
                                @csrf
                                @method('PUT')
                                <p class="text-sm font-bold">Where is the table?</p>
                                <p class="mt-0.5 text-xs text-text-secondary">Move the green line to where a cake's stand should touch the table. Every cake picture stands on it.</p>
                                <div class="mt-3 grid gap-4 sm:grid-cols-[10rem_1fr] sm:items-center">
                                    <div class="relative overflow-hidden rounded-lg border border-line">
                                        <img src="{{ $current }}" alt="Your cake background" class="block w-full">
                                        <div class="pointer-events-none absolute inset-x-0 border-t-2 border-dashed border-brand" :style="{ top: foot + '%' }" aria-hidden="true">
                                            <span class="absolute left-1/2 size-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="scene-foot" class="label">Table height <span class="font-normal text-text-muted" x-text="Math.round(foot) + '% from the top'"></span></label>
                                        <input id="scene-foot" name="scene_foot" type="range" min="40" max="95" step="0.5" x-model.number="foot" class="w-full accent-brand">
                                        <button type="submit" class="btn btn-sm mt-3">Save table height</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-studio-layout>
