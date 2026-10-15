@props(['eyebrow' => null, 'script' => null, 'title'])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif

    @if ($script)
        <p class="mt-3 font-display text-xl font-medium text-brand sm:text-2xl">{{ $script }}</p>
    @endif

    <h1 class="heading mt-2 text-4xl sm:text-5xl">{{ $title }}</h1>

    @if ($slot->isNotEmpty())
        <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-ink/75">{{ $slot }}</p>
    @endif
</div>
