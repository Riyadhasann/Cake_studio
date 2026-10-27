<x-studio-layout title="Orders" :admin="true">
    <div x-data="ordersAdmin()" @keydown.escape.window="closeCancel()">

        <x-admin.header title="Orders" description="Every order, newest first. Mark an order delivered when it leaves the shop, or cancel it with a reason the customer sees when they track it." />

        <x-admin.alerts />

        @if ($orders->isEmpty())
            <div class="admin-card mt-6 text-center">
                <p class="font-semibold">No orders yet</p>
                <p class="admin-hint mt-1">New orders appear here as soon as a customer checks out.</p>
            </div>
        @else
            <div class="panel mt-6 overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead class="bg-petal text-xs font-medium tracking-wide text-ink/80">
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
                                    <span class="font-display text-lg font-medium">#{{ $order->id }}</span><br>
                                    <span class="text-ink/80">{{ $order->created_at->format('M j, g:ia') }}</span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-medium">{{ $order->customer_name }}</span><br>
                                    <span class="text-ink/80">{{ $order->customer_phone }}</span><br>
                                    <span class="text-ink/80">{{ $order->customer_address }}</span>
                                    @if ($order->notes)
                                        <p class="mt-1 text-ink/80">Note: {{ $order->notes }}</p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <ul class="space-y-2">
                                        @foreach ($order->cakeCustomizations as $c)
                                            <li>
                                                <span class="font-medium">{{ $c->quantity }} &times;</span>
                                                {{ $c->cakeSize->label }} cake, {{ count($c->layer_summary) }} {{ count($c->layer_summary) === 1 ? 'layer' : 'layers' }}
                                                <span class="mt-1 block text-ink/90"><span class="font-medium text-ink">Design:</span> {{ $c->design_name ?? 'Plain' }}</span>
                                                <x-layer-list :layers="$c->layer_summary" class="mt-1" />
                                                @if ($c->message)
                                                    <br><span class="text-ink/80">&ldquo;{{ $c->message }}&rdquo;</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($order->delivered_at)
                                        <span class="font-medium text-sage">Delivered</span><br>
                                        {{ $order->delivered_at->format('D, M j, g:ia') }}<br>
                                        <span class="text-xs text-ink/80">Requested: {{ $order->delivery_date?->format('M j') ?? '—' }}</span>
                                    @else
                                        {{ $order->delivery_date?->format('D, M j') ?? '—' }}
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-display text-lg font-medium">৳{{ number_format($order->total, 2) }}</td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $badge }}">{{ ucfirst($order->status) }}</span>
                                    @if ($order->status === 'cancelled')
                                        <p class="mt-2 max-w-[14rem] text-xs text-ink/80">Reason: {{ $order->cancel_reason }}</p>
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
                                            <button type="submit" class="text-xs text-ink/80 underline underline-offset-4 hover:text-clay">Delete</button>
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
            <form :action="cancelUrl" method="POST" class="w-full max-w-md rounded-[1.75rem] bg-paper p-5">
                @csrf
                <h2 class="text-xl font-bold">Cancel order <span x-text="'#' + cancelId"></span></h2>
                <p class="mt-2 text-sm text-ink/80">The customer will see this reason when they check their order.</p>

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
                <p class="mt-1 text-right text-xs text-ink/80" x-text="reason.length + '/500'"></p>

                <div class="mt-6 space-y-3">
                    <button type="submit" class="btn w-full border-clay bg-clay hover:border-clay hover:bg-clay/90">
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
