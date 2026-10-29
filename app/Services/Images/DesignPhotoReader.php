<?php

namespace App\Services\Images;

use App\Models\CakeDesign;
use App\Services\PublicUploads;
use GdImage;

/**
 * Reads a cake design photo (a cake on a stand, on a transparent or plain background) the way the bundled designs
 * were fitted by hand, so an uploaded design turns in 3D like them: where the cake sits (CakeDesign::$fit) and the
 * toppings standing up from its top, cut out of the photo (CakeDesign::$toppings).
 */
class DesignPhotoReader
{
    /** Photos are read at most this wide or tall (the shares come out the same); toppings are cut from the original. */
    private const READ_SIZE = 640;

    /** Larger photos are not read: decoding them could exhaust PHP's memory. */
    private const MAX_PIXELS = 4096 * 4096;

    /** The curve of the cake's top and bottom edge per curve of the stand's plate (measured on the bundled photos). */
    private const TOP_CURVE = 0.52;

    private const BOTTOM_CURVE = 0.67;

    /** Height per width of the plate's ellipse when the photo has no stand to measure it on. */
    private const PLATE_ASPECT = 0.32;

    /**
     * A pixel on the top is a topping when its tint (red − green and green − blue) differs from the top's by more
     * than TINT, or its lightness by more than LIGHTNESS: shine, shade and a glaze's reflections change only how
     * light the top looks (they stay on the top), a topping's own color changes its tint, or is far lighter or darker.
     */
    private const TOPPING_TINT = 20;

    private const TOPPING_LIGHTNESS = 110;

    /** At most this many toppings are cut out, largest first. */
    private const MAX_TOPPINGS = 4;

    /** How much of a cut-out the 3D cake sinks into the top (TOPPING_SINK in resources/js/cake-3d/viewer.js). */
    private const TOPPING_SINK = 0.08;

    public function __construct(private PublicUploads $uploads) {}

    /**
     * Fits the design to its cover photo and replaces its toppings with the ones cut out of it. Returns false (and
     * changes nothing) when the photo can't be read.
     */
    public function fitDesign(CakeDesign $design): bool
    {
        $cover = $this->uploads->file($design->frames()->first()?->image);
        $reading = $cover ? $this->read($cover) : null;

        if (! $reading) {
            return false;
        }

        $old = [...array_column($design->toppingList(), 'image'), $design->top_image];
        $design->update([
            'fit' => $reading['fit'],
            'toppings' => array_map(fn (array $topping) => [
                'image' => $this->uploads->storeContents($topping['webp'], 'cake-designs', 'webp'),
                ...array_diff_key($topping, ['webp' => true]),
            ], $reading['toppings']),
            'top_image' => $reading['top'] !== null ? $this->uploads->storeContents($reading['top'], 'cake-designs', 'webp') : null,
        ]);
        array_walk($old, fn (?string $image) => $this->uploads->delete($image));

        return true;
    }

    /**
     * The fit, the topping cut-outs (WebP contents, placed as CakeDesign::toppingList() places them) and the photo
     * with them painted out of the top (WebP contents, null without toppings), or null when the photo can't be read
     * (not an image, too large, or no cake standing out from its background).
     *
     * @return array{fit: array<string, float>, toppings: list<array{webp: string, count: int, ring: float, size: float, angle: float}>, top: ?string}|null
     */
    public function read(string $file): ?array
    {
        $info = @getimagesize($file);
        if ($info === false || $info[0] * $info[1] > self::MAX_PIXELS || ! function_exists('imagewebp')) {
            return null;
        }

        $photo = @imagecreatefromstring((string) file_get_contents($file));
        if ($photo === false) {
            return null;
        }
        imagepalettetotruecolor($photo);

        $scale = min(1, self::READ_SIZE / max(imagesx($photo), imagesy($photo)));
        $small = $scale < 1 ? imagescale($photo, max(1, (int) round(imagesx($photo) * $scale)), max(1, (int) round(imagesy($photo) * $scale))) : $photo;
        if ($small === false) {
            return null;
        }
        $scale = imagesx($small) / imagesx($photo);

        $cake = $this->cake($small);
        $fit = $cake ? $this->fit($small, $cake) : null;

        if (! $cake || ! $fit) {
            return null;
        }

        ['toppings' => $toppings, 'pixels' => $pixels] = $this->toppings($photo, $small, $scale, $cake, $fit['pixels']);

        return [
            'fit' => $fit['shares'],
            'toppings' => $toppings,
            'top' => $pixels ? $this->cleanTop($photo, $scale, $pixels, $cake['width'], $fit['pixels']) : null,
        ];
    }

    /**
     * The photo with the toppings painted out of the top's ellipse, for the 3D cake's top (the cut-outs stand on it
     * instead, so nothing is left behind or doubled as it turns): each cut pixel is blended from the nearest kept
     * pixels left and right and above and below it, the nearer counting more. WebP contents, or null.
     *
     * @param  array<int, bool>  $pixels  the cut-out pixels in the read (small) photo
     * @param  array{x: float, r: float, center: float, curve: float}  $fit  in the read photo
     */
    private function cleanTop(GdImage $photo, float $scale, array $pixels, int $smallWidth, array $fit): ?string
    {
        $width = imagesx($photo);
        $height = imagesy($photo);
        [$cx, $r, $cy, $b] = [$fit['x'] / $scale, $fit['r'] / $scale, $fit['center'] / $scale, $fit['curve'] / $scale];

        // Cut pixels of the full photo on the top, with a pixel of the read photo round them (soft edges).
        $cut = function (int $x, int $y) use ($pixels, $scale, $smallWidth, $cx, $cy, $r, $b): bool {
            if ((($x - $cx) / $r) ** 2 + (($y - $cy) / $b) ** 2 > 1) {
                return false;
            }
            $sx = (int) ($x * $scale);
            $sy = (int) ($y * $scale);
            foreach ([[0, 0], [1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                if (isset($pixels[($sy + $dy) * $smallWidth + $sx + $dx])) {
                    return true;
                }
            }

            return false;
        };

        $holes = [];
        for ($y = max(0, (int) floor($cy - $b)); $y <= min($height - 1, $cy + $b); $y++) {
            for ($x = max(0, (int) floor($cx - $r)); $x <= min($width - 1, $cx + $r); $x++) {
                if ($cut($x, $y)) {
                    $holes[$y * $width + $x] = true;
                }
            }
        }
        if (! $holes) {
            return null;
        }

        // The nearest kept pixel along the row and the column, either way: [color, distance].
        $nearest = function (int $x, int $y, int $dx, int $dy) use ($photo, $holes, $width, $height, $cx, $cy, $r, $b): ?array {
            for ($step = 1; ; $step++) {
                $nx = $x + $dx * $step;
                $ny = $y + $dy * $step;
                if ($nx < 0 || $ny < 0 || $nx >= $width || $ny >= $height || (($nx - $cx) / $r) ** 2 + (($ny - $cy) / $b) ** 2 > 1) {
                    return null;
                }
                if (! isset($holes[$ny * $width + $nx])) {
                    return [$this->pixel($photo, $nx, $ny), $step];
                }
            }
        };

        $clean = imagecreatetruecolor($width, $height);
        if ($clean === false) {
            return null;
        }
        imagealphablending($clean, false);
        imagesavealpha($clean, true);
        imagecopy($clean, $photo, 0, 0, 0, 0, $width, $height);

        foreach (array_keys($holes) as $p) {
            $x = $p % $width;
            $y = intdiv($p, $width);
            $sum = [0.0, 0.0, 0.0];
            $weights = 0.0;
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                $found = $nearest($x, $y, $dx, $dy);
                if ($found) {
                    [$color, $distance] = $found;
                    // Rows of a top look alike; columns change from back to front, so they count for less.
                    $weight = ($dy === 0 ? 1.0 : 0.5) / $distance;
                    $sum[0] += (($color >> 16) & 255) * $weight;
                    $sum[1] += (($color >> 8) & 255) * $weight;
                    $sum[2] += ($color & 255) * $weight;
                    $weights += $weight;
                }
            }
            if ($weights > 0) {
                imagesetpixel($clean, $x, $y, ((int) round($sum[0] / $weights) << 16) | ((int) round($sum[1] / $weights) << 8) | (int) round($sum[2] / $weights));
            }
        }

        ob_start();
        $written = imagewebp($clean, null, 90);
        $webp = (string) ob_get_clean();
        imagedestroy($clean);

        return $written && $webp !== '' ? $webp : null;
    }

    /**
     * The cake (with its stand) as a mask: the largest piece that stands out from the background.
     *
     * @return array{width: int, height: int, mask: array<int, bool>, left: array<int, int>, right: array<int, int>}|null
     */
    private function cake(GdImage $image): ?array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $corners = [$this->pixel($image, 0, 0), $this->pixel($image, $width - 1, 0), $this->pixel($image, 0, $height - 1), $this->pixel($image, $width - 1, $height - 1)];
        $transparent = min(array_map(fn (int $c) => ($c >> 24) & 127, $corners)) > 100;
        $background = $corners[0];

        // A plain background must be the same in every corner, else the cake can't be told from it.
        if (! $transparent && max(array_map(fn (int $c) => $this->distance($c, $background), $corners)) > 30) {
            return null;
        }

        $solid = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = $this->pixel($image, $x, $y);
                $solid[$y * $width + $x] = $transparent ? (($color >> 24) & 127) < 64 : $this->distance($color, $background) > 48;
            }
        }

        $mask = $this->largestPiece($solid, $width, $height);
        if (count($mask) < 0.05 * $width * $height) {
            return null;
        }

        $left = array_fill(0, $height, -1);
        $right = array_fill(0, $height, -1);
        foreach (array_keys($mask) as $p) {
            $x = $p % $width;
            $y = intdiv($p, $width);
            $left[$y] = $left[$y] < 0 ? $x : min($left[$y], $x);
            $right[$y] = max($right[$y], $x);
        }

        return ['width' => $width, 'height' => $height, 'mask' => $mask, 'left' => $left, 'right' => $right];
    }

    /**
     * Where the cake sits: its side is the widest steady width above the stand's plate (the photo's widest row), the
     * plate's lower edge gives how much the ellipses curve and where the cake's bottom stands.
     *
     * @param  array{width: int, height: int, mask: array<int, bool>, left: array<int, int>, right: array<int, int>}  $cake
     * @return array{shares: array<string, float>, pixels: array{x: float, r: float, center: float, curve: float}}|null
     */
    private function fit(GdImage $image, array $cake): ?array
    {
        ['width' => $width, 'height' => $height, 'left' => $left, 'right' => $right] = $cake;
        $across = fn (int $y): int => $left[$y] < 0 ? 0 : $right[$y] - $left[$y] + 1;

        $widest = 0;
        for ($y = 0; $y < $height; $y++) {
            $widest = $across($y) > $across($widest) ? $y : $widest;
        }
        $plate = $across($widest);

        // The side: rows of (nearly) one width, the widest such run that is long enough and narrower than the plate.
        $tolerance = 0.012 * $width;
        $side = null;
        for ($y = 0; $y < $widest; $y++) {
            $w = $across($y);
            if ($w < 0.3 * $width || $w > 0.94 * $plate) {
                continue;
            }
            [$start, $end] = [$y, $y];
            while ($end + 1 < $widest && abs($across($end + 1) - $w) <= $tolerance) {
                $end++;
            }
            while ($start > 0 && abs($across($start - 1) - $w) <= $tolerance) {
                $start--;
            }
            $longer = $side && $end - $start > $side[1] - $side[0];
            if ($end - $start + 1 >= max(6, 0.03 * $height) && (! $side || $w > $side[2] + $tolerance || (abs($w - $side[2]) <= $tolerance && $longer))) {
                $side = [$start, $end, $w];
            }
        }
        if (! $side) {
            return null;
        }

        [$start, $end] = $side;
        $widths = [];
        $middles = [];
        for ($y = $start; $y <= $end; $y++) {
            $widths[] = $across($y);
            $middles[] = ($left[$y] + $right[$y]) / 2;
        }
        $r = $this->median($widths) / 2;
        $x = $this->median($middles);

        // Below its widest row the plate's outline is the front half of an ellipse: y = center + curve * √(1 − (w / plate)²).
        $points = [];
        for ($y = $widest; $y < $height && $plate > 2.16 * $r; $y++) {
            $q = $across($y) / $plate;
            if ($q < 0.3) {
                break;
            }
            if ($q <= 0.97) {
                $points[] = [sqrt(1 - $q * $q), $y];
            }
        }
        $line = $this->line($points);
        $aspect = $line && $line[1] > 0 ? min(0.6, $line[1] / ($plate / 2)) : self::PLATE_ASPECT;

        // The top's ellipse is widest where the side starts. Its back edge is the outline's top in each column, except
        // where a topping stands higher: so the curve is a low share of what the columns say (the cake may have been
        // photographed from higher up than the stand, so the plate only gives a floor).
        $highest = [];
        foreach (array_keys($cake['mask']) as $p) {
            $column = $p % $width;
            $highest[$column] = min($highest[$column] ?? $height, intdiv($p, $width));
        }
        $curves = [];
        for ($column = (int) ceil($x - 0.9 * $r); $column <= $x + 0.9 * $r; $column++) {
            $share = ($column - $x) / $r;
            if (abs($share) >= 0.25 && isset($highest[$column]) && $highest[$column] < $start) {
                $curves[] = ($start - $highest[$column]) / sqrt(1 - $share * $share);
            }
        }
        sort($curves);
        $topCurve = max(self::TOP_CURVE * $aspect * $r, $curves ? $curves[(int) (0.2 * count($curves))] : 0);
        $topCurve = min(0.6 * $r, $topCurve);
        $bottomCurve = $topCurve * self::BOTTOM_CURVE / self::TOP_CURVE;
        $top = $start + $topCurve;

        // The side's bottom straight in front: going down the middle from the top's front edge, where the stand's
        // plate shows (its color at its widest row, beside the cake); else level with the plate's middle.
        $bottom = ($line ? $line[0] : $end) + $bottomCurve;
        $plateColor = $plate > 2.16 * $r ? $this->pixel($image, (int) round($left[$widest] + 0.04 * $plate), $widest) : null;
        $run = 0;
        for ($y = (int) ceil($top + 0.05 * $height); $plateColor !== null && $y < $widest + 0.3 * $plate && $y < $height; $y++) {
            $run = $this->distance($this->pixel($image, (int) round($x), $y), $plateColor) < 35 ? $run + 1 : 0;
            if ($run === 3) {
                $bottom = $y - 2;
                break;
            }
        }

        $shares = [
            'x' => $x / $width,
            'r' => min(0.5, max(0.05, $r / $width)),
            'top' => $top / $height,
            'bottom' => max($top + 0.02 * $height, $bottom) / $height,
            'top_curve' => min(0.3, $topCurve / $height),
            'bottom_curve' => min(0.3, $bottomCurve / $height),
        ];

        return [
            'shares' => array_map(fn (float $v) => round(min(1, max(0, $v)), 3), $shares),
            'pixels' => ['x' => $x, 'r' => $r, 'center' => (float) $start, 'curve' => $topCurve],
        ];
    }

    /**
     * Toppings: what stands up above the back of the top's ellipse, with what lies on the top in another color than
     * the top's, joined to it. Each group of them is cut out of the full photo and stood where its foot is.
     *
     * @param  array{width: int, height: int, mask: array<int, bool>, left: array<int, int>, right: array<int, int>}  $cake
     * @param  array{x: float, r: float, center: float, curve: float}  $fit
     * @return array{toppings: list<array{webp: string, count: int, ring: float, size: float, angle: float}>, pixels: array<int, bool>}
     */
    private function toppings(GdImage $photo, GdImage $small, float $scale, array $cake, array $fit): array
    {
        ['width' => $width, 'height' => $height, 'mask' => $mask] = $cake;
        ['x' => $cx, 'r' => $r, 'center' => $cy, 'curve' => $b] = $fit;
        if ($b < 1) {
            return ['toppings' => [], 'pixels' => []];
        }

        $onTop = fn (int $x, int $y, float $share = 1.0): bool => (($x - $cx) / ($r * $share)) ** 2 + (($y - $cy) / ($b * $share)) ** 2 <= 1;
        // Above the back of the top, in a column where something rises well above it (not just a rounded rim).
        $highest = [];
        foreach (array_keys($mask) as $p) {
            $highest[$p % $width] = min($highest[$p % $width] ?? $height, intdiv($p, $width));
        }
        $standsUp = function (int $x, int $y) use ($cx, $cy, $r, $b, $highest): bool {
            $across = ($x - $cx) / $r;
            $rim = abs($across) < 1 ? $cy - $b * sqrt(1 - $across * $across) : null;

            return $rim !== null && ($highest[$x] ?? $y) < $rim - max(3, 0.3 * $b) && $y < $rim - max(1, 0.1 * $b);
        };
        // On the top or above it (not the side).
        $overTop = function (int $x, int $y) use ($cx, $cy, $r, $b): bool {
            $across = ($x - $cx) / $r;

            return abs($across) < 1 && $y <= $cy + $b * sqrt(1 - $across * $across);
        };

        // The top's own color: the middle of what its ellipse shows (toppings cover less than half of it).
        $all = [[], [], []];
        for ($y = max(0, (int) ($cy - $b)); $y <= min($height - 1, $cy + $b); $y++) {
            for ($x = max(0, (int) ($cx - $r)); $x <= min($width - 1, $cx + $r); $x++) {
                if ($onTop($x, $y, 0.95)) {
                    $color = $this->pixel($small, $x, $y);
                    foreach ([16, 8, 0] as $c => $shift) {
                        $all[$c][] = ($color >> $shift) & 255;
                    }
                }
            }
        }
        if (! $all[0]) {
            return ['toppings' => [], 'pixels' => []];
        }
        $topColor = $this->medianColor($all);
        $isTop = fn (int $x, int $y): bool => ! $this->differsFromTop($this->pixel($small, $x, $y), $topColor);

        // Grow from what stands up into touching pixels on the top that aren't the top's color (the part of a
        // topping lying on the top, and where it meets it).
        $reach = max(3, (int) round($b));
        $topping = [];
        $queue = [];
        $standing = [];
        foreach (array_keys($mask) as $p) {
            $x = $p % $width;
            $y = intdiv($p, $width);
            if ($standsUp($x, $y)) {
                $topping[$p] = 0;
                $queue[] = $p;
                $standing[$p] = true;
            } elseif ($onTop($x, $y) && $this->differsFromTop($this->pixel($small, $x, $y), $topColor, 3)) {
                // A vivid color on the top (a cherry below its stalk) starts a topping too, kept only if it is one
                // piece with something standing up (writing or a pattern on the top stays on the top).
                $topping[$p] = 0;
                $queue[] = $p;
            }
        }
        for ($i = 0; $i < count($queue); $i++) {
            $p = $queue[$i];
            if ($topping[$p] >= $reach) {
                continue;
            }
            foreach ($this->neighbours($p, $width, $height) as $q) {
                if (! isset($topping[$q]) && isset($mask[$q]) && $overTop($q % $width, intdiv($q, $width)) && ! $isTop($q % $width, intdiv($q, $width))) {
                    $topping[$q] = $topping[$p] + 1;
                    $queue[] = $q;
                }
            }
        }

        // Thin lines that grew (a shine along the rim, the edge of a reflection) are opened away: what grew is worn
        // down and built back up by a pixel or two, and only what still touches a standing topping is kept.
        $seeds = array_filter($topping, fn (int $depth) => $depth === 0);
        $grown = array_diff_key($topping, $seeds);
        $steps = max(1, (int) round(0.004 * $width));
        $opened = $grown;
        for ($step = 0; $step < $steps; $step++) {
            $opened = array_filter($opened, function (int $depth, int $p) use ($opened, $seeds, $width, $height) {
                foreach ($this->neighbours($p, $width, $height) as $q) {
                    if (! isset($opened[$q]) && ! isset($seeds[$q])) {
                        return false;
                    }
                }

                return true;
            }, ARRAY_FILTER_USE_BOTH);
        }
        for ($step = 0; $step < $steps; $step++) {
            foreach (array_keys($opened) as $p) {
                foreach ($this->neighbours($p, $width, $height) as $q) {
                    if (isset($grown[$q])) {
                        $opened[$q] = 1;
                    }
                }
            }
        }
        // What grew right next to a standing topping stays (a thin stalk may join a cherry), and each opened piece
        // reaching that far.
        $near = $seeds;
        for ($step = 0; $step <= $steps; $step++) {
            foreach (array_keys($near) as $p) {
                foreach ($this->neighbours($p, $width, $height) as $q) {
                    if (isset($grown[$q])) {
                        $near[$q] = 1;
                    }
                }
            }
        }
        $topping = $near;
        $queue = array_keys(array_intersect_key($opened, $near));
        $reached = array_fill_keys($queue, true);
        for ($i = 0; $i < count($queue); $i++) {
            foreach ($this->neighbours($queue[$i], $width, $height) as $q) {
                if (isset($opened[$q]) && ! isset($reached[$q])) {
                    $topping[$q] = 1;
                    $reached[$q] = true;
                    $queue[] = $q;
                }
            }
        }

        // Pieces close together are one topping (a cherry and its stalk): group them on a coarse grid.
        $cell = max(2, (int) round(0.02 * $width));
        $cells = [];
        foreach (array_keys($topping) as $p) {
            $cells[intdiv($p % $width, $cell).','.intdiv(intdiv($p, $width), $cell)][] = $p;
        }
        $groups = [];
        $seen = [];
        foreach (array_keys($cells) as $start) {
            if (isset($seen[$start])) {
                continue;
            }
            $seen[$start] = true;
            $queue = [$start];
            $pixels = [];
            while ($queue) {
                $key = array_pop($queue);
                array_push($pixels, ...$cells[$key]);
                [$gx, $gy] = array_map(intval(...), explode(',', $key));
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        $next = ($gx + $dx).','.($gy + $dy);
                        if (isset($cells[$next]) && ! isset($seen[$next])) {
                            $seen[$next] = true;
                            $queue[] = $next;
                        }
                    }
                }
            }
            $groups[] = $pixels;
        }
        usort($groups, fn (array $one, array $other) => count($other) <=> count($one));

        $toppings = [];
        $kept = [];
        foreach ($groups as $pixels) {
            $xs = array_map(fn (int $p) => $p % $width, $pixels);
            $ys = array_map(fn (int $p) => intdiv($p, $width), $pixels);
            [$left, $right, $top, $bottom] = [min($xs), max($xs), min($ys), max($ys)];

            // Specks are noise, and a band right round the rim (piping) is part of the cake's look, not a topping.
            $stands = false;
            foreach ($pixels as $p) {
                if (isset($standing[$p])) {
                    $stands = true;
                    break;
                }
            }
            if (! $stands || count($pixels) < 0.0015 * $width * $height || $right - $left > 1.6 * $r || count($toppings) === self::MAX_TOPPINGS) {
                continue;
            }

            // Its foot: the middle of its lowest rows, as a point on the top (z towards the camera). The 3D cake sinks
            // the bottom TOPPING_SINK of a cut-out into the top, so the foot is that far up: seen from the front, the
            // cut-out then lands exactly where it is in the photo.
            $footX = [];
            foreach ($pixels as $i => $p) {
                if ($ys[$i] >= $bottom - max(1, 0.1 * ($bottom - $top))) {
                    $footX[] = $xs[$i];
                }
            }
            $foot = $bottom + 1 - self::TOPPING_SINK * ($bottom - $top + 1);
            $across = max(-0.95, min(0.95, (array_sum($footX) / count($footX) - $cx) / $r));
            $toward = max(-1, min(1, ($foot - $cy) / $b));
            $ring = hypot($across, $toward);
            if ($ring > 0.95) {
                // Beyond the rim (it stands on the back edge): pulled in onto the top, keeping its direction.
                [$across, $toward, $ring] = [$across * 0.95 / $ring, $toward * 0.95 / $ring, 0.95];
            }

            $webp = $this->cutOut($photo, $scale, array_fill_keys($pixels, true), $width, [$left, $right, $top, $bottom]);
            if ($webp === null) {
                continue;
            }

            $kept += array_fill_keys($pixels, true);
            $toppings[] = [
                'webp' => $webp,
                'count' => 1,
                'ring' => round($ring, 2),
                'size' => round(min(1, max(0.05, ($right - $left + 1) / $r)), 2),
                'angle' => $ring < 0.05 ? 0.0 : round(fmod(rad2deg(atan2($across, $toward)) + 360, 360), 1),
            ];
        }

        return ['toppings' => $toppings, 'pixels' => $kept];
    }

    /**
     * A group's pixels cut out of the full photo (transparent round them) as WebP.
     *
     * @param  array<int, bool>  $pixels  pixel indexes in the read (small) photo
     * @param  array{0: int, 1: int, 2: int, 3: int}  $box  left, right, top, bottom in the read photo
     */
    private function cutOut(GdImage $photo, float $scale, array $pixels, int $smallWidth, array $box): ?string
    {
        [$left, $right, $top, $bottom] = $box;
        $x0 = (int) floor($left / $scale);
        $y0 = (int) floor($top / $scale);
        $x1 = min(imagesx($photo) - 1, (int) ceil(($right + 1) / $scale) - 1);
        $y1 = min(imagesy($photo) - 1, (int) ceil(($bottom + 1) / $scale) - 1);

        $cut = imagecreatetruecolor(max(1, $x1 - $x0 + 1), max(1, $y1 - $y0 + 1));
        if ($cut === false) {
            return null;
        }
        imagealphablending($cut, false);
        imagesavealpha($cut, true);
        imagefill($cut, 0, 0, 127 << 24);

        for ($y = $y0; $y <= $y1; $y++) {
            for ($x = $x0; $x <= $x1; $x++) {
                if (isset($pixels[(int) ($y * $scale) * $smallWidth + (int) ($x * $scale)])) {
                    imagesetpixel($cut, $x - $x0, $y - $y0, $this->pixel($photo, $x, $y));
                }
            }
        }

        ob_start();
        $written = imagewebp($cut, null, 92);
        $webp = (string) ob_get_clean();
        imagedestroy($cut);

        return $written && $webp !== '' ? $webp : null;
    }

    /**
     * The largest 4-connected piece of solid pixels.
     *
     * @param  array<int, bool>  $solid
     * @return array<int, bool> its pixel indexes
     */
    private function largestPiece(array $solid, int $width, int $height): array
    {
        $seen = [];
        $largest = [];
        foreach ($solid as $start => $isSolid) {
            if (! $isSolid || isset($seen[$start])) {
                continue;
            }
            $seen[$start] = true;
            $stack = [$start];
            $piece = [];
            while ($stack) {
                $p = array_pop($stack);
                $piece[$p] = true;
                $x = $p % $width;
                foreach ([$x > 0 ? $p - 1 : -1, $x < $width - 1 ? $p + 1 : -1, $p - $width, $p + $width] as $q) {
                    if ($q >= 0 && $q < $width * $height && $solid[$q] && ! isset($seen[$q])) {
                        $seen[$q] = true;
                        $stack[] = $q;
                    }
                }
            }
            if (count($piece) > count($largest)) {
                $largest = $piece;
            }
        }

        return $largest;
    }

    /**
     * The least-squares line y = a + b·u through [u, y] points, dropping points far off it; null with too few.
     *
     * @param  list<array{0: float, 1: int}>  $points
     * @return array{0: float, 1: float}|null
     */
    private function line(array $points): ?array
    {
        $fit = null;
        for ($pass = 0; $pass < 3 && count($points) >= 3; $pass++) {
            $n = count($points);
            $su = $sy = $suu = $suy = 0.0;
            foreach ($points as [$u, $y]) {
                $su += $u;
                $sy += $y;
                $suu += $u * $u;
                $suy += $u * $y;
            }
            $d = $n * $suu - $su * $su;
            if (abs($d) < 1e-9) {
                break;
            }
            $slope = ($n * $suy - $su * $sy) / $d;
            $fit = [($sy - $slope * $su) / $n, $slope];

            $misses = array_map(fn (array $p) => abs($p[1] - ($fit[0] + $fit[1] * $p[0])), $points);
            $limit = max(1.5, 2.5 * $this->median($misses));
            $points = array_values(array_filter($points, fn (array $p, int $i) => $misses[$i] <= $limit, ARRAY_FILTER_USE_BOTH));
        }

        return $fit;
    }

    /** @param  array<int, int|float>  $values */
    private function median(array $values): float
    {
        sort($values);

        return (float) $values[intdiv(count($values), 2)];
    }

    /** How far apart two colors are: red + green + blue differences. */
    private function distance(int $a, int $b): int
    {
        return abs((($a >> 16) & 255) - (($b >> 16) & 255)) + abs((($a >> 8) & 255) - (($b >> 8) & 255)) + abs(($a & 255) - ($b & 255));
    }

    /** Whether a color on the top belongs to a topping rather than the top (see TOPPING_TINT), or `times` as clearly. */
    private function differsFromTop(int $color, int $top, float $times = 1): bool
    {
        [$r, $g, $b] = [($color >> 16) & 255, ($color >> 8) & 255, $color & 255];
        [$tr, $tg, $tb] = [($top >> 16) & 255, ($top >> 8) & 255, $top & 255];
        $tint = abs(($r - $g) - ($tr - $tg)) + abs(($g - $b) - ($tg - $tb));

        return $tint > $times * self::TOPPING_TINT || abs(($r + $g + $b) - ($tr + $tg + $tb)) / 3 > min(200, $times * self::TOPPING_LIGHTNESS);
    }

    /**
     * The middle of each channel, as one color.
     *
     * @param  array{0: list<int>, 1: list<int>, 2: list<int>}  $channels  red, green and blue values
     */
    private function medianColor(array $channels): int
    {
        return ((int) $this->median($channels[0]) << 16) | ((int) $this->median($channels[1]) << 8) | (int) $this->median($channels[2]);
    }

    /**
     * The 8 pixels round a pixel index that are inside the image.
     *
     * @return list<int>
     */
    private function neighbours(int $p, int $width, int $height): array
    {
        $x = $p % $width;
        $y = intdiv($p, $width);
        $around = [];
        for ($dy = -1; $dy <= 1; $dy++) {
            for ($dx = -1; $dx <= 1; $dx++) {
                if (($dx || $dy) && $x + $dx >= 0 && $y + $dy >= 0 && $x + $dx < $width && $y + $dy < $height) {
                    $around[] = ($y + $dy) * $width + $x + $dx;
                }
            }
        }

        return $around;
    }

    /** A pixel's color with its alpha (0 = opaque … 127 = clear); clear black outside the image. */
    private function pixel(GdImage $image, int $x, int $y): int
    {
        $color = @imagecolorat($image, $x, $y);

        return $color === false ? 127 << 24 : $color;
    }
}
