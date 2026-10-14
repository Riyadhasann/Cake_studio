<?php

namespace App\Http\Controllers;

use App\Models\CakePart;
use App\Models\CakeSize;
use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Services\SvgSanitizer;
use Illuminate\View\View;

class CakeStudioController extends Controller
{
    public function index(SvgSanitizer $sanitizer): View
    {
        // The page only needs these fields. layer_image and texture_image are paths inside public/, or null.
        $forPage = fn ($flavor) => [
            'id' => $flavor->id,
            'name' => $flavor->name,
            'extra_price' => $flavor->extra_price,
            'color' => $flavor->color,
            'image' => $flavor->layer_image ? asset($flavor->layer_image) : null,
            'texture' => $flavor->texture_image ? asset($flavor->texture_image) : null,
        ];

        // ['frosting' => url or null, 'plate' => url or null]
        $parts = CakePart::all()->mapWithKeys(
            fn ($part) => [$part->key => $part->layer_image ? asset($part->layer_image) : null]
        );

        $sponges = SpongeFlavor::where('is_active', true)->ordered()->get();
        $creams = CreamFlavor::where('is_active', true)->ordered()->get();

        return view('cake-studio.index', [
            'sizes' => CakeSize::ordered()->get(),
            'sponges' => $sponges->map($forPage)->values(),
            'creams' => $creams->map($forPage)->values(),
            'parts' => $parts,
            'variants' => $this->publishedVariants($sanitizer),
            // The admin's default flavors, which every new layer starts with. Falls back to the first one on the menu.
            'defaultSpongeId' => ($sponges->firstWhere('is_default', true) ?? $sponges->first())?->id,
            'defaultCreamId' => ($creams->firstWhere('is_default', true) ?? $creams->first())?->id,
        ]);
    }

    /**
     * The SVG drawing for each layer count that has a published, complete one, keyed by layer count.
     * A layer count missing here keeps the layered preview.
     *
     * @return array<int, array{width: int, height: int, base_svg: string, slots: array<int, array{layer_no: int, type: string, path: string}>}>
     */
    private function publishedVariants(SvgSanitizer $sanitizer): array
    {
        return CakeVariant::published()
            ->with('slots')
            ->get()
            ->filter(fn (CakeVariant $variant) => $variant->canPublish())
            ->mapWithKeys(fn (CakeVariant $variant) => [$variant->layers => [
                'width' => $variant->view_box_width,
                'height' => $variant->view_box_height,
                // Sanitized on save already; again here so markup written straight into the database can't run either.
                'base_svg' => $sanitizer->sanitize((string) $variant->base_svg),
                // Bottom layer first and each sponge before its cream, so a cream's shadow falls on its sponge.
                'slots' => $variant->slots
                    ->sortBy([['layer_no', 'asc'], fn (CakeVariantSlot $a, CakeVariantSlot $b) => array_search($a->type, CakeVariantSlot::TYPES, true) <=> array_search($b->type, CakeVariantSlot::TYPES, true)])
                    ->map(fn (CakeVariantSlot $slot) => ['layer_no' => $slot->layer_no, 'type' => $slot->type, 'path' => $slot->svg_path])
                    ->values()
                    ->all(),
            ]])
            ->sortKeys()
            ->all();
    }
}
