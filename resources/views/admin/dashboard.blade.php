<x-studio-layout title="Admin" :admin="true">
    <x-admin.header title="Welcome back" description="Today's orders at a glance, and what your shop still needs. Everything customers see is set up from the menu on the left." />

    <x-admin.alerts />

    {{-- Today's numbers --}}
    <section class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="At a glance">
        @foreach ($stats as $stat)
            <a href="{{ route($stat['route']) }}" class="admin-card group block transition hover:border-brand/50 hover:shadow-lift">
                <p class="text-3xl font-bold text-ink">{{ $stat['value'] }}</p>
                <p class="mt-1 text-sm font-semibold text-text-secondary group-hover:text-brand">{{ $stat['label'] }}</p>
            </a>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-5">
        {{-- Set-up checklist --}}
        @php
            $required = collect($checklist)->where('required', true);
            $doneCount = collect($checklist)->where('done', true)->count();
        @endphp
        <section class="admin-card lg:col-span-3" aria-labelledby="checklist-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="checklist-heading" class="text-lg font-bold">Shop set-up</h2>
                <p class="text-sm font-semibold text-text-secondary">{{ $doneCount }} of {{ count($checklist) }} done</p>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-petal" aria-hidden="true">
                <div class="h-full rounded-full bg-brand transition-all" style="width: {{ round($doneCount / max(1, count($checklist)) * 100) }}%"></div>
            </div>
            @if ($required->every('done'))
                <p class="admin-hint mt-3">The shop is ready for customers. The rest is optional polish.</p>
            @endif

            <ul class="mt-4 divide-y divide-line">
                @foreach ($checklist as $item)
                    <li class="flex items-start gap-3 py-3">
                        @if ($item['done'])
                            <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-brand text-white" aria-hidden="true">
                                <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </span>
                        @else
                            <span class="mt-0.5 size-6 shrink-0 rounded-full border-2 {{ $item['required'] ? 'border-clay' : 'border-line' }}" aria-hidden="true"></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold {{ $item['done'] ? 'text-text-secondary' : 'text-ink' }}">
                                <span class="sr-only">{{ $item['done'] ? 'Done:' : 'To do:' }}</span>
                                {{ $item['label'] }}
                                @unless ($item['required'])
                                    <span class="ml-1 text-xs font-medium text-text-muted">optional</span>
                                @endunless
                            </p>
                            <p class="mt-0.5 text-xs text-text-secondary">{{ $item['hint'] }}</p>
                        </div>
                        @unless ($item['done'])
                            <a href="{{ route($item['route']) }}" class="btn-outline btn-sm shrink-0">Set up</a>
                        @endunless
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Next orders to make --}}
        <section class="admin-card lg:col-span-2" aria-labelledby="upcoming-heading">
            <div class="flex items-baseline justify-between gap-2">
                <h2 id="upcoming-heading" class="text-lg font-bold">Next to deliver</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand hover:text-brand-dark">All orders</a>
            </div>
            @if ($upcoming->isEmpty())
                <p class="admin-hint mt-4">No orders waiting. New orders appear here.</p>
            @else
                <ul class="mt-3 divide-y divide-line">
                    @foreach ($upcoming as $order)
                        <li class="flex items-center justify-between gap-3 py-3 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-semibold">#{{ $order->id }} · {{ $order->customer_name }}</p>
                                <p class="text-xs text-text-secondary">
                                    {{ $order->delivery_date ? 'Deliver '.\Illuminate\Support\Carbon::parse($order->delivery_date)->format('D j M') : 'No delivery date' }}
                                </p>
                            </div>
                            <span class="shrink-0 font-semibold">৳{{ number_format((float) $order->total, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- Where things are --}}
    <section class="mt-6" aria-labelledby="sections-heading">
        <h2 id="sections-heading" class="text-lg font-bold">What you can change</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['admin.orders.index', 'Orders', 'See new orders, mark them delivered or cancel them.'],
                ['admin.sizes.index', 'Sizes & prices', 'Cake sizes and what each one costs.'],
                ['admin.cake-visuals.index', 'Flavors & cake photo', 'The cut cake photo, every sponge and cream, their pictures and prices.'],
                ['admin.designs.index', 'Cake designs', 'Finished outside looks customers can pick, with their 3D view.'],
                ['admin.settings.index', 'Shop & pictures', 'Shop name, contact details, logo, banner and the cake background.'],
                ['admin.cake-variants.index', 'Cake shapes', 'Advanced: where the layers sit in the cake photo, and how many layers customers can pick.'],
            ] as [$route, $title, $text])
                <a href="{{ route($route) }}" class="admin-card group block transition hover:border-brand/50 hover:shadow-lift">
                    <p class="font-bold text-ink group-hover:text-brand">{{ $title }}</p>
                    <p class="mt-1 text-sm text-text-secondary">{{ $text }}</p>
                </a>
            @endforeach
        </div>
    </section>
</x-studio-layout>
