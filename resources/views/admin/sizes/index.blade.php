<x-studio-layout title="Sizes" :admin="true">
    <x-admin.header title="Sizes & prices" description="The size sets the base price of a cake; each layer's sponge and cream add their own extra price on top. Customers see sizes lightest first. New prices only apply to new orders." />

    <x-admin.alerts />

    <div class="panel mt-6 overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <thead class="bg-petal text-xs font-medium tracking-wide text-ink/80">
                <tr>
                    <th class="px-5 py-3.5">Label</th>
                    <th class="px-5 py-3.5">Weight</th>
                    <th class="px-5 py-3.5">Price</th>
                    <th class="px-5 py-3.5">Used in orders</th>
                    <th class="px-5 py-3.5">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($sizes as $size)
                    <tr>
                        <td class="px-5 py-4 font-display text-lg font-medium">{{ $size->label }}</td>
                        <td class="px-5 py-4">{{ $size->weight_kg !== null ? rtrim(rtrim($size->weight_kg, '0'), '.') . ' kg' : '—' }}</td>
                        <td class="px-5 py-4">৳{{ number_format((float) $size->price, 2) }}</td>
                        <td class="px-5 py-4 text-ink/80">{{ $size->cake_customizations_count }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('admin.sizes.edit', $size) }}" class="btn-outline btn-sm">Edit</a>
                                @if ($size->cake_customizations_count === 0)
                                    <form action="{{ route('admin.sizes.destroy', $size) }}" method="POST"
                                          onsubmit="return confirm('Delete this size?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-ink/80 underline underline-offset-4 hover:text-clay">Delete</button>
                                    </form>
                                @else
                                    <span class="text-xs text-ink/70" title="Past orders use this size">Can't delete</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-ink/80">No sizes yet. Add one below.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <section class="panel mt-6 max-w-xl p-4">
        <h2 class="text-lg font-bold">Add a size</h2>
        <form action="{{ route('admin.sizes.store') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            @include('admin.sizes._fields', ['size' => null])
            <button type="submit" class="btn">Add size</button>
        </form>
    </section>
</x-studio-layout>
