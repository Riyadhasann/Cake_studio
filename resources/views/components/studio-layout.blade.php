@props(['title' => null, 'description' => null, 'step' => null, 'admin' => false, 'alerts' => true])
@inject('cart', 'App\Services\CartService')
@php
    $shop = config('studio.name');
    $cartCount = $admin ? 0 : $cart->count();
    $pageTitle = $title ? $title.' · '.$shop : $shop;
    $description ??= config('studio.tagline');

    $links = $admin
        ? [
            ['route' => 'admin.orders.index', 'label' => 'Orders', 'active' => 'admin.orders.*'],
            ['route' => 'admin.cake-visuals.index', 'label' => 'Cake images', 'active' => 'admin.cake-visuals.*'],
            ['route' => 'admin.designs.index', 'label' => 'Designs', 'active' => 'admin.designs.*'],
            ['route' => 'admin.sizes.index', 'label' => 'Sizes', 'active' => 'admin.sizes.*'],
            ['route' => 'admin.cake-variants.index', 'label' => 'Cake shapes', 'active' => 'admin.cake-variants.*'],
        ]
        : [
            ['route' => 'cake-studio.index', 'label' => 'Design a cake', 'active' => 'cake-studio.*'],
            ['route' => 'orders.track', 'label' => 'Track order', 'active' => 'orders.*'],
        ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>

    @if ($admin)
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="description" content="{{ $description }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $shop }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Scroll reveal on customer pages only; set before the first paint so cards don't flash in and out --}}
    @if (! $admin)
        <script>
            if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('js-motion');
        </script>
    @endif

    @vite(['resources/css/studio.css', 'resources/js/studio.js'])
</head>
<body class="page-bg flex min-h-screen flex-col font-sans text-ink antialiased">
    <a href="#main" class="sr-only z-50 rounded-lg bg-cocoa px-3 py-1.5 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    <header x-data="{ open: false }" @keydown.escape.window="open = false"
            class="site-header sticky top-0 z-40 border-b border-line bg-white/90 shadow-box backdrop-blur-md">
        <div class="mx-auto flex h-14 items-center justify-between gap-4 px-4 sm:px-6 {{ $admin ? 'max-w-7xl' : 'max-w-6xl' }}">
            <a href="{{ $admin ? route('admin.orders.index') : route('cake-studio.index') }}" class="flex shrink-0 items-center gap-2" aria-label="{{ $shop }}{{ $admin ? ' admin' : '' }}">
                <span class="grid size-8 place-items-center rounded-lg bg-brand text-white shadow-sm" aria-hidden="true">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16M5 21v-6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6M5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0M12 13V9"/><path d="M12 6.5a1.3 1.3 0 0 0 1.3-1.3C13.3 4.2 12 3 12 3s-1.3 1.2-1.3 2.2A1.3 1.3 0 0 0 12 6.5Z"/></svg>
                </span>
                <span class="font-display text-xl leading-none">{{ $shop }}</span>
                @if ($admin)
                    <span class="rounded-md bg-brand-soft px-1.5 py-0.5 text-[0.65rem] font-bold tracking-wider text-brand-dark uppercase">Admin</span>
                @endif
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Main">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" class="nav-link" @if (request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @if ($admin)
                    <a href="{{ route('cake-studio.index') }}" class="btn-outline btn-sm hidden sm:inline-flex" target="_blank" rel="noopener">View shop</a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="btn-outline btn-sm">Sign out</button>
                        </form>
                    @endauth
                @else
                    <a href="{{ route('cart.index') }}" class="btn-outline btn-sm relative"
                       aria-label="Cart{{ $cartCount ? ', '.$cartCount.' '.str('item')->plural($cartCount) : ', empty' }}" @if (request()->routeIs('cart.*')) aria-current="page" @endif>
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
                        Cart
                        @if ($cartCount > 0)
                            <span class="grid h-4.5 min-w-4.5 place-items-center rounded-full bg-brand px-1 text-[0.65rem] font-bold text-white">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif

                <button type="button" @click="open = ! open" :aria-expanded="open" aria-controls="mobile-nav"
                        class="btn-outline btn-sm md:hidden" aria-label="Menu">
                    <svg x-show="! open" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="open" x-cloak class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        <nav id="mobile-nav" x-show="open" x-cloak x-transition.opacity.duration.150ms @click.outside="open = false"
             class="border-t border-line bg-white px-4 py-2 shadow-card md:hidden" aria-label="Main">
            <div class="flex flex-col gap-0.5">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" class="nav-link py-2" @if (request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a>
                @endforeach
                @if ($admin)
                    <a href="{{ route('cake-studio.index') }}" class="nav-link py-2">View shop</a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link w-full py-2 text-left">Sign out</button>
                        </form>
                    @endauth
                @endif
            </div>
        </nav>
    </header>

    <main id="main" class="mx-auto w-full flex-1 px-4 py-6 sm:px-6 {{ $admin ? 'max-w-7xl' : 'max-w-6xl' }}">
        @if ($alerts && ! $admin)
            <x-order-alerts />
        @endif

        @if ($step)
            <ol class="mb-5 flex items-center justify-center gap-3 text-sm font-semibold" aria-label="Order progress">
                @foreach (['Design', 'Cart & delivery'] as $i => $label)
                    @php
                        $number = $i + 1;
                        $done = $step > $number;
                        $active = $step === $number;
                    @endphp
                    <li class="flex items-center gap-2 {{ $active ? 'text-brand' : 'text-ink/70' }}" @if ($active) aria-current="step" @endif>
                        <span class="grid size-5 place-items-center rounded-full text-[0.65rem] font-bold {{ $active ? 'bg-brand text-white' : ($done ? 'bg-sage text-white' : 'border border-line bg-white') }}">
                            @if ($done)
                                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                                <span class="sr-only">Done:</span>
                            @else
                                {{ $number }}
                            @endif
                        </span>
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
        <footer class="mt-10 border-t border-white/60 bg-white/55 backdrop-blur-sm">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-4 text-sm text-ink/70 sm:flex-row sm:px-6">
                <p>&copy; {{ now()->year }} {{ $shop }} · Prices in BDT (৳)</p>
                <nav class="flex flex-wrap items-center gap-x-4 gap-y-1" aria-label="Footer">
                    <a href="{{ route('cake-studio.index') }}" class="hover:text-brand">Design a cake</a>
                    <a href="{{ route('orders.track') }}" class="hover:text-brand">Track order</a>
                    <a href="{{ route('cart.index') }}" class="hover:text-brand">Cart</a>
                    @if (config('studio.phone'))
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', config('studio.phone')) }}" class="hover:text-brand">{{ config('studio.phone') }}</a>
                    @endif
                    @if (config('studio.email'))
                        <a href="mailto:{{ config('studio.email') }}" class="hover:text-brand">{{ config('studio.email') }}</a>
                    @endif
                </nav>
            </div>
        </footer>
    @endunless
</body>
</html>
