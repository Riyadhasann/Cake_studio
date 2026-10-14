{{-- A saved variant drawn with its slots colored by type. The base SVG is cleaned again on the way out. --}}
<svg viewBox="0 0 {{ $variant->view_box_width }} {{ $variant->view_box_height }}" class="{{ $class ?? '' }}"
     role="img" aria-label="{{ $variant->layers }}-layer cake shape">
    @foreach ($variant->slots as $slot)
        <path d="{{ $slot->svg_path }}" fill="{{ $slot->type === 'sponge' ? '#e9c9a1' : '#f6dfe4' }}" stroke="#905954" stroke-width="2" />
    @endforeach
    @if ($variant->base_svg)
        <g>{!! app(\App\Support\SvgSanitizer::class)->sanitize($variant->base_svg) !!}</g>
    @endif
</svg>
