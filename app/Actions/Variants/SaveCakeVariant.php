<?php

namespace App\Actions\Variants;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Support\SvgSanitizer;
use Illuminate\Support\Facades\DB;

class SaveCakeVariant
{
    public function __construct(private SvgSanitizer $sanitizer) {}

    /**
     * Saves the view box, the cleaned base SVG and the slot shapes.
     * A published variant that no longer passes the publish rule is unpublished, so the
     * design page never shows a broken drawing. Returns true when that happened.
     *
     * @param  array<mixed>  $slots  slots[layer_no][type] => [mode, d] or [mode, x, y, width, height, radius]
     */
    public function handle(CakeVariant $variant, int $width, int $height, ?string $baseSvg, array $slots): bool
    {
        return DB::transaction(function () use ($variant, $width, $height, $baseSvg, $slots) {
            $variant->update([
                'view_box_width' => $width,
                'view_box_height' => $height,
                'base_svg' => $this->sanitizer->sanitize($baseSvg ?? '') ?: null,
            ]);

            // Slots for layers the variant doesn't have can't be edited here, so drop them.
            $variant->slots()->where('layer_no', '>', $variant->layers)->delete();

            foreach (range(1, $variant->layers) as $layerNo) {
                foreach (CakeVariantSlot::TYPES as $type) {
                    $path = $this->path($slots[$layerNo][$type] ?? []);
                    $where = ['layer_no' => $layerNo, 'type' => $type];

                    if ($path === '') {
                        $variant->slots()->where($where)->delete();
                    } else {
                        $variant->slots()->updateOrCreate($where, ['svg_path' => $path]);
                    }
                }
            }

            $variant->load('slots');

            if ($variant->is_published && ! $variant->canPublish()) {
                $variant->update(['is_published' => false]);

                return true;
            }

            return false;
        });
    }

    private function path(mixed $slot): string
    {
        if (! is_array($slot)) {
            return '';
        }

        if (($slot['mode'] ?? 'path') === 'rect') {
            return CakeVariantSlot::roundedRectPath(
                (float) $slot['x'], (float) $slot['y'], (float) $slot['width'], (float) $slot['height'], (float) ($slot['radius'] ?? 0),
            );
        }

        return trim((string) ($slot['d'] ?? ''));
    }
}
