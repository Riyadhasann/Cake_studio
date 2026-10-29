<?php

namespace App\Services;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Models\CreamFlavor;
use App\Models\FlavorVariantImage;
use App\Models\SpongeFlavor;
use App\Services\Images\SvgSanitizer;
use Illuminate\Database\Eloquent\Collection;

/** The cake drawings (variants) as plain arrays for the pages that draw them. */
class CakeDrawings
{
    public function __construct(private SvgSanitizer $sanitizer, private PublicUploads $uploads) {}

    /**
     * The drawing for each layer count that has a published, complete one, keyed by layer count,
     * with its shading image and the photos of the given (active) flavors. A layer count missing here
     * keeps the layered preview.
     *
     * @param  Collection<int, SpongeFlavor>  $sponges
     * @param  Collection<int, CreamFlavor>  $creams
     * @return array<int, array{width: int, height: int, base_svg: string, base_image: array{url: string, width: int, height: int}|null, slots: array<int, array{layer_no: int, type: string, path: string, band: list<array{0: float, 1: float}>|null}>, shading: array{url: string, width: int, height: int}|null, photos: array<string, array{id: int, url: string, width: int, height: int}>}>
     */
    public function published(Collection $sponges, Collection $creams): array
    {
        $flavors = ['sponge' => $sponges, 'cream' => $creams];

        return CakeVariant::published()
            ->with(['slots', 'flavorImages'])
            ->get()
            ->filter(fn (CakeVariant $variant) => $variant->canPublish())
            ->mapWithKeys(fn (CakeVariant $variant) => [$variant->layers => [
                ...$this->drawing($variant),
                'shading' => $this->image($variant->shading_image),
                'photos' => $this->photos($variant, $flavors),
            ]])
            ->sortKeys()
            ->all();
    }

    /** @return array{width: int, height: int, base_svg: string, base_image: array{url: string, width: int, height: int}|null, slots: array<int, array{layer_no: int, type: string, path: string, band: list<array{0: float, 1: float}>|null}>} */
    public function drawing(CakeVariant $variant): array
    {
        return [
            'width' => $variant->view_box_width,
            'height' => $variant->view_box_height,
            // Sanitized on save already; again here so markup written straight into the database can't run either.
            'base_svg' => $this->sanitizer->sanitize((string) $variant->base_svg),
            // The cake photo the slots are drawn on, or null for a drawing on its own.
            'base_image' => $this->image($variant->base_image),
            // Bottom layer first and each sponge before its cream, so a cream's shadow falls on its sponge.
            'slots' => $variant->slots
                ->sortBy([
                    ['layer_no', 'asc'],
                    fn (CakeVariantSlot $a, CakeVariantSlot $b) => array_search($a->type, CakeVariantSlot::TYPES, true) <=> array_search($b->type, CakeVariantSlot::TYPES, true),
                ])
                ->map(fn (CakeVariantSlot $slot) => ['layer_no' => $slot->layer_no, 'type' => $slot->type, 'path' => $slot->svg_path, 'band' => $slot->bandPoints()])
                ->values()
                ->all(),
        ];
    }

    /**
     * The URL and pixel size of an uploaded image, or null when there is none or its file is missing.
     *
     * @return array{url: string, width: int, height: int}|null
     */
    public function image(?string $path): ?array
    {
        $size = $this->uploads->size($path);

        return $path && $size ? ['url' => asset($path), 'width' => $size[0], 'height' => $size[1]] : null;
    }

    /**
     * The variant's photos of the given flavors, keyed "type-flavorId-layerNo" (e.g. "sponge-3-1").
     *
     * @param  array{sponge: Collection<int, SpongeFlavor>, cream: Collection<int, CreamFlavor>}  $flavors
     * @return array<string, array{id: int, url: string, width: int, height: int}>
     */
    private function photos(CakeVariant $variant, array $flavors): array
    {
        return $variant->flavorImages
            ->filter(fn (FlavorVariantImage $photo) => isset($flavors[$photo->flavor_type]) && $flavors[$photo->flavor_type]->contains($photo->flavor_id))
            ->mapWithKeys(function (FlavorVariantImage $photo) {
                $image = $this->image($photo->image);

                return $image ? ["{$photo->flavor_type}-{$photo->flavor_id}-{$photo->layer_no}" => ['id' => $photo->id, ...$image]] : [];
            })
            ->all();
    }
}
