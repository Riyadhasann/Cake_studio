@props(['layers'])
@php $total = count($layers); @endphp

<ol {{ $attributes->merge(['class' => 'text-sm text-ink/80']) }}>
    @foreach ($layers as $layer)
        @php
            $where = match (true) {
                $total > 1 && $loop->first => ' (bottom)',
                $total > 1 && $loop->last => ' (top)',
                default => '',
            };
        @endphp
        <li>
            <span class="font-medium text-ink">Layer {{ $layer['position'] }}{{ $where }}:</span>
            {{ $layer['sponge_name'] }} sponge with {{ $layer['cream_name'] }}
        </li>
    @endforeach
</ol>
