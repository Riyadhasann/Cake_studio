<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Variants\ApplyCakePhoto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantBaseRequest;
use App\Models\CakeVariant;
use App\Services\PublicUploads;
use Illuminate\Http\RedirectResponse;

/** The cake photo from the Cake images page: one upload for every layer count. */
class CakePhotoController extends Controller
{
    public function update(StoreVariantBaseRequest $request, ApplyCakePhoto $apply): RedirectResponse
    {
        // Every layer count gets a drawing, so the photo is used whatever the customer picks.
        $variants = collect(CakeVariant::offeredLayerCounts())->map(fn (int $layers) => CakeVariant::firstOrCreate(
            ['layers' => $layers],
            ['view_box_width' => 700, 'view_box_height' => 196 * $layers],
        ));

        if (! $apply->handle($request->file('base'), $variants)) {
            return back()->with('warning', 'Photo saved, but the empty cut could not be found in it, so customers don\'t see it yet. '
                .'Leave the cut see-through (or a light gray checkerboard) and upload again, or mark the cut by hand under Cake shapes.');
        }

        $counts = $variants->pluck('layers')->all();
        $listed = implode(', ', array_slice($counts, 0, -1)).' and '.end($counts);

        return back()->with('success', "Cake photo saved. The layers were fitted into the cut for {$listed} layers, and customers see it now.");
    }

    public function destroy(PublicUploads $uploads): RedirectResponse
    {
        foreach (CakeVariant::whereNotNull('base_image')->get() as $variant) {
            $uploads->delete($variant->base_image);
            $variant->update(['base_image' => null, 'cut_face' => null, 'is_published' => false]);
        }

        return back()->with('success', 'Cake photo removed. The design page shows the drawn cake again.');
    }
}
