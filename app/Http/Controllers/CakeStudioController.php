<?php

namespace App\Http\Controllers;

use App\Models\CakePart;
use App\Models\CakeSize;
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
        // The page only needs these fields. layer_image and texture_image are paths inside public/, or null.
        $forPage = fn ($flavor) => [
            'id' => $flavor->id,
            'name' => $flavor->name,
            'extra_price' => $flavor->extra_price,
            'color' => $flavor->color,
            'image' => $flavor->layer_image ? asset($flavor->layer_image) : null,
            // A texture whose file is gone is left out, so the slot falls back instead of drawing nothing.
            'texture' => $uploads->size($flavor->texture_image) ? asset($flavor->texture_image) : null,
            // [width, height] in pixels, so the drawing can crop the image to a slot like object-fit: cover
            'image_size' => $uploads->size($flavor->layer_image),
            // The strip of art in the layer image (e.g. a V-shaped slice), mapped arm by arm onto a slot of a cake photo
            'strip' => $strips->of($flavor->layer_image),
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
            'variants' => $drawings->published($sponges, $creams),
            // The admin's default flavors, which every new layer starts with. Falls back to the first one on the menu.
            'defaultSpongeId' => ($sponges->firstWhere('is_default', true) ?? $sponges->first())?->id,
            'defaultCreamId' => ($creams->firstWhere('is_default', true) ?? $creams->first())?->id,
        ]);
    }
}
