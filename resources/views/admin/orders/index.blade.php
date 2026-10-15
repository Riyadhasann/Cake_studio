<x-studio-layout title="Orders" :admin="true">
    <div x-data="ordersAdmin()" @keydown.escape.window="closeCancel()">

        <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

        <h1 class="heading mt-6 text-5xl">Orders</h1>

        <div class="mt-8 space-y-4">
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

        @if ($orders->isEmpty())
            <p class="mt-8 text-ink/70">No orders yet.</p>
        @else
            <div class="panel mt-8 overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead class="bg-petal text-xs font-medium tracking-wide text-ink/70">
                        <tr>
                            <th class="px-5 py-3.5">Order</th>
                            <th class="px-5 py-3.5">Customer</th>
                            <th class="px-5 py-3.5">Cakes</th>
                            <th class="px-5 py-3.5">Deliver on</th>
                            <th class="px-5 py-3.5">Total</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($orders as $order)
                            @php
                                $badge = match ($order->status) {
                                    'delivered' => 'bg-sage-soft text-sage',
                                    'cancelled' => 'bg-clay-soft text-clay',
                                    default => 'bg-brand-soft text-brand',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <span class="font-display text-2xl font-medium">#{{ $order->id }}</span><br>
                                    <span class="text-ink/70">{{ $order->created_at->format('M j, g:ia') }}</span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-medium">{{ $order->customer_name }}</span><br>
                                    <span class="text-ink/70">{{ $order->customer_phone }}</span><br>
                                    <span class="text-ink/70">{{ $order->customer_address }}</span>
                                    @if ($order->notes)
                                        <p class="mt-1 text-ink/70">Note: {{ $order->notes }}</p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <ul class="space-y-2">
                                        @foreach ($order->cakeCustomizations as $c)
                                            <li>
                                                <span class="font-medium">{{ $c->quantity }} &times;</span>
                                                {{ $c->cakeSize->label }} cake, {{ count($c->layer_summary) }} {{ count($c->layer_summary) === 1 ? 'layer' : 'layers' }}
                                                <x-layer-list :layers="$c->layer_summary" class="mt-1" />
                                                @if ($c->message)
                                                    <br><span class="text-ink/70">&ldquo;{{ $c->message }}&rdquo;</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($order->delivered_at)
                                        <span class="font-medium text-sage">Delivered</span><br>
                                        {{ $order->delivered_at->format('D, M j, g:ia') }}<br>
                                        <span class="text-xs text-ink/70">Requested: {{ $order->delivery_date?->format('M j') ?? '—' }}</span>
                                    @else
                                        {{ $order->delivery_date?->format('D, M j') ?? '—' }}
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-display text-2xl font-medium">৳{{ number_format($order->total, 2) }}</td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $badge }}">{{ ucfirst($order->status) }}</span>
                                    @if ($order->status === 'cancelled')
                                        <p class="mt-2 max-w-[14rem] text-xs text-ink/70">Reason: {{ $order->cancel_reason }}</p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-col items-start gap-2">
                                        @if ($order->status === 'pending')
                                            <form action="{{ route('admin.orders.deliver', $order) }}" method="POST"
                                                  onsubmit="return confirm('Mark order #{{ $order->id }} as delivered?')">
                                                @csrf
                                                <button type="submit" class="rounded-full bg-sage px-4 py-1.5 text-xs font-medium text-white transition-colors hover:bg-sage/90">Deliver</button>
                                            </form>

                                            <button type="button"
                                                    @click="openCancel({{ $order->id }}, @js(route('admin.orders.cancel', $order)))"
                                                    class="rounded-full border border-clay px-4 py-1.5 text-xs font-medium text-clay transition-colors hover:bg-clay hover:text-white">Cancel</button>
                                        @endif

                                        <form action="{{ route('admin.orders.destroy', $order) }}" method="POST"
                                              onsubmit="return confirm('Permanently delete order #{{ $order->id }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-ink/70 underline underline-offset-4 hover:text-clay">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Cancel modal --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            @click.self="closeCancel()"
            class="fixed inset-0 z-50 grid place-items-center bg-ink/60 p-4"
            role="dialog"
            aria-modal="true"
        >
            <form :action="cancelUrl" method="POST" class="w-full max-w-md rounded-[1.75rem] bg-paper p-8">
                @csrf
                <h2 class="heading text-4xl">Cancel order <span x-text="'#' + cancelId"></span></h2>
                <p class="mt-2 text-sm text-ink/70">The customer will see this reason when they check their order.</p>

                <label for="cancel_reason" class="label mt-6">Reason for cancellation</label>
                <textarea
                    id="cancel_reason"
                    name="cancel_reason"
                    x-model="reason"
                    x-ref="reason"
                    rows="3"
                    required
                    minlength="3"
                    maxlength="500"
                    class="field"
                ></textarea>
                <p class="mt-1 text-right text-xs text-ink/70" x-text="reason.length + '/500'"></p>

                <div class="mt-6 space-y-3">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full border border-clay bg-clay px-8 py-3.5 text-[0.7rem] font-medium uppercase tracking-[0.2em] text-white transition-colors hover:bg-clay/90">
                        Cancel order and notify customer
                    </button>
                    <button type="button" @click="closeCancel()" class="btn-outline w-full">
                        Keep order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function ordersAdmin() {
            return {
                open: false,
                cancelId: null,
                cancelUrl: '',
                reason: '',

                openCancel(id, url) {
                    this.cancelId = id;
                    this.cancelUrl = url;
                    this.reason = '';
                    this.open = true;
                    this.$nextTick(() => this.$refs.reason.focus());
                },
                closeCancel() {
                    this.open = false;
                },
            };
        }
    </script>
</x-studio-layout>
