{{--
    An SVG pattern that crops an image to whatever shape uses it, like CSS object-fit: cover:
    one tile the size of the shape's box, with the image's own size as the view box, sliced to fill it.
    Without a known size the image is stretched to the box instead. The href is bound by Alpine so the
    error listener is in place before the image starts loading.
--}}
@props(['id', 'url', 'size' => null])

@if ($size)
    <pattern id="{{ $id }}" patternUnits="objectBoundingBox" width="1" height="1" viewBox="0 0 {{ $size[0] }} {{ $size[1] }}" preserveAspectRatio="xMidYMid slice">
        <image :href="@js($url)" width="{{ $size[0] }}" height="{{ $size[1] }}" @@error="markBroken($el.getAttribute('href'))" />
    </pattern>
@else
    <pattern id="{{ $id }}" patternUnits="objectBoundingBox" patternContentUnits="objectBoundingBox" width="1" height="1">
        <image :href="@js($url)" width="1" height="1" preserveAspectRatio="none" @@error="markBroken($el.getAttribute('href'))" />
    </pattern>
@endif
