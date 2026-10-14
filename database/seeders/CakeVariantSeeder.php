<?php

namespace Database\Seeders;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 2-, 3- and 4-layer cakes drawn with the same proportions as the design page's default drawing
 * (resources/views/cake-studio/index.blade.php), at 10 units per cqw: the sponge is 660 wide and
 * 140 tall with 22 rounded corners, the cream 700 wide and 56 tall with round ends, 20 wider on each side.
 */
class CakeVariantSeeder extends Seeder
{
    private const CREAM_WIDTH = 700;

    private const CREAM_HEIGHT = 56;

    private const SPONGE_INSET = 20;

    private const SPONGE_HEIGHT = 140;

    private const SPONGE_RADIUS = 22;

    public function run(): void
    {
        foreach (CakeVariant::LAYER_COUNTS as $layers) {
            DB::transaction(fn () => $this->seedVariant($layers));
        }
    }

    private function seedVariant(int $layers): void
    {
        $layerHeight = self::SPONGE_HEIGHT + self::CREAM_HEIGHT;
        $height = $layers * $layerHeight;

        // Never overwrite a drawing the admin may have edited: only create missing variants.
        if (CakeVariant::where('layers', $layers)->exists()) {
            return;
        }

        $variant = CakeVariant::create([
            'layers' => $layers,
            'view_box_width' => self::CREAM_WIDTH,
            'view_box_height' => $height,
            'is_published' => true,
        ]);

        // Layer 1 sits at the bottom; each layer is a sponge with its cream on top.
        foreach (range(1, $layers) as $layerNo) {
            $bottom = $height - ($layerNo - 1) * $layerHeight;

            $shapes = [
                'sponge' => CakeVariantSlot::roundedRectPath(
                    self::SPONGE_INSET, $bottom - self::SPONGE_HEIGHT,
                    self::CREAM_WIDTH - 2 * self::SPONGE_INSET, self::SPONGE_HEIGHT, self::SPONGE_RADIUS,
                ),
                'cream' => CakeVariantSlot::roundedRectPath(
                    0, $bottom - $layerHeight, self::CREAM_WIDTH, self::CREAM_HEIGHT, self::CREAM_HEIGHT / 2,
                ),
            ];

            foreach ($shapes as $type => $path) {
                $variant->slots()->create(['layer_no' => $layerNo, 'type' => $type, 'svg_path' => $path]);
            }
        }
    }
}
