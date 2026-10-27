<?php

namespace App\Http\Controllers;

use App\Models\CakeDesign;
use App\Models\CakePart;
use App\Models\CakeSize;
use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Services\CakeDrawings;
use App\Services\LayerStrips;
use App\Services\PublicUploads;
use Illuminate\View\View;

class CakeStudioController extends Controller
{
    public function index(CakeDrawings $drawings, PublicUploads $uploads, LayerStrips $strips): View
    {
        // The page only needs these fields. layer_image is a path inside public/, or null.
        $forPage = fn ($flavor) => [
            'id' => $flavor->id,
            'name' => $flavor->name,
            'extra_price' => $flavor->extra_price,
            'color' => $flavor->color,
            'image' => $flavor->layer_image ? asset($flavor->layer_image) : null,
            // [width, height] in pixels, so the drawing can crop the image to a slot like object-fit: cover
            'image_size' => $uploads->size($flavor->layer_image),
            // The strip of art in the layer image (e.g. a V-shaped slice), mapped arm by arm onto a slot of a cake photo
            'strip' => $strips->of($flavor->layer_image),
            // What the 3D cake wraps around the layer; without a side picture it draws a pattern in the color
            'surface' => [
                'side' => $flavor->side_texture ? asset($flavor->side_texture) : null,
                'top' => $flavor instanceof CreamFlavor && $flavor->top_texture ? asset($flavor->top_texture) : null,
                'scale' => (float) $flavor->tile_scale,
            ],
        ];

        // ['frosting' => url or null, 'plate' => url or null]
        $parts = CakePart::all()->mapWithKeys(
            fn ($part) => [$part->key => $part->layer_image ? asset($part->layer_image) : null]
        );

        $sponges = SpongeFlavor::where('is_active', true)->ordered()->get();
        $creams = CreamFlavor::where('is_active', true)->ordered()->get();

        // Finished designs with at least one photo: [id, name, extra_price, frames => [url, ...], fit], the first frame is
        // the cover, which the 3D cake wears (fit: where the cake sits in it; toppings: cut-outs stood on its top;
        // top_image: the cover with them painted out of the top).
        $designs = CakeDesign::where('is_active', true)->ordered()->with('frames')->get()
            ->filter(fn (CakeDesign $design) => $design->frames->isNotEmpty())
            ->map(fn (CakeDesign $design) => [
                'id' => $design->id,
                'name' => $design->name,
                'extra_price' => $design->extra_price,
                'frames' => $design->frames->map(fn ($frame) => asset($frame->image))->values()->all(),
                'fit' => $design->fitOrDefault(),
                'toppings' => array_map(fn (array $topping) => [...$topping, 'image' => asset($topping['image'])], $design->toppingList()),
                'top_image' => $design->top_image ? asset($design->top_image) : null,
            ])
            ->values();

        return view('cake-studio.index', [
            'designs' => $designs,
            'sizes' => CakeSize::ordered()->get(),
            'sponges' => $sponges->map($forPage)->values(),
            'creams' => $creams->map($forPage)->values(),
            'parts' => $parts,
            'variants' => $drawings->published($sponges, $creams),
            // 2, 3 and 4 layers, plus any layer count the admin added on the Cake shapes page
            'layerOptions' => CakeVariant::offeredLayerCounts(),
            // The admin's default flavors, which every new layer starts with. Falls back to the first one on the menu.
            'defaultSpongeId' => ($sponges->firstWhere('is_default', true) ?? $sponges->first())?->id,
            'defaultCreamId' => ($creams->firstWhere('is_default', true) ?? $creams->first())?->id,
        ]);
    }
}
