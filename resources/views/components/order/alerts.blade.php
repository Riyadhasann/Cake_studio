@foreach ($orders as $order)
    <div class="alert mb-4 border-clay/30 bg-clay-soft" role="alert">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-base font-bold text-clay">Order #{{ $order->id }} was cancelled</p>
                <p class="mt-1 text-sm text-ink"><span class="font-medium">Reason:</span> {{ $order->cancel_reason }}</p>
                <p class="mt-2 text-xs text-ink/80">Questions? Contact us and mention your order number.</p>
            </div>

            <form action="{{ route('orders.dismiss', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-outline btn-sm">Dismiss</button>
            </form>
        </div>
    </div>
@endforeach
