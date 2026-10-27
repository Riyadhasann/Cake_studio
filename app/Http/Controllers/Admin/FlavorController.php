<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveFlavorRequest;
use App\Models\CakeCustomization;
use App\Models\CakeCustomizationLayer;
use App\Models\CreamFlavor;
use App\Models\FlavorVariantImage;
use App\Models\SpongeFlavor;
use App\Services\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

/** Adding, editing, hiding and deleting sponge and cream flavors (their pictures are on CakeVisualController). */
class FlavorController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    public function store(SaveFlavorRequest $request, string $type): RedirectResponse
    {
        $model = $this->model($type);
        $flavor = $model::create([
            ...$request->safe()->only(['name', 'extra_price', 'color']),
            'is_active' => true,
            // A new flavor goes to the end of the menu
            'sort_order' => (int) $model::max('sort_order') + 1,
        ]);

        return back()->with('success', "{$flavor->name} added to the menu. Give it a picture below.");
    }

    public function update(SaveFlavorRequest $request, string $type, int $id): RedirectResponse
    {
        $flavor = $this->find($type, $id);
        $active = $request->boolean('is_active', $flavor->is_active);

        if (! $active && $flavor->is_active) {
            $this->ensureNotNeeded($flavor, $type, 'hidden');
        }

        // New prices apply to new cart items only: orders keep the price they were placed at.
        $flavor->update([...$request->safe()->only(['name', 'extra_price', 'color']), 'is_active' => $active]);

        return back()->with('success', "{$flavor->name} saved.".($active ? '' : ' It is hidden from customers.'));
    }

    /** Deletes a flavor no order has used, with its pictures. A used one can only be hidden. */
    public function destroy(string $type, int $id): RedirectResponse
    {
        $flavor = $this->find($type, $id);
        $column = "{$type}_flavor_id";

        if (CakeCustomizationLayer::where($column, $flavor->id)->exists() || CakeCustomization::where($column, $flavor->id)->exists()) {
            throw ValidationException::withMessages([
                'flavor' => "{$flavor->name} is in past orders, so it can't be deleted. Hide it from the menu instead.",
            ]);
        }

        if ($flavor->is_active) {
            $this->ensureNotNeeded($flavor, $type, 'deleted');
        }

        foreach (FlavorVariantImage::where('flavor_type', $type)->where('flavor_id', $flavor->id)->get() as $photo) {
            $this->uploads->delete($photo->image);
            $photo->delete();
        }
        $this->uploads->delete($flavor->layer_image);
        $this->uploads->delete($flavor->side_texture);
        if ($flavor instanceof CreamFlavor) {
            $this->uploads->delete($flavor->top_texture);
        }
        $flavor->delete();

        return back()->with('success', "{$flavor->name} deleted.");
    }

    /** The default flavor and the last one on the menu must stay: every layer needs one. */
    private function ensureNotNeeded(SpongeFlavor|CreamFlavor $flavor, string $type, string $doing): void
    {
        if ($flavor->is_default) {
            throw ValidationException::withMessages([
                'flavor' => "{$flavor->name} is the default {$type}, so it can't be {$doing}. Choose another default first.",
            ]);
        }

        if ($flavor->newQuery()->where('is_active', true)->count() === 1) {
            throw ValidationException::withMessages([
                'flavor' => "{$flavor->name} is the only {$type} on the menu. Add another one first.",
            ]);
        }
    }

    /** @return class-string<SpongeFlavor>|class-string<CreamFlavor> */
    private function model(string $type): string
    {
        return $type === 'cream' ? CreamFlavor::class : SpongeFlavor::class;
    }

    private function find(string $type, int $id): SpongeFlavor|CreamFlavor
    {
        return $this->model($type)::findOrFail($id);
    }
}
