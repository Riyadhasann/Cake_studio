<?php

namespace App\Services\Images;

/**
 * Finds the empty cut face in a cake photo, so the admin only has to upload it. The cut is the largest empty
 * area that doesn't touch the photo's edge (the background does): see-through pixels, or a light gray
 * checkerboard painted in to look see-through. It is measured as three points along its top edge and three
 * along its bottom edge (left, middle, right), a little outside the empty area so no gap shows.
 */
class CutFaceFinder
{
    /** The photo is searched at this width first, then each point is measured again at full size. */
    private const SEARCH_WIDTH = 400;

    /** Pixels the points are moved outwards, so the layers overlap the cut's rim instead of leaving a gap. */
    private const PAD = 2;

    private const MAX_PIXELS = 4000 * 4000;

    /** @return array{top: list<array{0: int, 1: int}>, bottom: list<array{0: int, 1: int}>, sponge_share: float}|null */
    public function find(string $file, float $spongeShare = 0.72): ?array
    {
        $size = @getimagesize($file);

        if (! $size || $size[0] * $size[1] > self::MAX_PIXELS) {
            return null;
        }

        $image = @imagecreatefromstring((string) file_get_contents($file));

        if ($image === false) {
            return null;
        }

        imagepalettetotruecolor($image);
        $cut = $this->measure($image, $size[0], $size[1]);
        imagedestroy($image);

        return $cut ? [...$cut, 'sponge_share' => $spongeShare] : null;
    }

    /** @return array{top: list<array{0: int, 1: int}>, bottom: list<array{0: int, 1: int}>}|null */
    private function measure(\GdImage $image, int $width, int $height): ?array
    {
        $scale = max(1, $width / self::SEARCH_WIDTH);
        $small = $scale > 1 ? imagescale($image, (int) round($width / $scale), (int) round($height / $scale)) : $image;

        if ($small === false) {
            return null;
        }

        $region = $this->largestEnclosedEmptyArea($small);

        if ($small !== $image) {
            imagedestroy($small);
        }

        if (! $region || $region['area'] < 0.02 * imagesx($image) / $scale * imagesy($image) / $scale) {
            return null;
        }

        // The rough columns: both ends (a step in, where the column is whole) and the highest point of the top edge.
        $spans = $region['spans'];
        $columns = array_keys($spans);
        [$first, $last] = [$columns[0], $columns[count($columns) - 1]];

        if ($last - $first < 6) {
            return null;
        }

        $apex = $first;

        foreach ($spans as $x => $span) {
            if ($span[0] < $spans[$apex][0]) {
                $apex = $x;
            }
        }

        if ($spans[$first + 2][0] - $spans[$apex][0] < 2 && $spans[$last - 2][0] - $spans[$apex][0] < 2) {
            $apex = intdiv($first + $last, 2); // a straight cut: bend it in the middle, where nothing changes
        }

        // Each point measured again at full size, in the full-size column under the rough one.
        $empty = fn (int $x, int $y): bool => $this->isEmpty($image, $x, $y);
        // [top, bottom] at full size, a little outside the empty area.
        $edges = function (int $x) use ($empty, $spans, $scale, $height): array {
            [$top, $bottom] = $this->refineSpan($empty, (int) round(($x + 0.5) * $scale), (int) round($spans[$x][0] * $scale), (int) round(($spans[$x][1] + 1) * $scale), $scale, $height);

            return [$top - self::PAD, $bottom + self::PAD];
        };
        $points = ['left' => $edges($first + 2), 'middle' => $edges($apex), 'right' => $edges($last - 2)];

        // The ends of the cut, measured along a row through the middle of each end.
        $leftX = $this->refineEdge($empty, (int) round($first * $scale), (int) round(array_sum($points['left']) / 2), -1, $scale, $width);
        $rightX = $this->refineEdge($empty, (int) round(($last + 1) * $scale) - 1, (int) round(array_sum($points['right']) / 2), 1, $scale, $width);
        $apexX = (int) round(($apex + 0.5) * $scale);
        [$leftX, $rightX] = [$leftX - self::PAD, $rightX + 1 + self::PAD];

        // The ends' points were measured a step in; carry their edges out to the ends along the edge slope.
        $leftIn = (int) round(($first + 2.5) * $scale);
        $rightIn = (int) round(($last - 1.5) * $scale);
        $at = fn (int $x, int $edge): int => (int) round($points['left'][$edge]
            + ($points['middle'][$edge] - $points['left'][$edge]) * ($x - $leftIn) / max(1, $apexX - $leftIn));
        $atRight = fn (int $x, int $edge): int => (int) round($points['middle'][$edge]
            + ($points['right'][$edge] - $points['middle'][$edge]) * ($x - $apexX) / max(1, $rightIn - $apexX));

        return [
            'top' => [[$leftX, $at($leftX, 0)], [$apexX, $points['middle'][0]], [$rightX, $atRight($rightX, 0)]],
            'bottom' => [[$leftX, $at($leftX, 1)], [$apexX, $points['middle'][1]], [$rightX, $atRight($rightX, 1)]],
        ];
    }

    /**
     * The largest group of empty pixels that doesn't touch the image's edge, with its top and bottom per column.
     *
     * @return array{area: int, spans: array<int, array{0: int, 1: int}>}|null
     */
    private function largestEnclosedEmptyArea(\GdImage $image): ?array
    {
        [$width, $height] = [imagesx($image), imagesy($image)];
        $seen = [];
        $best = null;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $i = $y * $width + $x;

                if (isset($seen[$i]) || ! $this->isEmpty($image, $x, $y)) {
                    continue;
                }

                // Flood fill this group, noting whether it reaches the edge.
                $seen[$i] = true;
                $stack = [$i];
                $area = 0;
                $touchesEdge = false;
                $spans = [];

                while ($stack) {
                    $j = array_pop($stack);
                    [$px, $py] = [$j % $width, intdiv($j, $width)];
                    $area++;
                    $touchesEdge = $touchesEdge || $px === 0 || $py === 0 || $px === $width - 1 || $py === $height - 1;
                    $spans[$px] = isset($spans[$px]) ? [min($spans[$px][0], $py), max($spans[$px][1], $py)] : [$py, $py];

                    foreach ([[$px - 1, $py], [$px + 1, $py], [$px, $py - 1], [$px, $py + 1]] as [$nx, $ny]) {
                        $n = $ny * $width + $nx;

                        if ($nx >= 0 && $ny >= 0 && $nx < $width && $ny < $height && ! isset($seen[$n]) && $this->isEmpty($image, $nx, $ny)) {
                            $seen[$n] = true;
                            $stack[] = $n;
                        }
                    }
                }

                if (! $touchesEdge && $area > ($best['area'] ?? 0)) {
                    ksort($spans);
                    $best = ['area' => $area, 'spans' => $spans];
                }
            }
        }

        return $best;
    }

    /**
     * The empty span of a full-size column near a rough one: the first empty pixel from above and the last from below.
     *
     * @param  \Closure(int, int): bool  $empty
     * @return array{0: int, 1: int}
     */
    private function refineSpan(\Closure $empty, int $x, int $roughTop, int $roughBottom, float $scale, int $height): array
    {
        $reach = (int) ceil(2 * $scale) + 2;
        $top = max(0, $roughTop - $reach);

        while ($top < $roughTop + $reach && ! $empty($x, $top)) {
            $top++;
        }

        $bottom = min($height - 1, $roughBottom + $reach);

        while ($bottom > $roughBottom - $reach && ! $empty($x, $bottom)) {
            $bottom--;
        }

        return [$top, $bottom + 1];
    }

    /**
     * The outermost empty pixel of a row near a rough end of the cut, going in $direction (-1 left, 1 right).
     *
     * @param  \Closure(int, int): bool  $empty
     */
    private function refineEdge(\Closure $empty, int $roughX, int $y, int $direction, float $scale, int $width): int
    {
        $reach = (int) ceil(2 * $scale) + 2;
        $x = max(0, min($width - 1, $roughX + $direction * $reach));

        while ($x !== $roughX - $direction * $reach && ! $empty($x, $y)) {
            $x -= $direction;
        }

        return $x;
    }

    /** See-through, or a light neutral gray like the squares of a painted transparency checkerboard. */
    private function isEmpty(\GdImage $image, int $x, int $y): bool
    {
        $color = imagecolorat($image, $x, $y);

        if ((($color >> 24) & 127) >= 64) {
            return true;
        }

        [$r, $g, $b] = [($color >> 16) & 255, ($color >> 8) & 255, $color & 255];

        return max($r, $g, $b) - min($r, $g, $b) <= 6 && min($r, $g, $b) >= 190;
    }
}
