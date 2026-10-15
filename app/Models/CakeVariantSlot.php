<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CakeVariantSlot extends Model
{
    public const TYPES = ['sponge', 'cream'];

    protected $fillable = [
        'cake_variant_id',
        'layer_no',
        'type',
        'svg_path',
    ];

    protected $casts = [
        'layer_no' => 'integer',
    ];

    /** @return BelongsTo<CakeVariant, $this> */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(CakeVariant::class, 'cake_variant_id');
    }

    /**
     * The path for a rectangle with rounded corners, e.g. for a slot entered as x, y, width, height.
     * The radius is capped at half the shorter side, which turns the ends into half circles.
     */
    public static function roundedRectPath(float $x, float $y, float $width, float $height, float $radius = 0): string
    {
        $r = max(0, min($radius, $width / 2, $height / 2));
        $n = fn (float $value): string => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        if ($r == 0) {
            return "M{$n($x)} {$n($y)}H{$n($x + $width)}V{$n($y + $height)}H{$n($x)}Z";
        }

        return "M{$n($x + $r)} {$n($y)}"
            ."H{$n($x + $width - $r)}A{$n($r)} {$n($r)} 0 0 1 {$n($x + $width)} {$n($y + $r)}"
            ."V{$n($y + $height - $r)}A{$n($r)} {$n($r)} 0 0 1 {$n($x + $width - $r)} {$n($y + $height)}"
            ."H{$n($x + $r)}A{$n($r)} {$n($r)} 0 0 1 {$n($x)} {$n($y + $height - $r)}"
            ."V{$n($y + $r)}A{$n($r)} {$n($r)} 0 0 1 {$n($x + $r)} {$n($y)}Z";
    }

    /**
     * The sponge and cream bands that fill a cut face marked on a photo, keyed by layer (1 = bottom).
     * The face runs from its top edge to its bottom edge (three points each: left, middle, right), and every
     * band follows those edges, so a wedge cut gives V-shaped bands. Each layer is a sponge with its cream on
     * top; the sponge takes `sponge_share` of the layer's height. Bands overlap a hair so no seam shows.
     * Same geometry as fitBands() in resources/views/admin/cake-variants/edit.blade.php.
     *
     * @param  array{top: list<array{0: float|int, 1: float|int}>, bottom: list<array{0: float|int, 1: float|int}>, sponge_share: float|int}  $cut
     * @return array<int, array{sponge: string, cream: string}>
     */
    public static function cutFacePaths(array $cut, int $layers): array
    {
        $n = fn (float $value): string => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        $share = max(0.05, min(0.95, (float) $cut['sponge_share']));
        $overlap = 0.002;

        // The points across the face at a depth from 0 (top edge) to 1 (bottom edge).
        $at = fn (float $depth): array => array_map(
            fn (array $top, array $bottom) => [$top[0] + $depth * ($bottom[0] - $top[0]), $top[1] + $depth * ($bottom[1] - $top[1])],
            $cut['top'], $cut['bottom'],
        );

        $band = function (float $from, float $to) use ($at, $n, $overlap): string {
            $points = [...$at(max(0, $from - $overlap)), ...array_reverse($at(min(1, $to + $overlap)))];

            return 'M'.implode('L', array_map(fn (array $p) => $n($p[0]).' '.$n($p[1]), $points)).'Z';
        };

        $paths = [];

        foreach (range(1, $layers) as $layerNo) {
            $top = 1 - $layerNo / $layers;
            $creamEnd = $top + (1 - $share) / $layers;
            $paths[$layerNo] = [
                'sponge' => $band($creamEnd, 1 - ($layerNo - 1) / $layers),
                'cream' => $band($top, $creamEnd),
            ];
        }

        return $paths;
    }

    /**
     * The six corners of a band made by cutFacePaths (top edge left, middle, right, then bottom edge right,
     * middle, left), or null for any other shape.
     *
     * @return list<array{0: float, 1: float}>|null
     */
    public function bandPoints(): ?array
    {
        $number = '(-?\d+(?:\.\d+)?)';

        if (preg_match('/^M'.$number.' '.$number.str_repeat('L'.$number.' '.$number, 5).'Z$/', (string) $this->svg_path, $m) !== 1) {
            return null;
        }

        return array_map(fn (int $i) => [(float) $m[2 * $i + 1], (float) $m[2 * $i + 2]], range(0, 5));
    }

    /** "Layer 2 cream" */
    public function label(): string
    {
        return "Layer {$this->layer_no} {$this->type}";
    }
}
