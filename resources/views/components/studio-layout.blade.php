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
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Nunito+Sans:opsz,wght@6..12,400..800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        /*
         * THEME TOKENS: change the look of the whole app here.
         * Bakery theme from docs/Cake Studio theme sample.png: warm peach page, cream cards, terracotta for the
         * picked choice, dark chocolate for the main buttons, dark brown text, serif headings.
         * Text is brown on light colors, or white on terracotta (4.8:1) and chocolate (11:1).
         */
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        // Headings in DM Serif Display (one solid weight), everything else in Nunito Sans.
                        display: ['"DM Serif Display"', 'Georgia', 'serif'],
                        sans: ['"Nunito Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        paper: '#fdf3ea', // peach page
                        petal: '#fcefe4', // unpicked tiles, tinted surfaces
                        line: '#e6c9b2', // borders: strong enough to see every box
                        ink: '#4a2412', // dark brown
                        brand: { DEFAULT: '#b5562a', dark: '#8f4220', soft: '#fbe4d3' }, // terracotta
                        gold: { DEFAULT: '#e3a15a', soft: '#fdebd6' }, // caramel
                        cocoa: { DEFAULT: '#6b3416', dark: '#542810' }, // dark chocolate: main buttons
                        cream: '#fff8f2', // cards
                        brown: '#7a5141', // light brown, for secondary labels
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
                @apply text-xs font-bold uppercase tracking-[0.2em] text-brand;
            }
            .heading {
                @apply font-display font-normal leading-[1.1] text-ink;
            }
            /* Main buttons: dark chocolate with white text, softly rounded. */
            .btn {
                @apply inline-flex items-center justify-center gap-2 rounded-2xl border border-cocoa bg-cocoa px-7 py-3 text-base font-bold text-white shadow-[0_12px_24px_-14px_rgba(107,52,22,0.9)] transition hover:border-cocoa-dark hover:bg-cocoa-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60;
            }
            .btn-outline {
                @apply inline-flex items-center justify-center gap-2 rounded-2xl border border-brand/50 bg-white px-7 py-3 text-sm font-bold text-brand transition hover:border-brand hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60;
            }
            .btn-sm {
                @apply px-5 py-2;
            }
            .panel {
                @apply rounded-3xl border border-line bg-cream shadow-[0_2px_4px_rgba(74,36,18,0.06),0_20px_44px_-18px_rgba(74,36,18,0.38)];
            }
            .field {
                @apply w-full rounded-xl border border-line bg-white shadow-[inset_0_1px_2px_rgba(74,36,18,0.06)] px-4 py-3 text-sm text-ink placeholder:text-ink/50 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20;
            }
            /* One choice on the design page (a flavor): a peach tile that turns white with a terracotta outline when picked. */
            .choice {
                @apply relative rounded-2xl border border-line bg-petal text-left text-sm text-ink shadow-[0_2px_6px_-2px_rgba(74,36,18,0.15)] transition hover:border-brand/50 hover:bg-white hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2;
            }
            .choice[aria-pressed="true"] {
                @apply border-brand bg-white font-bold ring-1 ring-brand;
            }
            .label {
                @apply mb-2 block text-sm font-medium text-ink;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }

        /* MOTION: everything here is switched off for "reduce motion" (bottom of this block). */
        :root { --ease-out: cubic-bezier(.2, .8, .2, 1); --ease-spring: cubic-bezier(.3, 1.6, .5, 1); }

        /* Page to page: the content slides in, the header stays put (browsers without view transitions just load the page). */
        @view-transition { navigation: auto; }
        .site-header { view-transition-name: site-header; transition: box-shadow .3s ease; }
        .site-header.is-scrolled { box-shadow: 0 10px 30px -18px rgba(59, 38, 34, .35); }
        ::view-transition-old(root) { animation: page-out .2s ease both; }
        ::view-transition-new(root) { animation: page-in .45s var(--ease-out) both; }
        @keyframes page-out { to { opacity: 0; transform: translateY(-6px); } }
        @keyframes page-in { from { opacity: 0; transform: translateY(14px); } }

        /* The page background: warm peach with soft caramel and cream glows (the cream cards sit on it) */
        .page-bg {
            background-color: #fdf3ea;
            background-image:
                radial-gradient(55% 45% at 0% 0%, rgba(240, 186, 144, .45), transparent 70%),
                radial-gradient(45% 40% at 100% 5%, rgba(255, 255, 255, .75), transparent 70%),
                radial-gradient(60% 50% at 80% 100%, rgba(227, 161, 90, .28), transparent 70%);
            background-size: 100% 100%, 100% 100%, 100% 100%;
            background-attachment: fixed;
        }

        /* Two soft, slowly drifting glows behind the page: caramel and cream */
        .ambient { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        .ambient::before, .ambient::after { content: ''; position: absolute; width: 55vmax; height: 55vmax; border-radius: 50%; filter: blur(90px); opacity: .45; }
        .ambient::before { top: -20vmax; left: -15vmax; background: #f6d3b5; animation: drift 26s ease-in-out infinite alternate; }
        .ambient::after { right: -20vmax; bottom: -25vmax; background: #fde7d2; animation: drift 32s ease-in-out infinite alternate-reverse; }
        @keyframes drift { to { transform: translate(10vmax, 8vmax) scale(1.15); } }

        /* Scroll reveal: [data-reveal] and every .panel fade up and un-blur, one after another (see the script at the end). */
        .js-motion [data-reveal] {
            opacity: 0; transform: translateY(22px); filter: blur(6px);
            transition: opacity .7s var(--ease-out), transform .8s var(--ease-out), filter .7s var(--ease-out);
            transition-delay: var(--reveal-delay, 0ms);
        }
        .js-motion [data-reveal].is-visible { opacity: 1; transform: none; filter: none; }

        /* Buttons: a light sweep on hover, a small lift, a press */
        .btn { position: relative; overflow: hidden; isolation: isolate; }
        .btn::after {
            content: ''; position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .35) 50%, transparent 70%);
            transform: translateX(-120%); transition: transform .75s var(--ease-out);
        }
        .btn:hover::after { transform: translateX(120%); }
        .btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 28px -12px rgba(181, 86, 42, .5); }
        .btn:active:not(:disabled), .btn-outline:active:not(:disabled) { transform: scale(.97); }

        /* Choice tiles lift on hover and pop when picked; the check mark springs in */
        .choice { transition: transform .25s var(--ease-out), box-shadow .25s ease, border-color .2s ease, background-color .2s ease; }
        .choice:hover { transform: translateY(-2px); }
        .choice:active { transform: scale(.97); }
        .choice[aria-pressed="true"] { animation: choice-pop .45s var(--ease-spring); }
        .choice[aria-pressed="true"] > svg { animation: check-in .4s var(--ease-spring) both; }
        @keyframes choice-pop { from { transform: scale(.94); } }
        @keyframes check-in { from { opacity: 0; transform: scale(0) rotate(-60deg); } }

        /* Step numbers send out one caramel ring when their card comes into view */
        .step-dot { position: relative; transition: background-color .3s ease, color .3s ease; }
        .step-dot::after { content: ''; position: absolute; inset: 0; border-radius: inherit; }
        .is-visible .step-dot::after { animation: ring 1.4s .35s ease-out; }
        .panel:hover .step-dot { background: #b5562a; color: #fff; }
        @keyframes ring { from { box-shadow: 0 0 0 0 rgba(227, 161, 90, .8); } to { box-shadow: 0 0 0 14px rgba(227, 161, 90, 0); } }

        /* Cake preview: floats gently and tilts toward the pointer */
        .cake-float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 50% { transform: translateY(-8px); } }
        [data-tilt] { transform: perspective(1000px) rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)); transition: transform .6s var(--ease-out); }

        /* Caramel shimmer for headlines on chocolate */
        .text-shimmer {
            background: linear-gradient(90deg, #e3a15a 0%, #fff8f2 25%, #e3a15a 50%, #fff8f2 75%, #e3a15a 100%) 0 0 / 200% 100%;
            -webkit-background-clip: text; background-clip: text; color: transparent;
            animation: shimmer 7s linear infinite;
        }
        @keyframes shimmer { to { background-position: -200% 0; } }

        /* DM Serif Display has a single weight; browsers would otherwise fake a blurry bold */
        .font-display { font-weight: 400 !important; }

        /* A card with an open dropdown sits above the cards after it, so the list is never covered */
        .panel:has([aria-expanded="true"]) { position: relative; z-index: 30; }

        /* Dialogs spring in each time they open */
        .dialog-pop { animation: dialog-pop .4s var(--ease-spring); }
        @keyframes dialog-pop { from { opacity: 0; transform: translateY(16px) scale(.94); } }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            ::view-transition-old(*), ::view-transition-new(*), ::view-transition-group(*) { animation: none !important; }
            [data-tilt] { transform: none; }
        }
    </style>

    <script>
        (() => {
            const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            // Scroll reveal on customer pages only; admin pages stay put.
            if (! still && @js(! $admin)) document.documentElement.classList.add('js-motion');

            // The 3D cake viewer (resources/js/cake-3d.js), once its script has run.
            window.whenCake3d = () => window.CakeViewer3D
                ? Promise.resolve(window.CakeViewer3D)
                : new Promise((resolve) => window.addEventListener('cake-3d:ready', () => resolve(window.CakeViewer3D), { once: true }));
            // Whether this browser can draw 3D at all.
            window.canDraw3d = () => {
                try {
                    const canvas = document.createElement('canvas');
                    return Boolean(canvas.getContext('webgl2') || canvas.getContext('webgl'));
                } catch (e) {
                    return false;
                }
            };

            // Counts a price to its new value; Alpine calls it from x-effect, e.g. x-effect="countTo($el, totalPrice, format)".
            window.countTo = (el, value, format) => {
                const to = Number(value) || 0;
                const from = el._shown ?? to;
                cancelAnimationFrame(el._frame);
                if (still || from === to) { el._shown = to; el.textContent = format(to); return; }

                const start = performance.now();
                const step = (now) => {
                    const t = Math.min(1, (now - start) / 500);
                    el._shown = from + (to - from) * (1 - Math.pow(1 - t, 3));
                    el.textContent = format(t < 1 ? el._shown : to);
                    if (t < 1) el._frame = requestAnimationFrame(step);
                };
                el._frame = requestAnimationFrame(step);
                el.parentElement?.animate(
                    [{ transform: 'scale(1)' }, { transform: 'scale(1.07)', color: '#b5562a' }, { transform: 'scale(1)' }],
                    { duration: 450, easing: 'ease-out' },
                );
            };
        })();
    </script>
</head>
<body class="page-bg flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">
    <div class="ambient" aria-hidden="true"></div>

    @unless ($admin)
        <div class="bg-cocoa px-4 py-2 text-center text-xs font-semibold tracking-wide text-cream">
            <span class="mr-1.5 inline-block size-1.5 rounded-full bg-gold align-middle" aria-hidden="true"></span>
            Custom cakes, made to order
        </div>
    @endunless

    <header class="site-header sticky top-0 z-40 border-b border-line bg-paper/90 backdrop-blur">
        <div class="mx-auto flex items-center justify-between px-4 {{ $admin ? 'min-h-20 max-w-7xl flex-wrap gap-x-6 gap-y-3 py-4' : 'h-20 max-w-6xl' }}">

            @if ($admin)
                <a href="{{ route('admin.orders.index') }}" class="font-display text-2xl font-bold leading-none text-ink">
                    Cake <span class="text-brand">Studio</span>
                    <span class="ml-2 rounded-full bg-cocoa px-2.5 py-1 align-middle font-sans text-[0.6rem] font-semibold uppercase tracking-[0.2em] text-cream">Admin</span>
                </a>

                {{-- Wraps under the logo on phones --}}
                <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[0.7rem] font-semibold uppercase tracking-[0.22em] sm:gap-x-8">
                    <a href="{{ route('admin.orders.index') }}"
                       class="{{ request()->routeIs('admin.orders.*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Orders</a>
                    <a href="{{ route('admin.cake-visuals.index') }}"
                       class="{{ request()->routeIs('admin.cake-visuals.*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Cake images</a>
                    <a href="{{ route('admin.designs.index') }}"
                       class="{{ request()->routeIs('admin.designs.*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Designs</a>
                    <a href="{{ route('admin.sizes.index') }}"
                       class="{{ request()->routeIs('admin.sizes.*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Sizes</a>
                    <a href="{{ route('admin.cake-variants.index') }}"
                       class="{{ request()->routeIs('admin.cake-variants.*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Cake shapes</a>
                </nav>
            @else
                <a href="{{ route('cake-studio.index') }}" class="flex items-center gap-2.5 font-display text-2xl font-bold leading-none text-ink">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand text-white shadow-[0_8px_18px_-8px_rgba(181, 86, 42,0.7)]" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16M5 21v-6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6M5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0M12 13V9"/><path d="M12 6.5a1.3 1.3 0 0 0 1.3-1.3C13.3 4.2 12 3 12 3s-1.3 1.2-1.3 2.2A1.3 1.3 0 0 0 12 6.5Z"/></svg>
                    </span>
                    <span>Cake <span class="text-brand">Studio</span></span>
                </a>

                <nav class="flex items-center gap-5 text-sm font-semibold sm:gap-7">
                    <a href="{{ route('cake-studio.index') }}"
                       class="hidden sm:inline {{ request()->routeIs('cake-studio.*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Design</a>
                    <a href="{{ route('orders.track') }}"
                       class="{{ request()->routeIs('orders.track*') ? 'text-brand' : 'text-ink/80 hover:text-brand' }}">Track order</a>

                    <a href="{{ route('cart.index') }}" class="btn btn-sm">
                        Cart
                        @if ($cartCount > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 text-xs font-bold text-brand">{{ $cartCount }}</span>
                        @endif
                    </a>
                </nav>
            @endif
        </div>
    </header>

    <main class="mx-auto w-full flex-1 px-4 py-10 {{ $admin ? 'max-w-7xl' : 'max-w-6xl' }}">
        @if ($alerts && ! $admin)
            <x-order-alerts />
        @endif

        @if ($step)
            <ol class="mb-8 flex items-center justify-center gap-4 text-sm font-semibold">
                @foreach (['Design', 'Cart & details'] as $i => $label)
                    @php $active = $step === $i + 1; @endphp
                    <li class="flex items-center gap-2.5 {{ $active ? 'text-brand' : 'text-ink/80' }}">
                        <span class="grid size-5 place-items-center rounded-full border text-[0.65rem] font-bold {{ $active ? 'border-brand bg-brand text-white' : 'border-line bg-white' }}">{{ $i + 1 }}</span>
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
        <footer class="mt-16 border-t-4 border-gold bg-cocoa text-cream">
            <div class="mx-auto max-w-6xl px-4 py-16 text-center">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold">Designed around you</p>
                <p class="mt-3 text-shimmer font-display text-3xl font-semibold leading-tight sm:text-4xl">Every cake is made to order.</p>

                <nav class="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm font-semibold">
                    <a href="{{ route('cake-studio.index') }}" class="text-cream/90 hover:text-gold">Design a cake</a>
                    <a href="{{ route('orders.track') }}" class="text-cream/90 hover:text-gold">Track order</a>
                    <a href="{{ route('cart.index') }}" class="text-cream/90 hover:text-gold">Cart</a>
                </nav>
            </div>
        </footer>
    @endunless

    <script>
        (() => {
            const header = document.querySelector('.site-header');
            const onScroll = () => header?.classList.toggle('is-scrolled', window.scrollY > 8);
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            if (! document.documentElement.classList.contains('js-motion')) return;

            // Reveal: cards that enter the view together come in one after another.
            const targets = document.querySelectorAll('main [data-reveal], main .panel');
            const io = new IntersectionObserver((entries) => {
                entries.filter((entry) => entry.isIntersecting).forEach((entry, i) => {
                    const el = entry.target;
                    el.style.setProperty('--reveal-delay', i * 90 + 'ms');
                    el.classList.add('is-visible');
                    el.addEventListener('transitionend', () => el.style.removeProperty('--reveal-delay'), { once: true });
                    io.unobserve(el);
                });
            }, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });
            targets.forEach((el) => { el.setAttribute('data-reveal', ''); io.observe(el); });

            // Tilt toward the pointer (mouse and pen only).
            if (! window.matchMedia('(hover: hover)').matches) return;
            document.querySelectorAll('[data-tilt]').forEach((el) => {
                el.addEventListener('pointermove', (e) => {
                    const r = el.getBoundingClientRect();
                    el.style.setProperty('--ry', (((e.clientX - r.left) / r.width - 0.5) * 10).toFixed(2) + 'deg');
                    el.style.setProperty('--rx', (((e.clientY - r.top) / r.height - 0.5) * -8).toFixed(2) + 'deg');
                });
                el.addEventListener('pointerleave', () => {
                    el.style.setProperty('--rx', '0deg');
                    el.style.setProperty('--ry', '0deg');
                });
            });
        })();
    </script>
</body>
</html>
