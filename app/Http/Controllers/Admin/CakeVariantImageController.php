<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Variants\ApplyCakePhoto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantBaseRequest;
use App\Http\Requests\Admin\StoreVariantPhotoRequest;
use App\Http\Requests\Admin\StoreVariantShadingRequest;
use App\Models\CakeVariant;
use App\Models\FlavorVariantImage;
use App\Services\PublicUploads;
use Illuminate\Http\RedirectResponse;

/** The images of a drawing: the cake photo under it, its shading image and per-slot flavor photos. */
class CakeVariantImageController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    /**
     * The photo of the cake with an empty cut face, optionally for every layer count at once. The cut is found
     * in the photo and the layers fitted into it; when it can't be found, it is marked by hand.
     */
    public function updateBase(StoreVariantBaseRequest $request, CakeVariant $variant, ApplyCakePhoto $apply): RedirectResponse
    {
        $targets = $request->boolean('all_layers') ? CakeVariant::orderBy('layers')->get() : collect([$variant]);
        $saved = $targets->count() > 1 ? 'Cake photo saved for every layer count.' : 'Cake photo saved.';

        return $apply->handle($request->file('base'), $targets)
            ? back()->with('success', $saved.' The layers were fitted into the cut.')
            : back()->with('success', $saved)->with('warning', 'The empty cut could not be found in the photo: mark it below and fit the shapes, then publish.');
    }

    /** Back to a drawing without a photo. It is unpublished: shapes fitted to the photo would float on nothing. */
    public function destroyBase(CakeVariant $variant): RedirectResponse
    {
        $this->uploads->delete($variant->base_image);
        $variant->update(['base_image' => null, 'cut_face' => null, 'is_published' => false]);

        return back()->with('success', 'Cake photo removed. Draw the shapes again, then publish.');
    }

    public function updateShading(StoreVariantShadingRequest $request, CakeVariant $variant): RedirectResponse
    {
        $this->uploads->delete($variant->shading_image);
        $variant->update(['shading_image' => $this->uploads->storeAsWebp($request->file('shading'), 'cake-shading')]);

        return back()->with('success', 'Shading image saved.');
    }

    public function destroyShading(CakeVariant $variant): RedirectResponse
    {
        $this->uploads->delete($variant->shading_image);
        $variant->update(['shading_image' => null]);

        return back()->with('success', 'Shading image removed. The drawing uses the built-in shading again.');
    }

    /** One photo per flavor per slot: uploading again replaces it. */
    public function storePhoto(StoreVariantPhotoRequest $request, CakeVariant $variant): RedirectResponse
    {
        $slot = $request->safe()->only(['layer_no', 'flavor_type', 'flavor_id']);
        $existing = $variant->flavorImages()->where($slot)->first();
        $path = $this->uploads->storeAsWebp($request->file('photo'), 'cake-photos');

        $photo = $variant->flavorImages()->updateOrCreate($slot, ['image' => $path]);
        $this->uploads->delete($existing?->image);

        $flavor = $photo->flavor()->name ?? 'Flavor';

        return back()->with('success', "Photo saved for {$flavor} in layer {$photo->layer_no}.");
    }

    public function destroyPhoto(CakeVariant $variant, FlavorVariantImage $photo): RedirectResponse
    {
        abort_unless($photo->cake_variant_id === $variant->id, 404);

        $this->uploads->delete($photo->image);
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }
}
