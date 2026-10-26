<x-studio-layout title="Your Cart" :step="2" description="Review your cakes and add your delivery details.">
    @php
        $cakeCount = collect($items)->sum(fn ($item) => $item['quantity'] ?? 1);
        // Invalid fields get a red border and point at their message
        $invalid = fn (string $field) => $errors->has($field) ? 'aria-invalid=true aria-describedby='.$field.'-error' : '';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <x-back-link :href="route('cake-studio.index')" label="Keep designing" />
            <h1 class="heading mt-2 text-3xl">Your cart</h1>
            <p class="mt-1 text-sm text-ink/75">
                @if (empty($items))
                    Nothing here yet.
                @else
                    {{ $cakeCount }} {{ str('cake')->plural($cakeCount) }}. Check them, then add your delivery details.
                @endif
            </p>
        </div>
    </div>

    <div class="mt-4 space-y-2">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <p class="font-bold">Please check the highlighted fields.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if (empty($items))
        <div class="panel mx-auto mt-6 max-w-md p-8 text-center">
            <span class="mx-auto grid size-12 place-items-center rounded-xl bg-brand-soft text-brand" aria-hidden="true">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
            </span>
            <p class="mt-4 text-lg font-bold">Your cart is empty</p>
            <p class="mt-1 text-sm text-ink/75">Design a cake and it will appear here, ready to order.</p>
            <a href="{{ route('cake-studio.index') }}" class="btn mt-5">Design a cake</a>
        </div>
    @else
        <div class="mt-5 grid grid-cols-1 items-start gap-4 lg:grid-cols-12">
            <div class="min-w-0 space-y-4 lg:col-span-7">
                {{-- The cakes --}}
                <section aria-labelledby="items-heading" class="panel">
                    <h2 id="items-heading" class="sr-only">Cakes in your cart</h2>
                    <ul class="divide-y divide-line">
                        @foreach ($items as $item)
                            @php
                                $qty = $item['quantity'] ?? 1;
                                $layerCount = count($item['layers']);
                            @endphp
                            <li class="flex gap-3 p-4">
                                <x-cake-thumb :layers="$item['layers']" :image="$item['design_image'] ?? null" class="size-16" />

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-base font-bold leading-snug">{{ $item['size_label'] }} cake</p>
                                            <p class="mt-1 flex flex-wrap gap-1.5">
                                                <span class="chip">{{ $layerCount }} {{ str('layer')->plural($layerCount) }}</span>
                                                <span class="chip">{{ $item['design_name'] ?? 'Plain' }}</span>
                                                @if ($qty > 1)
                                                    <span class="chip border-brand/20 bg-brand-soft text-brand-dark">&times; {{ $qty }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <p class="shrink-0 text-right text-base font-extrabold">৳{{ number_format($item['price'] * $qty, 2) }}</p>
                                    </div>

                                    <x-layer-list :layers="$item['layers']" class="mt-2 space-y-0.5" />
                                    @if (! empty($item['message']))
                                        <p class="mt-2 text-sm text-ink/80"><span class="font-semibold text-ink">Message:</span> &ldquo;{{ $item['message'] }}&rdquo;</p>
                                    @endif

                                    <div class="mt-2 flex items-center justify-between gap-4 text-sm">
                                        <span class="text-ink/70">{{ $qty }} &times; ৳{{ number_format($item['price'], 2) }}</span>
                                        <form action="{{ route('cart.remove', $item['id']) }}" method="POST"
                                              onsubmit="return confirm('Remove this cake from your cart?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-outline btn-sm hover:border-clay/40 hover:text-clay">
                                                <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.44c-.8.08-1.58.18-2.36.3a.75.75 0 1 0 .22 1.49l.15-.03.8 10.42A2.75 2.75 0 0 0 7.55 19h4.9a2.75 2.75 0 0 0 2.74-2.63L16 5.95l.14.02a.75.75 0 1 0 .22-1.48c-.78-.13-1.56-.23-2.36-.31v-.43A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.67.03 2.5.08v-.33c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.33C8.33 4.03 9.16 4 10 4Z" clip-rule="evenodd"/></svg>
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- Delivery details --}}
                <form id="checkout" action="{{ route('cart.place-order') }}" method="POST" class="panel space-y-4 p-4 sm:p-5">
                    @csrf

                    <div>
                        <h2 class="text-base font-bold">Delivery details</h2>
                        <p class="mt-0.5 text-sm text-ink/70">We use your phone number to confirm the order and to let you track it.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="customer_name" class="label">Full name</label>
                            <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="255"
                                   autocomplete="name" class="field" {{ $invalid('customer_name') }}>
                            @error('customer_name') <p id="customer_name-error" class="mt-1.5 text-xs font-semibold text-clay">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="label">Phone number</label>
                            <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="20"
                                   autocomplete="tel" inputmode="tel" placeholder="01XXXXXXXXX" class="field" {{ $invalid('customer_phone') }}>
                            @error('customer_phone') <p id="customer_phone-error" class="mt-1.5 text-xs font-semibold text-clay">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="customer_address" class="label">Delivery address</label>
                        <textarea id="customer_address" name="customer_address" rows="3" required maxlength="500" autocomplete="street-address"
                                  placeholder="House, road, area, city" class="field" {{ $invalid('customer_address') }}>{{ old('customer_address') }}</textarea>
                        @error('customer_address') <p id="customer_address-error" class="mt-1.5 text-xs font-semibold text-clay">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="delivery_date" class="label">Delivery date</label>
                            <input id="delivery_date" type="date" name="delivery_date" min="{{ now()->toDateString() }}"
                                   value="{{ old('delivery_date') }}" required class="field" {{ $invalid('delivery_date') }}>
                            @error('delivery_date') <p id="delivery_date-error" class="mt-1.5 text-xs font-semibold text-clay">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="label">Notes for the baker <span class="font-normal text-ink/60">(optional)</span></label>
                        <textarea id="notes" name="notes" rows="2" maxlength="500" placeholder="Allergies, a delivery time, directions…" class="field" {{ $invalid('notes') }}>{{ old('notes') }}</textarea>
                        @error('notes') <p id="notes-error" class="mt-1.5 text-xs font-semibold text-clay">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phones: the summary is below, so the button is here too --}}
                    <button type="submit" class="btn w-full lg:hidden">Place order · ৳{{ number_format($total, 2) }}</button>
                </form>
            </div>

            {{-- Order summary --}}
            <aside class="min-w-0 lg:sticky lg:top-20 lg:col-span-5" aria-labelledby="summary-heading">
                <div class="panel p-4 sm:p-5">
                    <h2 id="summary-heading" class="text-base font-bold">Order summary</h2>

                    <dl class="mt-3 space-y-2 text-sm">
                        @foreach ($items as $item)
                            @php $qty = $item['quantity'] ?? 1; @endphp
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink/80">{{ $qty }} &times; {{ $item['size_label'] }} cake{{ ! empty($item['design_name']) ? ', '.$item['design_name'] : '' }}</dt>
                                <dd class="shrink-0 font-semibold">৳{{ number_format($item['price'] * $qty, 2) }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-3 flex items-baseline justify-between border-t border-line pt-3">
                        <span class="font-bold">Total</span>
                        <span class="text-2xl font-extrabold">৳{{ number_format($total, 2) }}</span>
                    </div>

                    <button type="submit" form="checkout" class="btn mt-3 hidden w-full lg:inline-flex">
                        Place order
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </button>

                    <ul class="mt-3 space-y-1.5 border-t border-line pt-3 text-xs text-ink/70">
                        <li class="flex gap-3">
                            <svg class="mt-0.5 size-4 shrink-0 text-sage" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            Prices are checked again when you order.
                        </li>
                        <li class="flex gap-3">
                            <svg class="mt-0.5 size-4 shrink-0 text-sage" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            Every cake is baked to order for your date.
                        </li>
                        <li class="flex gap-3">
                            <svg class="mt-0.5 size-4 shrink-0 text-sage" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            Track your order online with its number.
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    @endif
</x-studio-layout>
