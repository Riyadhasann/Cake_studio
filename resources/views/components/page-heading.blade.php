@props(['eyebrow' => null, 'script' => null, 'title'])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif

    @if ($script)
        <p class="mt-4 font-script text-4xl leading-none text-brand sm:text-5xl">{{ $script }}</p>
    @endif

    <h1 class="heading mt-3 text-5xl sm:text-6xl">{{ $title }}</h1>

    @if ($slot->isNotEmpty())
        <p class="mx-auto mt-5 max-w-xl text-sm leading-7 text-ink/75">{{ $slot }}</p>
    @endif
</div>
