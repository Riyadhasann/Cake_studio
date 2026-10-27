<x-studio-layout title="Cake shapes" :admin="true">
    <x-admin.header title="Cake shapes" description="Advanced. Where the sponge and cream layers sit in the cake photo, and how many layers customers can pick. The layers are fitted automatically when you upload the cake photo, so you only need this page to adjust them or add more layers.">
        <x-slot:actions>
            <a href="{{ route('admin.cake-visuals.index') }}" class="btn-outline btn-sm">Cake photo</a>
        </x-slot:actions>
    </x-admin.header>

    <x-admin.alerts />

    <div class="panel mt-5 overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <thead class="bg-petal text-xs font-medium tracking-wide text-ink/80">
                <tr>
                    <th class="px-5 py-3.5">Layers</th>
                    <th class="px-5 py-3.5">Cake photo</th>
                    <th class="px-5 py-3.5">Layers fitted</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($layerCounts as $layers)
                    @php $variant = $variants->get($layers); @endphp
                    <tr class="align-top">
                        <td class="px-5 py-4 font-display text-lg font-medium">{{ $layers }} layers</td>
                        @if ($variant)
                            @php $problems = $variant->publishProblems(); @endphp
                            <td class="px-5 py-4">
                                @if ($variant->base_image)
                                    <img src="{{ asset($variant->base_image) }}" alt="" class="h-12 rounded-lg border border-line">
                                @else
                                    <span class="text-ink/70">None (drawn cake)</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $variant->slots->count() === 2 * $variant->layers ? 'Yes' : $variant->slots->count().' of '.(2 * $variant->layers).' shapes' }}</td>
                            <td class="px-5 py-4">
                                @if ($variant->is_published)
                                    <span class="rounded-full bg-sage-soft px-3 py-1 text-xs text-sage">Published</span>
                                @else
                                    <span class="rounded-full bg-petal px-3 py-1 text-xs text-ink/80">Draft</span>
                                @endif
                                @if ($problems)
                                    <ul class="mt-2 max-w-xs list-inside list-disc text-xs text-clay">
                                        @foreach ($problems as $problem)
                                            <li>{{ $problem }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-4">
                                    <a href="{{ route('admin.cake-variants.edit', $variant) }}" class="btn-outline btn-sm">Adjust</a>
                                    @include('admin.cake-variants._publish', ['variant' => $variant, 'problems' => $problems])
                                    @if ($variant->isExtra())
                                        <form action="{{ route('admin.cake-variants.destroy', $variant) }}" method="POST"
                                              onsubmit="return confirm('Remove {{ $layers }} layers? Customers can no longer pick it. Orders already placed keep their layers.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-danger underline">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @else
                            <td class="px-5 py-4 text-ink/70" colspan="3">No drawing yet: the design page uses the layered preview.</td>
                            <td class="px-5 py-4">
                                <form action="{{ route('admin.cake-variants.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="layers" value="{{ $layers }}">
                                    <button type="submit" class="btn-outline btn-sm">Start drawing</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($addable)
        <section class="panel mt-5 p-5" aria-labelledby="add-layers-heading">
            <h2 id="add-layers-heading" class="text-base font-bold">Add a layer count</h2>
            <p class="mt-1.5 max-w-2xl text-sm text-ink/80">
                Let customers order a taller cake. With a cake photo up, the layers are fitted into its cut straight away;
                otherwise the design page shows the layered preview for it. The price doesn't change with the layer count.
            </p>
            <form action="{{ route('admin.cake-variants.store') }}" method="POST" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label for="add-layers" class="block text-sm font-medium">Layers</label>
                    <select id="add-layers" name="layers" class="field mt-1 w-40">
                        @foreach ($addable as $count)
                            <option value="{{ $count }}" @selected((int) old('layers', $addable[0]) === $count)>{{ $count }} layers</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn">Add</button>
            </form>
        </section>
    @endif
</x-studio-layout>
