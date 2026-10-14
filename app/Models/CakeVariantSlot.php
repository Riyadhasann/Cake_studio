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

    /** "Layer 2 cream" */
    public function label(): string
    {
        return "Layer {$this->layer_no} {$this->type}";
    }
}
