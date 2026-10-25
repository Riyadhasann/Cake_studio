<x-studio-layout title="Your Cart" :step="2">
    <div class="mx-auto max-w-2xl">
        <x-back-link :href="route('cake-studio.index')" label="Back to Cake Studio" />

        <x-page-heading class="mt-8" title="Your Cart">
            Check your cakes, then add your delivery details.
        </x-page-heading>

        <div class="mt-10 space-y-4">
            @if (session('success'))
                <div class="rounded-2xl border border-sage/30 bg-sage-soft px-5 py-4 text-sm text-ink">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if (empty($items))
            <div class="panel mt-10 p-10 text-center">
                <p class="heading text-3xl">Your cart is empty</p>
                <p class="mt-2 text-sm text-ink/80">Design a cake and it will appear here.</p>
                <a href="{{ route('cake-studio.index') }}" class="btn mt-6">Design a cake</a>
            </div>
        @else
            <div class="panel mt-10 divide-y divide-line">
                @foreach ($items as $item)
                    @php $qty = $item['quantity'] ?? 1; @endphp
                    <div class="flex items-start justify-between gap-6 p-6">
                        <div class="min-w-0">
                            @php $layerCount = count($item['layers']); @endphp
                            <p class="font-display text-2xl font-medium leading-snug">{{ $item['size_label'] }} cake, {{ $layerCount }} {{ $layerCount === 1 ? 'layer' : 'layers' }}</p>
                            <p class="mt-1 text-sm text-ink/90"><span class="font-medium text-ink">Design:</span> {{ $item['design_name'] ?? 'Plain' }}</p>
                            <x-layer-list :layers="$item['layers']" class="mt-1" />
                            @if (! empty($item['message']))
                                <p class="mt-1 text-sm text-ink/80">&ldquo;{{ $item['message'] }}&rdquo;</p>
                            @endif
                            <p class="mt-2 text-sm text-ink/80">{{ $qty }} &times; ৳{{ number_format($item['price'], 2) }}</p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="font-display text-2xl font-medium">৳{{ number_format($item['price'] * $qty, 2) }}</p>
                            <form action="{{ route('cart.remove', $item['id']) }}" method="POST"
                                  onsubmit="return confirm('Remove this cake from your cart?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mt-2 text-xs text-ink/80 underline underline-offset-4 hover:text-clay">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="flex items-baseline justify-between p-6">
                    <span class="text-sm text-ink/80">Total</span>
                    <span class="font-display text-4xl font-medium">৳{{ number_format($total, 2) }}</span>
                </div>
            </div>

            <form action="{{ route('cart.place-order') }}" method="POST" class="panel mt-8 space-y-6 p-6 sm:p-8">
                @csrf

                <h2 class="heading text-3xl">Delivery details</h2>

                <div>
                    <label for="customer_name" class="label">Full name</label>
                    <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required class="field">
                </div>

                <div>
                    <label for="customer_phone" class="label">Phone number</label>
                    <input id="customer_phone" type="text" name="customer_phone" value="{{ old('customer_phone') }}" required class="field">
                </div>

                <div>
                    <label for="customer_address" class="label">Delivery address</label>
                    <textarea id="customer_address" name="customer_address" rows="2" required class="field">{{ old('customer_address') }}</textarea>
                </div>

                <div>
                    <label for="delivery_date" class="label">Delivery date</label>
                    <input id="delivery_date" type="date" name="delivery_date" min="{{ now()->toDateString() }}"
                           value="{{ old('delivery_date') }}" required class="field">
                </div>

                <div>
                    <label for="notes" class="label">Special notes <span class="font-normal text-ink/80">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="2" class="field">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn w-full">Place order for ৳{{ number_format($total, 2) }}</button>
            </form>
        @endif
    </div>
</x-studio-layout>
