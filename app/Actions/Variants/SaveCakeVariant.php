<?php

namespace App\Actions\Variants;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Services\SvgSanitizer;
use Illuminate\Support\Facades\DB;

class SaveCakeVariant
{
    public function __construct(private SvgSanitizer $sanitizer) {}

    /**
     * Saves the drawing and its slots. A published variant that is no longer valid is unpublished,
     * so customers never see a broken cake.
     *
     * @param  array{view_box_width: int|string, view_box_height: int|string, base_svg?: string|null, slots: array<int|string, array<string, array<string, mixed>>>}  $input
     * @return bool whether the variant had to be unpublished
     */
    public function handle(CakeVariant $variant, array $input): bool
    {
        return DB::transaction(function () use ($variant, $input) {
            $variant->update([
                'view_box_width' => (int) $input['view_box_width'],
                'view_box_height' => (int) $input['view_box_height'],
                'base_svg' => $this->sanitizer->sanitize((string) ($input['base_svg'] ?? '')) ?: null,
            ]);

            // Slots beyond the layer count can't be edited here; drop them.
            $variant->slots()->where('layer_no', '>', $variant->layers)->delete();

            foreach (range(1, $variant->layers) as $layerNo) {
                foreach (CakeVariantSlot::TYPES as $type) {
                    $path = $this->pathFor($input['slots'][$layerNo][$type] ?? []);
                    $match = ['layer_no' => $layerNo, 'type' => $type];

                    if ($path === '') {
                        $variant->slots()->where($match)->delete();
                    } else {
                        $variant->slots()->updateOrCreate($match, ['svg_path' => $path]);
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

    /** @param  array<string, mixed>  $slot */
    private function pathFor(array $slot): string
    {
        if (($slot['mode'] ?? 'path') === 'rect') {
            return CakeVariantSlot::roundedRectPath(
                (float) $slot['x'], (float) $slot['y'], (float) $slot['width'], (float) $slot['height'], (float) ($slot['radius'] ?? 0),
            );
        }

        return $this->sanitizer->normalizePath((string) ($slot['path'] ?? ''));
    }
}
