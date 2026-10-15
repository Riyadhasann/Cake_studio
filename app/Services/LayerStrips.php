<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Finds the strip in a flavor's layer image: the band of art that runs across it, straight or bent into a V
 * (like a slice of the cut face), on a transparent background. Its edges may be wavy or ragged and its
 * thickness may vary: what is measured is the average line of its top edge and of its bottom edge. On a cake
 * photo each arm of the strip is mapped onto the matching arm of a slot by those lines, so the art's own
 * edges, crumbs and shading show as drawn.
 */
class LayerStrips
{
    /** Larger images are skipped: decoding them could exhaust PHP's memory. */
    private const MAX_PIXELS = 2048 * 2048;

    /** Columns measured across the image. */
    private const SAMPLES = 96;

    public function __construct(private PublicUploads $uploads) {}

    /**
     * The average top and bottom edge lines of the strip at its left end, its apex and its right end, in the
     * image's pixels; null when the image has no strip of art running across it.
     *
     * @return array{width: int, height: int, top: list<array{0: float, 1: float}>, bottom: list<array{0: float, 1: float}>}|null
     */
    public function of(?string $path): ?array
    {
        $file = $this->uploads->file($path);

        if (! $file || str_ends_with(strtolower($file), '.svg')) {
            return null;
        }

        // Uploads get random names, so a path never changes content; the time guards hand-placed files.
        /** @var array{width: int, height: int, top: list<array{0: float, 1: float}>, bottom: list<array{0: float, 1: float}>}|false $strip */
        $strip = Cache::rememberForever('layer-strip:v2:'.$path.':'.filemtime($file), fn () => $this->detect($file));

        return $strip ?: null;
    }

    /** @return array{width: int, height: int, top: list<array{0: float, 1: float}>, bottom: list<array{0: float, 1: float}>}|false */
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

        // The first and last opaque pixel of each sampled column.
        $tops = $bottoms = [];
        $step = max(1, $width / self::SAMPLES);

        for ($sample = 0; $sample < self::SAMPLES && $sample * $step < $width; $sample++) {
            $x = (int) min($width - 1, round(($sample + 0.5) * $step));

            for ($top = 0; $top < $height && ((imagecolorat($image, $x, $top) >> 24) & 127) >= 64; $top++);

            if ($top === $height) {
                continue;
            }

            for ($bottom = $height - 1; ((imagecolorat($image, $x, $bottom) >> 24) & 127) >= 64; $bottom--);

            [$tops[$x], $bottoms[$x]] = [$top, $bottom + 1];
        }

        imagedestroy($image);

        // Art across most of the width, or it is not a strip.
        if (count($tops) < 0.6 * min(self::SAMPLES, $width)) {
            return false;
        }

        $apex = $this->apex($tops, $width);
        $top = $this->fit($tops, $apex);
        $bottom = $this->fit($bottoms, $apex);
        $at = fn (array $line, float $x): float => round($line[0] + ($x < $apex ? $line[1] : $line[2]) * ($x - $apex), 1);
        $ends = [0.0, (float) $apex, (float) $width];

        foreach ($ends as $x) {
            if ($at($bottom, $x) - $at($top, $x) < 2) {
                return false; // the edges cross: not a strip
            }
        }

        return [
            'width' => $width,
            'height' => $height,
            'top' => array_map(fn (float $x) => [$x, $at($top, $x)], $ends),
            'bottom' => array_map(fn (float $x) => [$x, $at($bottom, $x)], $ends),
        ];
    }

    /**
     * Where the top edge bends: the column whose two-line fit is best, or the middle when one straight line fits
     * about as well (a straight strip).
     *
     * @param  array<int, int>  $tops
     */
    private function apex(array $tops, int $width): int
    {
        $columns = array_keys($tops);
        $straight = $this->fit($tops, intdiv($width, 2), bent: false);
        [$best, $bestError] = [intdiv($width, 2), $straight[3]];

        // Candidates leave at least three columns on each side.
        foreach (array_slice($columns, 3, -3) as $x) {
            $error = $this->fit($tops, $x)[3];

            if ($error < $bestError) {
                [$best, $bestError] = [$x, $error];
            }
        }

        return $bestError < 0.6 * $straight[3] ? $best : intdiv($width, 2);
    }

    /**
     * The least-squares line through the edge, bent at $apex: [y at the apex, slope left of it, slope right of it, squared error].
     *
     * @param  array<int, int>  $edge
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function fit(array $edge, int $apex, bool $bent = true): array
    {
        // y = c + m1 * min(x - apex, 0) + m2 * max(x - apex, 0); a straight line has m1 = m2.
        $basis = fn (int $x): array => $bent ? [1, min($x - $apex, 0), max($x - $apex, 0)] : [1, $x - $apex];
        $n = $bent ? 3 : 2;
        $a = array_fill(0, $n, array_fill(0, $n + 1, 0.0));

        foreach ($edge as $x => $y) {
            $row = $basis($x);

            foreach ($row as $i => $ri) {
                foreach ($row as $j => $rj) {
                    $a[$i][$j] += $ri * $rj;
                }
                $a[$i][$n] += $ri * $y;
            }
        }

        $solution = $this->solve($a, $n);
        $line = $bent ? [$solution[0], $solution[1], $solution[2]] : [$solution[0], $solution[1], $solution[1]];
        $error = 0.0;

        foreach ($edge as $x => $y) {
            $error += ($line[0] + ($x < $apex ? $line[1] : $line[2]) * ($x - $apex) - $y) ** 2;
        }

        return [$line[0], $line[1], $line[2], $error];
    }

    /**
     * Gaussian elimination with partial pivoting on an augmented n × (n + 1) matrix.
     *
     * @param  array<int, array<int, float>>  $a
     * @return array<int, float>
     */
    private function solve(array $a, int $n): array
    {
        foreach (range(0, $n - 1) as $col) {
            $pivot = $col;

            foreach (range($col, $n - 1) as $row) {
                if (abs($a[$row][$col]) > abs($a[$pivot][$col])) {
                    $pivot = $row;
                }
            }

            [$a[$col], $a[$pivot]] = [$a[$pivot], $a[$col]];

            if (abs($a[$col][$col]) < 1e-9) {
                return array_fill(0, $n, 0.0);
            }

            foreach (range(0, $n - 1) as $row) {
                if ($row !== $col) {
                    $factor = $a[$row][$col] / $a[$col][$col];

                    foreach (range($col, $n) as $k) {
                        $a[$row][$k] -= $factor * $a[$col][$k];
                    }
                }
            }
        }

        return array_map(fn (int $i) => $a[$i][$n] / $a[$i][$i], range(0, $n - 1));
    }
}
