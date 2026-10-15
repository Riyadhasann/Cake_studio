<x-studio-layout title="Cake shapes" :admin="true">
    <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

    <h1 class="heading mt-6 text-5xl">Cake shapes</h1>
    <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/75">
        Where the sponge and cream layers sit in the cake, for 2, 3 and 4 layers. When you upload the cake photo on the
        <a href="{{ route('admin.cake-visuals.index') }}" class="underline">Cake images</a> page, the layers are fitted
        into its cut automatically, so you only need this page to adjust them. Only published shapes are shown to customers.
    </p>

    @include('admin.cake-variants._alerts')

    <div class="panel mt-8 overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <thead class="bg-petal text-xs font-medium tracking-wide text-ink/70">
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
                        <td class="px-5 py-4 font-display text-2xl font-medium">{{ $layers }} layers</td>
                        @if ($variant)
                            @php $problems = $variant->publishProblems(); @endphp
                            <td class="px-5 py-4">
                                @if ($variant->base_image)
                                    <img src="{{ asset($variant->base_image) }}" alt="" class="h-12 rounded-lg border border-line">
                                @else
                                    <span class="text-ink/50">None (drawn cake)</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $variant->slots->count() === 2 * $variant->layers ? 'Yes' : $variant->slots->count().' of '.(2 * $variant->layers).' shapes' }}</td>
                            <td class="px-5 py-4">
                                @if ($variant->is_published)
                                    <span class="rounded-full bg-sage-soft px-3 py-1 text-xs text-sage">Published</span>
                                @else
                                    <span class="rounded-full bg-petal px-3 py-1 text-xs text-ink/70">Draft</span>
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
                                </div>
                            </td>
                        @else
                            <td class="px-5 py-4 text-ink/50" colspan="3">No drawing yet: the design page uses the layered preview.</td>
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
</x-studio-layout>
