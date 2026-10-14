@foreach ($orders as $order)
    <div class="mb-8 rounded-2xl border border-clay/30 bg-clay-soft p-5" role="alert">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-display text-2xl font-medium text-clay">Order #{{ $order->id }} was cancelled</p>
                <p class="mt-1 text-sm text-ink"><span class="font-medium">Reason:</span> {{ $order->cancel_reason }}</p>
                <p class="mt-2 text-xs text-ink/70">Questions? Contact us and mention your order number.</p>
            </div>

            <form action="{{ route('orders.dismiss', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-medium text-ink/70 underline underline-offset-4 hover:text-clay">Dismiss</button>
            </form>
        </div>
    </div>
@endforeach
