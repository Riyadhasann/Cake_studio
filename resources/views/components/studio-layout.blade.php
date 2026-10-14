@props(['title' => 'Cake Studio', 'step' => null, 'admin' => false, 'alerts' => true])
@inject('cart', 'App\Services\CartService')
@php $cartCount = $admin ? 0 : $cart->count(); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Cake Studio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500&family=Pinyon+Script&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        /*
         * THEME TOKENS: change the look of the whole app here.
         * Colors were sampled from the reference image; ink is an estimate.
         */
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                        script: ['"Pinyon Script"', 'cursive'],
                        sans: ['Jost', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        paper: '#faf6f3',
                        petal: '#f8eeec',
                        line: '#e4d3cb',
                        ink: '#3b2a27',
                        brand: { DEFAULT: '#905954', dark: '#75483f', soft: '#f3e3df' },
                        sage: { DEFAULT: '#4f6b4a', soft: '#e6eee2' },
                        clay: { DEFAULT: '#a8453f', soft: '#f7e4e1' },
                    },
                },
            },
        };
    </script>

    {{-- Reusable classes, so views say "btn" instead of repeating 20 utilities --}}
    <style type="text/tailwindcss">
        @layer components {
            .eyebrow {
                @apply text-[0.7rem] font-medium uppercase tracking-[0.28em] text-brand;
            }
            .heading {
                @apply font-display font-normal leading-[1.08] text-ink;
            }
            .btn {
                @apply inline-flex items-center justify-center gap-2 rounded-full border border-brand bg-brand px-8 py-3.5 text-[0.7rem] font-medium uppercase tracking-[0.2em] text-white transition-colors hover:border-brand-dark hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-60;
            }
            .btn-outline {
                @apply inline-flex items-center justify-center gap-2 rounded-full border border-brand bg-transparent px-8 py-3.5 text-[0.7rem] font-medium uppercase tracking-[0.2em] text-brand transition-colors hover:bg-brand hover:text-white disabled:cursor-not-allowed disabled:opacity-60;
            }
            .btn-sm {
                @apply px-5 py-2.5;
            }
            .panel {
                @apply rounded-[1.75rem] border border-line bg-white/60;
            }
            .field {
                @apply w-full rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink placeholder:text-ink/40 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand;
            }
            .label {
                @apply mb-2 block text-sm font-medium text-ink;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">

    @unless ($admin)
        <div class="bg-brand px-4 py-2 text-center text-[0.65rem] font-medium uppercase tracking-[0.3em] text-white/90">
            Custom cakes, made to order
        </div>
    @endunless

    <header class="sticky top-0 z-40 border-b border-line bg-paper/90 backdrop-blur">
        <div class="mx-auto flex h-20 items-center justify-between px-4 {{ $admin ? 'max-w-7xl' : 'max-w-5xl' }}">

            @if ($admin)
                <a href="{{ route('admin.orders.index') }}" class="font-script text-4xl leading-none text-brand">
                    Cake Studio
                    <span class="ml-2 rounded-full bg-ink px-2.5 py-1 align-middle font-sans text-[0.6rem] font-medium uppercase tracking-[0.2em] text-white">Admin</span>
                </a>

                <nav class="flex items-center gap-8 text-[0.7rem] font-medium uppercase tracking-[0.22em]">
                    <a href="{{ route('admin.orders.index') }}"
                       class="{{ request()->routeIs('admin.orders.*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Orders</a>
                    <a href="{{ route('admin.cake-visuals.index') }}"
                       class="{{ request()->routeIs('admin.cake-visuals.*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Cake images</a>
                    <a href="{{ route('admin.sizes.index') }}"
                       class="{{ request()->routeIs('admin.sizes.*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Sizes</a>
                    <a href="{{ route('admin.cake-variants.index') }}"
                       class="{{ request()->routeIs('admin.cake-variants.*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Cake shapes</a>
                </nav>
            @else
                <a href="{{ route('cake-studio.index') }}" class="font-script text-4xl leading-none text-brand">Cake Studio</a>

                <nav class="flex items-center gap-6 text-[0.7rem] font-medium uppercase tracking-[0.22em] sm:gap-8">
                    <a href="{{ route('cake-studio.index') }}"
                       class="hidden sm:inline {{ request()->routeIs('cake-studio.*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Design</a>
                    <a href="{{ route('orders.track') }}"
                       class="{{ request()->routeIs('orders.track*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Track order</a>

                    <a href="{{ route('cart.index') }}" class="btn btn-sm">
                        Cart
                        @if ($cartCount > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 text-[0.65rem] font-semibold tracking-normal text-brand">{{ $cartCount }}</span>
                        @endif
                    </a>
                </nav>
            @endif
        </div>
    </header>

    <main class="mx-auto w-full flex-1 px-4 py-10 {{ $admin ? 'max-w-7xl' : 'max-w-5xl' }}">
        @if ($alerts && ! $admin)
            <x-order-alerts />
        @endif

        @if ($step)
            <ol class="mb-10 flex items-center justify-center gap-4 text-[0.7rem] font-medium uppercase tracking-[0.22em]">
                @foreach (['Design', 'Cart & details'] as $i => $label)
                    @php $active = $step === $i + 1; @endphp
                    <li class="flex items-center gap-2.5 {{ $active ? 'text-brand' : 'text-ink/70' }}">
                        <span class="grid h-6 w-6 place-items-center rounded-full border text-[0.65rem] tracking-normal {{ $active ? 'border-brand bg-brand text-white' : 'border-line' }}">{{ $i + 1 }}</span>
                        {{ $label }}
                    </li>
                    @if (! $loop->last)
                        <li class="h-px w-10 bg-line" aria-hidden="true"></li>
                    @endif
                @endforeach
            </ol>
        @endif

        {{ $slot }}
    </main>

    @unless ($admin)
        <footer class="mt-16 bg-brand text-white">
            <div class="mx-auto max-w-5xl px-4 py-16 text-center">
                <p class="font-script text-3xl leading-none text-white/90">designed around you</p>
                <p class="mt-3 font-display text-4xl leading-tight sm:text-5xl">Every cake is made to order.</p>

                <nav class="mt-9 flex flex-wrap justify-center gap-x-8 gap-y-3 text-[0.7rem] font-medium uppercase tracking-[0.22em]">
                    <a href="{{ route('cake-studio.index') }}" class="hover:text-white/70">Design a cake</a>
                    <a href="{{ route('orders.track') }}" class="hover:text-white/70">Track order</a>
                    <a href="{{ route('cart.index') }}" class="hover:text-white/70">Cart</a>
                </nav>
            </div>
        </footer>
    @endunless
</body>
</html>
