@props(['title' => null, 'description' => null, 'step' => null, 'admin' => false, 'alerts' => true])
@inject('cart', 'App\Services\CartService')
@inject('site', 'App\Services\SiteSettings')
@php
    $shop = $site->get('name');
    $logo = $site->imageUrl('logo');
    $phone = $site->get('phone');
    $email = $site->get('email');
    $cartCount = $admin ? 0 : $cart->count();
    $pageTitle = $title ? $title.' · '.$shop : $shop;
    $description ??= $site->get('tagline');

    $links = [
        ['route' => 'cake-studio.index', 'label' => 'Design a cake', 'active' => 'cake-studio.*'],
        ['route' => 'orders.track', 'label' => 'Track order', 'active' => 'orders.*'],
    ];

    // The admin menu, in groups. Icons are SVG path data (24 × 24, stroked).
    $adminMenu = [
        '' => [
            ['route' => 'admin.home', 'label' => 'Home', 'active' => 'admin.home', 'icon' => 'M3 11.5 12 4l9 7.5M5.5 10v10h13V10M10 20v-5h4v5'],
            ['route' => 'admin.orders.index', 'label' => 'Orders', 'active' => 'admin.orders.*', 'icon' => 'M6 7h12l-1 13H7L6 7ZM9 7V6a3 3 0 0 1 6 0v1'],
        ],
        'Menu' => [
            ['route' => 'admin.sizes.index', 'label' => 'Sizes & prices', 'active' => 'admin.sizes.*', 'icon' => 'M4 20h16M6 20v-5h12v5M8 15v-3h8v3M12 12V8'],
            ['route' => 'admin.cake-visuals.index', 'label' => 'Flavors & cake photo', 'active' => 'admin.cake-visuals.*', 'icon' => 'm12 3 9 5-9 5-9-5 9-5ZM3 13l9 5 9-5'],
            ['route' => 'admin.designs.index', 'label' => 'Cake designs', 'active' => 'admin.designs.*', 'icon' => 'M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4l-5.2 2.7 1-5.8L3.5 9.2l5.9-.9L12 3Z'],
        ],
        'Shop' => [
            ['route' => 'admin.settings.index', 'label' => 'Shop & pictures', 'active' => 'admin.settings.*', 'icon' => 'M4 5h16v14H4zM4 15l4.5-4.5L13 15M13 13l2.5-2.5L20 15M15.5 8.5h.01'],
            ['route' => 'admin.cake-variants.index', 'label' => 'Cake shapes', 'active' => 'admin.cake-variants.*', 'icon' => 'M12 3v18M5 7h14M5 12h14M5 17h14', 'note' => 'Advanced'],
        ],
    ];

    // The admin's pictures replace the bundled ones; the table spot keeps every cake standing on the new background's table.
    $siteStyle = collect([
        '--banner-image' => ($banner = $site->imageUrl('banner')) ? 'url("'.$banner.'")' : null,
        '--scene-image' => ($scene = $site->imageUrl('scene')) ? 'url("'.$scene.'")' : null,
        '--scene-foot' => $scene ? (string) $site->sceneFoot() : null,
        '--scene-aspect' => $scene ? (string) round($site->sceneAspect(), 4) : null,
    ])->filter()->map(fn ($value, $name) => $name.': '.$value.';')->implode(' ');
    // Only plain URL characters reach the style sheet (asset URLs of random file names always are).
    $siteStyle = preg_match('~^[\w\s:;/.\-%?=&"()]*$~', $siteStyle) === 1 ? $siteStyle : '';
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

    @if ($favicon = $site->imageUrl('favicon'))
        <link rel="icon" href="{{ $favicon }}">
    @else
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @endif

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
    @if ($siteStyle)
        <style>:root { {!! $siteStyle !!} }</style>
    @endif
</head>
<body class="page-bg flex min-h-screen flex-col font-sans text-ink antialiased">
    <a href="#main" class="sr-only z-50 rounded-lg bg-cocoa px-3 py-1.5 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

@if ($admin)
    {{-- Admin: a menu down the left on laptops; on phones and tablets a bar whose button slides the menu in --}}
    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="flex min-h-screen flex-1">
        <aside id="admin-menu" class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 -translate-x-full flex-col border-r border-line bg-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
               :class="open && 'translate-x-0 shadow-card'">
            <a href="{{ route('admin.home') }}" class="flex h-14 shrink-0 items-center gap-2 border-b border-line px-4" aria-label="{{ $shop }} admin">
                <span class="grid size-8 shrink-0 place-items-center overflow-hidden rounded-lg {{ $logo ? 'bg-white' : 'bg-brand text-white shadow-sm' }}" aria-hidden="true">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="" class="size-full object-contain">
                    @else
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16M5 21v-6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6M5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0M12 13V9"/><path d="M12 6.5a1.3 1.3 0 0 0 1.3-1.3C13.3 4.2 12 3 12 3s-1.3 1.2-1.3 2.2A1.3 1.3 0 0 0 12 6.5Z"/></svg>
                    @endif
                </span>
                <span class="min-w-0 truncate text-base font-bold leading-none">{{ $shop }}</span>
                <span class="rounded-md bg-brand-soft px-1.5 py-0.5 text-[0.6rem] font-bold tracking-wider text-brand-dark uppercase">Admin</span>
            </a>

            <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Admin">
                @foreach ($adminMenu as $group => $items)
                    @if ($group)
                        <p class="mt-5 mb-1.5 px-3 text-[0.7rem] font-bold tracking-wider text-text-muted uppercase">{{ $group }}</p>
                    @endif
                    <ul class="space-y-0.5">
                        @foreach ($items as $item)
                            @php $current = request()->routeIs($item['active']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}" class="admin-nav-link" @if ($current) aria-current="page" @endif>
                                    <svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $item['icon'] }}"/></svg>
                                    <span class="flex-1">{{ $item['label'] }}</span>
                                    @isset($item['note'])
                                        <span class="text-[0.65rem] font-semibold text-text-muted">{{ $item['note'] }}</span>
                                    @endisset
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>

            <div class="space-y-0.5 border-t border-line p-3">
                <a href="{{ route('cake-studio.index') }}" class="admin-nav-link" target="_blank" rel="noopener">
                    <svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                    <span class="flex-1">View shop</span>
                </a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-nav-link w-full text-left">
                            <svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 16l-4-4 4-4M6 12h10"/></svg>
                            <span class="flex-1">Sign out</span>
                        </button>
                    </form>
                @endauth
            </div>
        </aside>
        <div x-show="open" x-cloak x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-black/25 lg:hidden" aria-hidden="true"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-line bg-white/90 px-4 backdrop-blur-md lg:hidden">
                <button type="button" @click="open = true" :aria-expanded="open" aria-controls="admin-menu" class="btn-outline btn-sm" aria-label="Open the menu">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <span class="truncate text-base font-bold">{{ $shop }}</span>
                <span class="rounded-md bg-brand-soft px-1.5 py-0.5 text-[0.6rem] font-bold tracking-wider text-brand-dark uppercase">Admin</span>
            </header>

            <main id="main" class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>
@else
    <header x-data="{ open: false }" @keydown.escape.window="open = false"
            class="site-header sticky top-0 z-40 border-b border-line bg-white/90 shadow-box backdrop-blur-md">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="{{ route('cake-studio.index') }}" class="flex shrink-0 items-center gap-2" aria-label="{{ $shop }}">
                <span class="grid size-8 shrink-0 place-items-center overflow-hidden rounded-lg {{ $logo ? 'bg-white' : 'bg-brand text-white shadow-sm' }}" aria-hidden="true">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="" class="size-full object-contain">
                    @else
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16M5 21v-6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6M5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0M12 13V9"/><path d="M12 6.5a1.3 1.3 0 0 0 1.3-1.3C13.3 4.2 12 3 12 3s-1.3 1.2-1.3 2.2A1.3 1.3 0 0 0 12 6.5Z"/></svg>
                    @endif
                </span>
                <span class="font-display text-xl leading-none">{{ $shop }}</span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Main">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" class="nav-link" @if (request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('cart.index') }}" class="btn-outline btn-sm relative"
                   aria-label="Cart{{ $cartCount ? ', '.$cartCount.' '.str('item')->plural($cartCount) : ', empty' }}" @if (request()->routeIs('cart.*')) aria-current="page" @endif>
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
                    Cart
                    @if ($cartCount > 0)
                        <span class="grid h-4.5 min-w-4.5 place-items-center rounded-full bg-brand px-1 text-[0.65rem] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>

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
            </div>
        </nav>
    </header>

    <main id="main" class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6">
        @if ($alerts)
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

    <footer class="mt-10 border-t border-white/60 bg-white/55 backdrop-blur-sm">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-4 text-sm text-ink/70 sm:flex-row sm:px-6">
            <p>&copy; {{ now()->year }} {{ $shop }} · Prices in BDT (৳)</p>
            <nav class="flex flex-wrap items-center gap-x-4 gap-y-1" aria-label="Footer">
                <a href="{{ route('cake-studio.index') }}" class="hover:text-brand">Design a cake</a>
                <a href="{{ route('orders.track') }}" class="hover:text-brand">Track order</a>
                <a href="{{ route('cart.index') }}" class="hover:text-brand">Cart</a>
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hover:text-brand">{{ $phone }}</a>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}" class="hover:text-brand">{{ $email }}</a>
                @endif
            </nav>
        </div>
    </footer>
@endif
</body>
</html>
