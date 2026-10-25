@props(['href', 'label' => 'Back'])

<a href="{{ $href }}" class="inline-flex items-center gap-2 text-sm font-medium text-ink/80 transition-colors hover:text-brand">
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/>
    </svg>
    {{ $label }}
</a>
