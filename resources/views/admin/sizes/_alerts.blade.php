<div class="mt-5 space-y-4">
    @if (session('success'))
        <div class="rounded-xl border border-sage/30 bg-sage-soft px-5 py-4 text-sm text-ink">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
