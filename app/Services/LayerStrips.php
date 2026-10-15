<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Finds the strip in a flavor's layer image: the opaque band that runs across it, straight or bent into a V
 * (like a slice of the cut face), on a transparent background. On a cake photo each arm of that strip is
 * mapped onto the matching arm of the slot, so the art lines up with the cut and no see-through part of
 * the image shows. A fully opaque image is a straight strip of its full height.
 */
class LayerStrips
{
    /** Larger images are skipped: decoding them could exhaust PHP's memory. */
    private const MAX_PIXELS = 2048 * 2048;

    public function __construct(private PublicUploads $uploads) {}

    /**
     * The strip's top and bottom edges at its left end, its apex (highest point) and its right end,
     * in the image's pixels; null when the image is not one strip of even thickness.
     *
     * @return array{width: int, height: int, top: list<array{0: int, 1: int}>, bottom: list<array{0: int, 1: int}>}|null
     */
    public function of(?string $path): ?array
    {
        $file = $this->uploads->file($path);

        if (! $file || str_ends_with(strtolower($file), '.svg')) {
            return null;
        }

        // Uploads get random names, so a path never changes content; the time guards hand-placed files.
        /** @var array{width: int, height: int, top: list<array{0: int, 1: int}>, bottom: list<array{0: int, 1: int}>}|false $strip */
        $strip = Cache::rememberForever('layer-strip:v1:'.$path.':'.filemtime($file), fn () => $this->detect($file));

        return $strip ?: null;
    }

    /** @return array{width: int, height: int, top: list<array{0: int, 1: int}>, bottom: list<array{0: int, 1: int}>}|false */
    private function detect(string $file): array|false
    {
        $size = @getimagesize($file);

        if (! $size || ! in_array($size[2], [IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || $size[0] * $size[1] > self::MAX_PIXELS) {
            return false;
        }

        $image = @imagecreatefromstring((string) file_get_contents($file));

        if ($image === false) {
            return false;
        }

        imagepalettetotruecolor($image);
        [$width, $height] = [$size[0], $size[1]];

        $opaque = fn (int $x, int $y): bool => ((imagecolorat($image, $x, $y) >> 24) & 127) < 64;

        // [top, bottom] of the opaque pixels in a column, or null when it has none.
        $span = function (int $x) use ($opaque, $height): ?array {
            for ($top = 0; $top < $height && ! $opaque($x, $top); $top++);

            if ($top === $height) {
                return null;
            }

            for ($bottom = $height - 1; ! $opaque($x, $bottom); $bottom--);

            return [$top, $bottom + 1];
        };

        $strip = $this->measure($span, $width);
        imagedestroy($image);

        return $strip ? ['width' => $width, 'height' => $height, ...$strip] : false;
    }

    /**
     * The strip's edges from the opaque span of each column, or null when it is not one strip of even thickness.
     *
     * @param  \Closure(int): (array{0: int, 1: int}|null)  $span
     * @return array{top: list<array{0: int, 1: int}>, bottom: list<array{0: int, 1: int}>}|null
     */
    private function measure(\Closure $span, int $width): ?array
    {
        $step = max(1, intdiv($width, 64));
        $spans = [];

        foreach ([...range(0, $width - 1, $step), $width - 1] as $x) {
            $columnSpan = $span($x);

            if (! $columnSpan) {
                return null; // a gap across the image: not one strip
            }

            $spans[$x] = $columnSpan;
        }

        $thickness = array_map(fn (array $s) => $s[1] - $s[0], $spans);

        if (min($thickness) < 0.8 * max($thickness)) {
            return null;
        }

        // The apex is the highest column, found exactly around the best sample; a straight strip bends in the middle.
        $apex = 0;

        foreach ($spans as $x => $s) {
            if ($s[0] < $spans[$apex][0]) {
                $apex = $x;
            }
        }

        foreach (range(max(0, $apex - $step), min($width - 1, $apex + $step)) as $x) {
            $columnSpan = $spans[$x] ?? $span($x);

            if ($columnSpan && $columnSpan[0] < $spans[$apex][0]) {
                [$apex, $spans[$x]] = [$x, $columnSpan];
            }
        }

        if ($spans[0][0] - $spans[$apex][0] < 2 && $spans[$width - 1][0] - $spans[$apex][0] < 2) {
            $apex = intdiv($width, 2);
            $middle = $spans[$apex] ?? $span($apex);

            if (! $middle) {
                return null;
            }

            $spans[$apex] = $middle;
        }

        [$left, $middle, $right] = [$spans[0], $spans[$apex], $spans[$width - 1]];

        return [
            'top' => [[0, $left[0]], [$apex, $middle[0]], [$width, $right[0]]],
            'bottom' => [[0, $left[1]], [$apex, $middle[1]], [$width, $right[1]]],
        ];
    }
}
