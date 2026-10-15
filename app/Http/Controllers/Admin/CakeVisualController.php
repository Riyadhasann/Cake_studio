<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Flavors\ReorderFlavors;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderFlavorsRequest;
use App\Http\Requests\Admin\UpdateLayerImageRequest;
use App\Models\CakePart;
use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Services\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CakeVisualController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    public function index(): View
    {
        // The cake photo is shared by every layer count; any drawing that has one shows it.
        $photoDrawings = CakeVariant::whereNotNull('base_image')->orderBy('layers')->get();

        return view('admin.cake-visuals.index', [
            'photo' => $photoDrawings->first()?->base_image,
            'photoLive' => $photoDrawings->where('is_published', true)->pluck('layers')->all(),
            'sponges' => SpongeFlavor::where('is_active', true)->ordered()->get(),
            'creams' => CreamFlavor::where('is_active', true)->ordered()->get(),
            'parts' => CakePart::orderBy('id')->get(),
        ]);
    }

    /** Save (or replace) the image for one sponge, one cream, or one fixed part. Sponges and creams may be SVG. */
    public function update(UpdateLayerImageRequest $request, string $type, int $id): RedirectResponse
    {
        $item = $this->find($type, $id);
        $svg = $request->sanitizedSvg();

        $this->uploads->delete($item->layer_image);

        // Saved as a path inside public/ (through the storage link), the same format the studio page already reads.
        // An SVG is saved as cleaned, never as uploaded.
        $item->update(['layer_image' => $svg !== null
            ? $this->uploads->storeContents($svg, 'cake-layers', 'svg')
            : $this->uploads->store($request->file('image'), 'cake-layers')]);

        return back()->with('success', "Image saved for {$item->name}.");
    }

    /** Remove the image so that item goes back to the default drawing. */
    public function destroy(string $type, int $id): RedirectResponse
    {
        $item = $this->find($type, $id);

        $this->uploads->delete($item->layer_image);
        $item->update(['layer_image' => null]);

        return back()->with('success', "Image removed for {$item->name}. It now uses the default drawing.");
    }

    /** Make this sponge or cream the one every new layer starts with in the design page. */
    public function setDefault(string $type, int $id): RedirectResponse
    {
        $item = $this->findFlavor($type, $id);

        if (! $item->is_active) {
            return back()->withErrors(['default' => "{$item->name} is not on the menu, so it can't be the default."]);
        }

        // Exactly one default per type: clear the old one, then set the new one.
        DB::transaction(function () use ($item) {
            $item->newQuery()->where('is_default', true)->update(['is_default' => false]);
            $item->update(['is_default' => true]);
        });

        return back()->with('success', "{$item->name} is now the default {$type} in the cake preview.");
    }

    /** Save the order sponges or creams are listed in, here and on the design page. */
    public function reorder(ReorderFlavorsRequest $request, string $type, ReorderFlavors $reorder): RedirectResponse
    {
        $reorder->handle($request->modelClass(), $request->orderedIds());

        // Put keyboard focus back on the arrow that was pressed, so a flavor can be moved several places in a row.
        $focus = preg_match('/^(sponge|cream)-\d+-(up|down)$/', (string) $request->input('focus')) === 1 ? $request->input('focus') : null;

        return back()->with('success', 'New '.$type.' order saved.')->with('focus', $focus);
    }

    private function find(string $type, int $id): SpongeFlavor|CreamFlavor|CakePart
    {
        return match ($type) {
            'sponge' => SpongeFlavor::findOrFail($id),
            'cream' => CreamFlavor::findOrFail($id),
            'part' => CakePart::findOrFail($id),
            default => abort(404),
        };
    }

    /** Sponge or cream only: parts have no default or menu. */
    private function findFlavor(string $type, int $id): SpongeFlavor|CreamFlavor
    {
        return match ($type) {
            'sponge' => SpongeFlavor::findOrFail($id),
            'cream' => CreamFlavor::findOrFail($id),
            default => abort(404),
        };
    }
}
