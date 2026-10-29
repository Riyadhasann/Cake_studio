<?php

namespace App\Actions\Variants;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Services\Images\SvgSanitizer;
use Illuminate\Support\Facades\DB;

class SaveCakeVariant
{
    public function __construct(private SvgSanitizer $sanitizer) {}

    /**
     * Saves the drawing and its slots. A published variant that is no longer valid is unpublished,
     * so customers never see a broken cake.
     *
     * @param  array{view_box_width: int|string, view_box_height: int|string, base_svg?: string|null, cut_face?: array<string, mixed>|null, slots: array<int|string, array<string, array<string, mixed>>>}  $input
     * @return bool whether the variant had to be unpublished
     */
    public function handle(CakeVariant $variant, array $input): bool
    {
        return DB::transaction(function () use ($variant, $input) {
            // With a cake photo the view box is the photo's pixel size and the cut face is kept; without one, neither applies.
            $hasPhoto = (bool) $variant->base_image;

            $variant->update([
                ...($hasPhoto ? [] : [
                    'view_box_width' => (int) $input['view_box_width'],
                    'view_box_height' => (int) $input['view_box_height'],
                ]),
                'base_svg' => $this->sanitizer->sanitize((string) ($input['base_svg'] ?? '')) ?: null,
                'cut_face' => $hasPhoto ? $this->cutFace($input['cut_face'] ?? null) : null,
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

    /**
     * The validated cut face as plain numbers, or null when none was marked.
     *
     * @param  array<string, mixed>|null  $cut
     * @return array{top: list<array{0: float, 1: float}>, bottom: list<array{0: float, 1: float}>, sponge_share: float}|null
     */
    private function cutFace(?array $cut): ?array
    {
        if (! $cut) {
            return null;
        }

        $points = fn (mixed $edge): array => array_map(
            fn (mixed $point) => [(float) $point[0], (float) $point[1]],
            array_values((array) $edge),
        );

        return ['top' => $points($cut['top']), 'bottom' => $points($cut['bottom']), 'sponge_share' => (float) $cut['sponge_share']];
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
