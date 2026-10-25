<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFlavorSurfaceRequest;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Services\PublicUploads;
use App\Services\SeamlessTexture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

/** The pictures the 3D cake wraps around a sponge or cream layer, on the Cake images page. */
class FlavorSurfaceController extends Controller
{
    public function __construct(private PublicUploads $uploads, private SeamlessTexture $textures) {}

    /** Save the grain size, and the side and top pictures when new ones were chosen. */
    public function update(UpdateFlavorSurfaceRequest $request, string $type, int $id): RedirectResponse
    {
        $flavor = $this->find($type, $id);
        $changes = ['tile_scale' => $request->float('tile_scale')];

        if ($side = $request->file('side')) {
            $this->uploads->delete($flavor->side_texture);
            $changes['side_texture'] = $this->store($side, seamless: $request->boolean('seamless'));
        }

        if ($flavor instanceof CreamFlavor && ($top = $request->file('top'))) {
            $this->uploads->delete($flavor->top_texture);
            // Shown once on top, so cropped square but never tiled.
            $changes['top_texture'] = $this->store($top, seamless: false);
        }

        $flavor->update($changes);

        return back()->with('success', "3D look saved for {$flavor->name}.");
    }

    /** Remove the 3D pictures, so the 3D cake draws that layer from the flavor's color. */
    public function destroy(string $type, int $id): RedirectResponse
    {
        $flavor = $this->find($type, $id);

        $this->uploads->delete($flavor->side_texture);
        $changes = ['side_texture' => null];

        if ($flavor instanceof CreamFlavor) {
            $this->uploads->delete($flavor->top_texture);
            $changes['top_texture'] = null;
        }

        $flavor->update($changes);

        return back()->with('success', "3D pictures removed for {$flavor->name}. The 3D cake now uses its color.");
    }

    /** A square (or strip) WebP; the upload as it is when this PHP can't convert it. */
    private function store(UploadedFile $file, bool $seamless): string
    {
        $webp = $this->textures->make((string) $file->get(), $seamless);

        return $webp !== null
            ? $this->uploads->storeContents($webp, 'cake-textures', 'webp')
            : $this->uploads->store($file, 'cake-textures');
    }

    private function find(string $type, int $id): SpongeFlavor|CreamFlavor
    {
        return match ($type) {
            'sponge' => SpongeFlavor::findOrFail($id),
            'cream' => CreamFlavor::findOrFail($id),
            default => abort(404),
        };
    }
}
