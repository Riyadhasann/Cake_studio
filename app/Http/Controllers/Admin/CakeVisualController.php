<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Flavors\ReorderFlavors;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderFlavorsRequest;
use App\Http\Requests\Admin\StoreFlavorTextureRequest;
use App\Models\CakePart;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CakeVisualController extends Controller
{
    public function index(): View
    {
        return view('admin.cake-visuals.index', [
            'sponges' => SpongeFlavor::where('is_active', true)->ordered()->get(),
            'creams' => CreamFlavor::where('is_active', true)->ordered()->get(),
            'parts' => CakePart::orderBy('id')->get(),
        ]);
    }

    /** Save (or replace) the image for one sponge, one cream, or one fixed part. */
    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $item = $this->find($type, $id);

        $request->validate(
            ['image' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000']],
            [],
            ['image' => "image for {$item->name}"]
        );

        $this->deleteStoredFile($item->layer_image);

        $path = $request->file('image')->store('cake-layers', 'public');

        // Saved as a path inside public/ (through the storage link), the same format the studio page already reads.
        $item->update(['layer_image' => 'storage/'.$path]);

        return back()->with('success', "Image saved for {$item->name}.");
    }

    /** Remove the image so that item goes back to the default drawing. */
    public function destroy(string $type, int $id): RedirectResponse
    {
        $item = $this->find($type, $id);

        $this->deleteStoredFile($item->layer_image);
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

    /** Save (or replace) the tileable texture of one sponge or cream. */
    public function updateTexture(StoreFlavorTextureRequest $request, string $type, int $id): RedirectResponse
    {
        $item = $this->findFlavor($type, $id);

        $this->deleteStoredFile($item->texture_image);

        $path = $request->file('texture')->store('cake-textures', 'public');

        $item->update(['texture_image' => 'storage/'.$path]);

        return back()->with('success', "Texture saved for {$item->name}.");
    }

    /** Remove the texture so the flavor goes back to its image or color. */
    public function destroyTexture(string $type, int $id): RedirectResponse
    {
        $item = $this->findFlavor($type, $id);

        $this->deleteStoredFile($item->texture_image);
        $item->update(['texture_image' => null]);

        return back()->with('success', "Texture removed for {$item->name}.");
    }

    /** Save the order sponges or creams are listed in, here and on the design page. */
    public function reorder(ReorderFlavorsRequest $request, string $type, ReorderFlavors $reorder): RedirectResponse
    {
        $reorder->handle($request->modelClass(), $request->orderedIds());

        return back()->with('success', 'New '.$type.' order saved.');
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

    /** Sponge or cream only: parts have no texture, default or menu. */
    private function findFlavor(string $type, int $id): SpongeFlavor|CreamFlavor
    {
        return match ($type) {
            'sponge' => SpongeFlavor::findOrFail($id),
            'cream' => CreamFlavor::findOrFail($id),
            default => abort(404),
        };
    }

    /** Only deletes files this page stored. A path you placed in public/ by hand is never touched. */
    private function deleteStoredFile(?string $path): void
    {
        if (! $path || str_contains($path, '..')) {
            return;
        }

        foreach (['storage/cake-layers/', 'storage/cake-textures/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                Storage::disk('public')->delete(substr($path, strlen('storage/')));

                return;
            }
        }
    }
}
