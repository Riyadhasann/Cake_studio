<x-studio-layout title="Cake shapes" :admin="true">
    <x-back-link :href="route('dashboard')" label="Back to Dashboard" />

    <h1 class="heading mt-6 text-5xl">Cake shapes</h1>
    <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/75">
        One drawing per layer count. Each layer has a sponge and a cream shape (a "slot") that is filled with the
        flavor the customer picks. Only published drawings reach the design page; the others use the default drawing.
        A drawing can be published once it has exactly one sponge and one cream for every layer.
    </p>

    @include('admin.cake-variants._alerts')

    <div class="mt-8 grid gap-6 md:grid-cols-3">
        @foreach ($variants as $variant)
            @php($problems = $variant->publishProblems())
            <section class="panel flex flex-col p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="heading text-3xl">{{ $variant->layers }} layers</h2>
                    @if ($variant->is_published)
                        <span class="rounded-full bg-sage-soft px-3 py-1 text-xs font-medium text-sage">Published</span>
                    @elseif ($problems === [])
                        <span class="rounded-full bg-petal px-3 py-1 text-xs font-medium text-ink/70">Ready to publish</span>
                    @else
                        <span class="rounded-full bg-clay-soft px-3 py-1 text-xs font-medium text-clay">Incomplete</span>
                    @endif
                </div>

                <div class="mt-5 grid h-48 place-items-center rounded-2xl bg-petal p-4">
                    @include('admin.cake-variants._drawing', ['variant' => $variant, 'class' => 'max-h-40 w-full'])
                </div>

                <p class="mt-4 text-sm text-ink/70">
                    {{ $variant->slots->count() }} of {{ 2 * $variant->layers }} slots ·
                    {{ $variant->view_box_width }} × {{ $variant->view_box_height }}
                </p>

                <div class="mt-auto flex items-center gap-3 pt-5">
                    <a href="{{ route('admin.cake-variants.edit', $variant) }}" class="btn-outline btn-sm">Edit</a>
                    @if ($variant->is_published)
                        <form action="{{ route('admin.cake-variants.unpublish', $variant) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-ink/70 underline underline-offset-4 hover:text-clay">Unpublish</button>
                        </form>
                    @else
                        <form action="{{ route('admin.cake-variants.publish', $variant) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm">Publish</button>
                        </form>
                    @endif
                </div>
            </section>
        @endforeach

        @foreach ($missingLayers as $layers)
            <section class="panel flex flex-col items-start p-6">
                <h2 class="heading text-3xl">{{ $layers }} layers</h2>
                <p class="mt-3 text-sm leading-7 text-ink/70">No drawing yet. The design page uses the default drawing for {{ $layers }}-layer cakes.</p>
                <form action="{{ route('admin.cake-variants.store') }}" method="POST" class="mt-auto pt-5">
                    @csrf
                    <input type="hidden" name="layers" value="{{ $layers }}">
                    <button type="submit" class="btn btn-sm">Start a drawing</button>
                </form>
            </section>
        @endforeach
    </div>
</x-studio-layout>
