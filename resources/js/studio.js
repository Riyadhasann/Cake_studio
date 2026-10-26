/*
 * Shop and admin pages (x-studio-layout): Alpine, plus the small helpers the pages call.
 * Module scripts run after the page is parsed, so the components defined in each page's inline
 * <script> (e.g. cakeStudio()) already exist when Alpine starts.
 */
import Alpine from 'alpinejs';

const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// The 3D cake viewer (resources/js/cake-3d.js), once its script has run.
window.whenCake3d = () =>
    window.CakeViewer3D
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
    if (still || from === to) {
        el._shown = to;
        el.textContent = format(to);
        return;
    }

    const start = performance.now();
    const step = (now) => {
        const t = Math.min(1, (now - start) / 500);
        el._shown = from + (to - from) * (1 - Math.pow(1 - t, 3));
        el.textContent = format(t < 1 ? el._shown : to);
        if (t < 1) el._frame = requestAnimationFrame(step);
    };
    el._frame = requestAnimationFrame(step);
    el.parentElement?.animate(
        [{ transform: 'scale(1)' }, { transform: 'scale(1.06)', color: 'var(--color-primary)' }, { transform: 'scale(1)' }],
        { duration: 450, easing: 'ease-out' },
    );
};

// The header gets a shadow once the page scrolls.
const header = document.querySelector('.site-header');
const onScroll = () => header?.classList.toggle('is-scrolled', window.scrollY > 8);
onScroll();
window.addEventListener('scroll', onScroll, { passive: true });

// Reveal (customer pages only; the layout adds js-motion): cards that enter the view together come in one after another.
if (document.documentElement.classList.contains('js-motion')) {
    const targets = document.querySelectorAll('main [data-reveal], main .panel');
    const io = new IntersectionObserver(
        (entries) => {
            entries
                .filter((entry) => entry.isIntersecting)
                .forEach((entry, i) => {
                    const el = entry.target;
                    el.style.setProperty('--reveal-delay', i * 80 + 'ms');
                    el.classList.add('is-visible');
                    el.addEventListener('transitionend', () => el.style.removeProperty('--reveal-delay'), { once: true });
                    io.unobserve(el);
                });
        },
        { rootMargin: '0px 0px -6% 0px', threshold: 0.06 },
    );
    targets.forEach((el) => {
        el.setAttribute('data-reveal', '');
        io.observe(el);
    });

    // Tilt toward the pointer (mouse and pen only).
    if (window.matchMedia('(hover: hover)').matches) {
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
    }
}

window.Alpine = Alpine;
Alpine.start();
