<?php

namespace App\Http\Controllers\Admin;

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
     * The photo of the cake with an empty cut face, optionally for every layer count at once. The view box
     * becomes the photo's pixel size; when that changes, the shapes no longer fit, so the drawing is unpublished
     * until the cut face is marked again.
     */
    public function updateBase(StoreVariantBaseRequest $request, CakeVariant $variant): RedirectResponse
    {
        $targets = $request->boolean('all_layers') ? CakeVariant::orderBy('layers')->get() : collect([$variant]);
        $refit = [];

        foreach ($targets as $target) {
            $old = $target->base_image;
            $path = $this->uploads->storeAsWebp($request->file('base'), 'cake-bases');
            [$width, $height] = $this->uploads->size($path) ?? [$target->view_box_width, $target->view_box_height];
            $resized = $width !== $target->view_box_width || $height !== $target->view_box_height;

            $target->update([
                'base_image' => $path,
                'view_box_width' => $width,
                'view_box_height' => $height,
                ...($resized ? ['cut_face' => null, 'is_published' => false] : []),
            ]);
            $this->uploads->delete($old);

            if ($resized) {
                $refit[] = $target->layers;
            }
        }

        $redirect = back()->with('success', $targets->count() > 1 ? 'Cake photo saved for every layer count.' : 'Cake photo saved.');

        return $refit
            ? $redirect->with('warning', 'Mark the cut face on the photo and fit the shapes, then publish again ('.implode(', ', $refit).' layers).')
            : $redirect;
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
