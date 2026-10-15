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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

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
                        // Upright faces only: headings in Fraunces, everything else in DM Sans.
                        display: ['Fraunces', 'Georgia', 'serif'],
                        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
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
                @apply text-xs font-semibold uppercase tracking-[0.18em] text-brand;
            }
            .heading {
                @apply font-display font-semibold leading-[1.1] tracking-tight text-ink;
            }
            /* Buttons have softly rounded corners, never pill-shaped. */
            .btn {
                @apply inline-flex items-center justify-center gap-2 rounded-lg border border-brand bg-brand px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:border-brand-dark hover:bg-brand-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60;
            }
            .btn-outline {
                @apply inline-flex items-center justify-center gap-2 rounded-lg border border-brand bg-white px-6 py-3 text-sm font-semibold text-brand transition hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60;
            }
            .btn-sm {
                @apply px-4 py-2;
            }
            .panel {
                @apply rounded-2xl border border-line bg-white shadow-sm;
            }
            .field {
                @apply w-full rounded-lg border border-line bg-white px-4 py-3 text-sm text-ink placeholder:text-ink/40 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand;
            }
            /* One choice on the design page (size, layer count, flavor): a tile that turns brand-tinted when picked. */
            .choice {
                @apply relative rounded-lg border border-line bg-white text-left text-sm text-ink transition hover:border-brand/60 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2;
            }
            .choice[aria-pressed="true"] {
                @apply border-brand bg-brand-soft ring-1 ring-brand;
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
        <div class="bg-brand px-4 py-2 text-center text-xs font-medium tracking-wide text-white/90">
            Custom cakes, made to order
        </div>
    @endunless

    <header class="sticky top-0 z-40 border-b border-line bg-paper/90 backdrop-blur">
        <div class="mx-auto flex items-center justify-between px-4 {{ $admin ? 'min-h-20 max-w-7xl flex-wrap gap-x-6 gap-y-3 py-4' : 'h-20 max-w-5xl' }}">

            @if ($admin)
                <a href="{{ route('admin.orders.index') }}" class="font-display text-2xl font-semibold leading-none text-brand">
                    Cake Studio
                    <span class="ml-2 rounded-md bg-ink px-2.5 py-1 align-middle font-sans text-[0.6rem] font-medium uppercase tracking-[0.2em] text-white">Admin</span>
                </a>

                {{-- Wraps under the logo on phones --}}
                <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[0.7rem] font-medium uppercase tracking-[0.22em] sm:gap-x-8">
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
                <a href="{{ route('cake-studio.index') }}" class="flex items-center gap-2.5 font-display text-2xl font-semibold leading-none text-brand">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand text-white" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16M5 21v-6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6M5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0M12 13V9"/><path d="M12 6.5a1.3 1.3 0 0 0 1.3-1.3C13.3 4.2 12 3 12 3s-1.3 1.2-1.3 2.2A1.3 1.3 0 0 0 12 6.5Z"/></svg>
                    </span>
                    Cake Studio
                </a>

                <nav class="flex items-center gap-5 text-sm font-medium sm:gap-7">
                    <a href="{{ route('cake-studio.index') }}"
                       class="hidden sm:inline {{ request()->routeIs('cake-studio.*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Design</a>
                    <a href="{{ route('orders.track') }}"
                       class="{{ request()->routeIs('orders.track*') ? 'text-brand' : 'text-ink/70 hover:text-brand' }}">Track order</a>

                    <a href="{{ route('cart.index') }}" class="btn btn-sm">
                        Cart
                        @if ($cartCount > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-md bg-white px-1 text-xs font-semibold text-brand">{{ $cartCount }}</span>
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
            <ol class="mb-8 flex items-center justify-center gap-4 text-sm font-medium">
                @foreach (['Design', 'Cart & details'] as $i => $label)
                    @php $active = $step === $i + 1; @endphp
                    <li class="flex items-center gap-2.5 {{ $active ? 'text-brand' : 'text-ink/70' }}">
                        <span class="grid h-7 w-7 place-items-center rounded-full border text-xs {{ $active ? 'border-brand bg-brand text-white' : 'border-line' }}">{{ $i + 1 }}</span>
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
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">Designed around you</p>
                <p class="mt-3 font-display text-3xl font-semibold leading-tight sm:text-4xl">Every cake is made to order.</p>

                <nav class="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm font-medium">
                    <a href="{{ route('cake-studio.index') }}" class="hover:text-white/70">Design a cake</a>
                    <a href="{{ route('orders.track') }}" class="hover:text-white/70">Track order</a>
                    <a href="{{ route('cart.index') }}" class="hover:text-white/70">Cart</a>
                </nav>
            </div>
        </footer>
    @endunless
</body>
</html>
