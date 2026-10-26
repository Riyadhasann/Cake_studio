@props(['eyebrow' => null, 'script' => null, 'title'])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @if ($eyebrow)
        <p class="eyebrow" data-reveal>{{ $eyebrow }}</p>
    @endif

    @if ($script)
        <p class="mt-3 font-display text-xl font-medium text-brand sm:text-2xl" data-reveal>{{ $script }}</p>
    @endif

    <h1 class="heading mt-1 text-3xl" data-reveal>{{ $title }}</h1>

    @if ($slot->isNotEmpty())
        <p class="mx-auto mt-1 max-w-xl text-sm leading-6 text-ink/75" data-reveal>{{ $slot }}</p>
    @endif
</div>
