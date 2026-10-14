<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CakePart;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\Database\Eloquent\Model;
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
            'sponges' => SpongeFlavor::where('is_active', true)->orderBy('id')->get(),
            'creams' => CreamFlavor::where('is_active', true)->orderBy('id')->get(),
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
        $item->update(['layer_image' => 'storage/' . $path]);

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
        $item = $this->find($type, $id);

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

    private function find(string $type, int $id): Model
    {
        return match ($type) {
            'sponge' => SpongeFlavor::findOrFail($id),
            'cream' => CreamFlavor::findOrFail($id),
            'part' => CakePart::findOrFail($id),
            default => abort(404),
        };
    }

    /** Only deletes files this page stored. A path you placed in public/ by hand is never touched. */
    private function deleteStoredFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/cake-layers/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }
}
