<x-studio-layout title="Track order" :alerts="false">
    <div class="mx-auto max-w-xl">
        <x-back-link :href="route('cake-studio.index')" label="Back to Cake Studio" />

        <x-page-heading class="mt-8" eyebrow="Your order" title="Track your order">
            Enter the order number from your confirmation and the phone number you ordered with.
        </x-page-heading>

        @if ($errors->any())
            <div class="mt-8 rounded-2xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('orders.track.lookup') }}" method="POST" class="panel mt-10 space-y-6 p-6 sm:p-8">
            @csrf

            <div>
                <label for="order_id" class="label">Order number</label>
                <input id="order_id" type="number" name="order_id" min="1" value="{{ old('order_id', request('order_id')) }}" required class="field">
            </div>

            <div>
                <label for="phone" class="label">Phone number</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', request('phone')) }}" required class="field">
            </div>

            <button type="submit" class="btn w-full">Check status</button>
        </form>

        @if ($searched)
            @if (! $order)
                <div class="mt-8 rounded-2xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">
                    We couldn't find an order with that number and phone. Check both and try again.
                </div>
            @else
                @php
                    $badge = match ($order->status) {
                        'delivered' => 'bg-sage-soft text-sage',
                        'cancelled' => 'bg-clay-soft text-clay',
                        default => 'bg-brand-soft text-brand',
                    };
                @endphp

                <div class="panel mt-8 p-6 sm:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="heading text-3xl">Order #{{ $order->id }}</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $badge }}">{{ ucfirst($order->status) }}</span>
                    </div>

                    @if ($order->status === 'cancelled')
                        <div class="mt-5 rounded-2xl border border-clay/30 bg-clay-soft p-4 text-sm text-ink" role="alert">
                            <p class="font-medium text-clay">This order was cancelled{{ $order->cancelled_at ? ' on ' . $order->cancelled_at->format('M j, g:ia') : '' }}.</p>
                            <p class="mt-1"><span class="font-medium">Reason:</span> {{ $order->cancel_reason }}</p>
                        </div>
                    @elseif ($order->status === 'delivered')
                        <div class="mt-5 rounded-2xl border border-sage/30 bg-sage-soft p-4 text-sm text-ink">
                            Delivered on {{ $order->delivered_at?->format('M j, g:ia') }}. Enjoy your cake!
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl bg-petal p-4 text-sm text-ink">
                            We're preparing your order. Requested delivery: {{ $order->delivery_date?->format('D, M j') ?? '—' }}.
                        </div>
                    @endif

                    <ul class="mt-6 divide-y divide-line text-sm">
                        @foreach ($order->cakeCustomizations as $c)
                            <li class="py-3">
                                <span class="font-medium">{{ $c->quantity }} &times;</span>
                                {{ $c->cakeSize->label }} cake, {{ count($c->layer_summary) }} {{ count($c->layer_summary) === 1 ? 'layer' : 'layers' }}
                                <x-layer-list :layers="$c->layer_summary" class="mt-1" />
                                @if ($c->message)
                                    <br><span class="text-ink/70">&ldquo;{{ $c->message }}&rdquo;</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-3 flex items-baseline justify-between border-t border-line pt-4">
                        <span class="text-sm text-ink/70">Total</span>
                        <span class="font-display text-3xl font-medium">৳{{ number_format($order->total, 2) }}</span>
                    </p>
                </div>
            @endif
        @endif
    </div>
</x-studio-layout>
