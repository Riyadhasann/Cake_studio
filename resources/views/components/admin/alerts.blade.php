{{-- What just happened (saved, removed) and anything that went wrong, at the top of an admin page. --}}
@if (session()->hasAny(['success', 'warning', 'error']) || $errors->any())
<div class="mt-5 space-y-3">
    @if (session('success'))
        <div class="alert alert-success flex items-start gap-2" role="status">
            <svg class="mt-0.5 size-4 shrink-0 text-sage" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert border-clay/30 bg-clay-soft text-clay" role="status">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endif
