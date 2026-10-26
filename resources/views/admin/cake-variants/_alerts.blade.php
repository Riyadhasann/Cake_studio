@include('admin.sizes._alerts')

@if (session('warning'))
    <div class="mt-4 rounded-xl border border-clay/30 bg-clay-soft px-5 py-4 text-sm text-clay">{{ session('warning') }}</div>
@endif
