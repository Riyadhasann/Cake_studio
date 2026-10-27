{{-- The top of an admin page: its title, one or two lines on what it is for, and optional buttons on the right. --}}
@props(['title', 'description' => null])

<header class="flex flex-wrap items-start justify-between gap-4 border-b border-line pb-5">
    <div class="min-w-0 max-w-3xl">
        <h1 class="text-2xl font-bold text-ink sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="admin-hint mt-1.5">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
