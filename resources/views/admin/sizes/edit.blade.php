<x-studio-layout :title="'Edit '.$size->label" :admin="true">
    <x-back-link :href="route('admin.sizes.index')" label="Back to Sizes" />

    <h1 class="heading mt-6 text-3xl">Edit {{ $size->label }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-7 text-ink/90">
        A new price applies to cakes added to a cart from now on. Orders already placed keep their price.
    </p>

    @include('admin.sizes._alerts')

    <section class="panel mt-5 max-w-xl p-4">
        <form action="{{ route('admin.sizes.update', $size) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.sizes._fields', ['size' => $size])
            <button type="submit" class="btn">Save size</button>
        </form>
    </section>
</x-studio-layout>
