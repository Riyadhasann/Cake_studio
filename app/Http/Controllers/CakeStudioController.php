<?php

namespace App\Http\Controllers;

use App\Models\CakePart;
use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\View\View;

class CakeStudioController extends Controller
{
    public function index(): View
    {
        // The page only needs these fields. layer_image is a path inside public/, or null.
        $forPage = fn ($flavor) => [
            'id' => $flavor->id,
            'name' => $flavor->name,
            'extra_price' => $flavor->extra_price,
            'color' => $flavor->color,
            'image' => $flavor->layer_image ? asset($flavor->layer_image) : null,
        ];

        // ['frosting' => url or null, 'plate' => url or null]
        $parts = CakePart::all()->mapWithKeys(
            fn ($part) => [$part->key => $part->layer_image ? asset($part->layer_image) : null]
        );

        $sponges = SpongeFlavor::where('is_active', true)->ordered()->get();
        $creams = CreamFlavor::where('is_active', true)->ordered()->get();

        return view('cake-studio.index', [
            'sizes' => CakeSize::all(),
            'sponges' => $sponges->map($forPage)->values(),
            'creams' => $creams->map($forPage)->values(),
            'parts' => $parts,
            // The admin's default flavors, which every new layer starts with. Falls back to the first one on the menu.
            'defaultSpongeId' => ($sponges->firstWhere('is_default', true) ?? $sponges->first())?->id,
            'defaultCreamId' => ($creams->firstWhere('is_default', true) ?? $creams->first())?->id,
        ]);
    }
}
