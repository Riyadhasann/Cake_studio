<x-studio-layout title="Preview Images" :admin="true">
    <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

    <h1 class="heading mt-6 text-5xl">Cake preview images</h1>
    <p class="mt-3 max-w-xl text-sm leading-7 text-ink/75">One photo per sponge and cream combination. It shows up live on the design page.</p>

    <div class="mt-8 space-y-4">
        @if (session('success'))
            <div class="rounded-2xl border border-sage/30 bg-sage-soft px-5 py-4 text-sm text-ink">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($previews as $preview)
            <div class="panel p-4">
                <img
                    src="{{ asset('storage/' . $preview->image) }}"
                    onerror="this.onerror=null; this.src='https://placehold.co/400x400?text=No+Image'"
                    alt="Preview of {{ $preview->spongeFlavor->name }} and {{ $preview->creamFlavor->name }}"
                    class="aspect-square w-full rounded-2xl bg-petal object-cover"
                >

                <p class="mt-4 font-display text-2xl font-medium leading-snug">{{ $preview->spongeFlavor->name }} + {{ $preview->creamFlavor->name }}</p>

                <form action="{{ route('admin.cake-previews.update', $preview) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <input type="file" name="image" accept="image/*" required class="w-full text-sm text-ink/80 file:mr-3 file:rounded-lg file:border-0 file:bg-petal file:px-4 file:py-2 file:text-xs file:font-medium file:text-brand hover:file:bg-brand-soft">
                    <button type="submit" class="btn btn-sm w-full">Upload</button>
                </form>
            </div>
        @endforeach
    </div>
</x-studio-layout>
