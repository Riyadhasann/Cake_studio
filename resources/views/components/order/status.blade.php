@props(['order'])
@php
    // Placed → Preparing → Delivered; a cancelled order stops after "Placed".
    $cancelled = $order->status === 'cancelled';
    $delivered = $order->status === 'delivered';
    $steps = [
        ['label' => 'Order placed', 'detail' => $order->created_at?->format('M j, g:ia'), 'done' => true],
        $cancelled
            ? ['label' => 'Cancelled', 'detail' => $order->cancelled_at?->format('M j, g:ia'), 'done' => true, 'bad' => true]
            : ['label' => 'Being prepared', 'detail' => $delivered ? 'Baked and packed' : 'In the kitchen', 'done' => true, 'current' => ! $delivered],
        ['label' => 'Delivered', 'detail' => $delivered ? $order->delivered_at?->format('M j, g:ia') : 'Requested for '.($order->delivery_date?->format('D, M j') ?? '—'), 'done' => $delivered, 'current' => $delivered],
    ];
    if ($cancelled) {
        array_pop($steps);
    }
@endphp

<ol {{ $attributes->merge(['class' => 'grid gap-4 '.(count($steps) === 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2')]) }} aria-label="Order status">
    @foreach ($steps as $s)
        @php
            $tone = match (true) {
                $s['bad'] ?? false => 'border-clay bg-clay text-white',
                $s['done'] => 'border-sage bg-sage text-white',
                default => 'border-line bg-white text-ink/50',
            };
        @endphp
        <li class="flex items-start gap-3 sm:flex-col" @if ($s['current'] ?? false) aria-current="step" @endif>
            <span class="flex items-center gap-3 sm:w-full">
                <span class="grid size-8 shrink-0 place-items-center rounded-full border-2 {{ $tone }}">
                    @if ($s['bad'] ?? false)
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 5.3a1 1 0 0 0-1.4 1.4L8.58 10l-3.3 3.3a1 1 0 1 0 1.42 1.4L10 11.42l3.3 3.3a1 1 0 0 0 1.4-1.42L11.42 10l3.3-3.3a1 1 0 0 0-1.42-1.4L10 8.58l-3.3-3.3Z"/></svg>
                    @elseif ($s['done'])
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                    @else
                        <span class="size-2 rounded-full bg-line"></span>
                    @endif
                </span>
                @if (! $loop->last)
                    <span class="hidden h-0.5 flex-1 rounded-full sm:block {{ $steps[$loop->index + 1]['done'] ? 'bg-sage' : 'bg-line' }}" aria-hidden="true"></span>
                @endif
            </span>
            <span>
                <span class="block text-sm font-bold {{ ($s['bad'] ?? false) ? 'text-clay' : 'text-ink' }}">{{ $s['label'] }}</span>
                <span class="block text-xs text-ink/70">{{ $s['detail'] }}</span>
            </span>
        </li>
    @endforeach
</ol>
