<x-studio-layout title="Track order" :alerts="false" description="Check the status of your cake order with your order number and phone number.">
    <div class="mx-auto max-w-2xl">
        <x-page-heading eyebrow="Your order" title="Track your order">
            Enter the order number from your confirmation and the phone number you ordered with.
        </x-page-heading>

        @if ($errors->any())
            <div class="alert alert-error mt-4" role="alert">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('orders.track.lookup') }}" method="POST" class="panel mt-5 p-4 sm:p-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="order_id" class="label">Order number</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 grid place-items-center font-bold text-ink/50" aria-hidden="true">#</span>
                        <input id="order_id" type="number" name="order_id" min="1" inputmode="numeric" value="{{ old('order_id', request('order_id')) }}" required
                               class="field pl-8" placeholder="1024">
                    </div>
                </div>

                <div>
                    <label for="phone" class="label">Phone number</label>
                    <input id="phone" type="tel" name="phone" inputmode="tel" autocomplete="tel" value="{{ old('phone', request('phone')) }}" required
                           class="field" placeholder="01XXXXXXXXX">
                </div>
            </div>

            <div class="mt-4 flex justify-end"><button type="submit" class="btn w-full sm:w-auto">Check status</button></div>
        </form>

        @if ($searched)
            @if (! $order)
                <div class="alert alert-error mt-4" role="alert">
                    We couldn't find an order with that number and phone. Check both and try again.
                </div>
            @else
                @php
                    $badge = match ($order->status) {
                        'delivered' => 'bg-sage-soft text-sage',
                        'cancelled' => 'bg-clay-soft text-clay',
                        default => 'bg-brand-soft text-brand-dark',
                    };
                    $label = match ($order->status) {
                        'pending' => 'Being prepared',
                        default => ucfirst($order->status),
                    };
                @endphp

                <div class="panel mt-4 p-4 sm:p-5" role="status">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-lg font-bold">Order #{{ $order->id }}</h2>
                        <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $badge }}">{{ $label }}</span>
                    </div>

                    <x-order.status :order="$order" class="mt-4 border-y border-line py-4" />

                    @if ($order->status === 'cancelled')
                        <div class="alert mt-4 border-clay/30 bg-clay-soft text-ink" role="alert">
                            <p class="font-bold text-clay">This order was cancelled{{ $order->cancelled_at ? ' on ' . $order->cancelled_at->format('M j, g:ia') : '' }}.</p>
                            <p class="mt-1"><span class="font-semibold">Reason:</span> {{ $order->cancel_reason }}</p>
                        </div>
                    @elseif ($order->status === 'delivered')
                        <div class="alert alert-success mt-4">
                            Delivered on {{ $order->delivered_at?->format('M j, g:ia') }}. Enjoy your cake!
                        </div>
                    @endif

                    <ul class="mt-2 divide-y divide-line text-sm">
                        @foreach ($order->cakeCustomizations as $c)
                            @php $layers = $c->layer_summary; @endphp
                            <li class="flex gap-3 py-3">
                                <x-cake.thumb :layers="$layers" class="size-12" />
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold">{{ $c->quantity }} &times; {{ $c->cakeSize->label }} cake, {{ count($layers) }} {{ str('layer')->plural(count($layers)) }}</p>
                                    <p class="mt-0.5 text-ink/90"><span class="font-semibold text-ink">Design:</span> {{ $c->design_name ?? 'Plain' }}</p>
                                    <x-cake.layer-list :layers="$layers" class="mt-1" />
                                    @if ($c->message)
                                        <p class="mt-1 text-ink/80">&ldquo;{{ $c->message }}&rdquo;</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-2 flex items-baseline justify-between border-t border-line pt-4">
                        <span class="font-bold">Total</span>
                        <span class="text-xl font-extrabold">৳{{ number_format($order->total, 2) }}</span>
                    </p>
                </div>
            @endif
        @endif
    </div>
</x-studio-layout>
