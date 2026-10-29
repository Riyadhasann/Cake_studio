<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Variants\SaveCakeVariant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCakeVariantRequest;
use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Services\Images\SvgSanitizer;
use App\Services\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CakeVariantController extends Controller
{
    /** One row per offered layer count, whether or not its drawing exists yet. */
    public function index(): View
    {
        $variants = CakeVariant::with('slots')->get()->keyBy('layers');
        $layerCounts = CakeVariant::offeredLayerCounts();

        return view('admin.cake-variants.index', [
            'layerCounts' => $layerCounts,
            'variants' => $variants,
            // The layer counts the admin can still add, e.g. [5, 6, 7, 8]
            'addable' => array_values(array_diff(range(CakeVariant::MIN_LAYERS, CakeVariant::MAX_LAYERS), $layerCounts)),
        ]);
    }

    /**
     * Starts a drawing for a layer count that has none. A count above the built-in ones is then offered to customers.
     * When the cake photo is already up, the new drawing gets a copy of it with the layers fitted into its cut.
     */
    public function store(Request $request, PublicUploads $uploads): RedirectResponse
    {
        $validated = $request->validate([
            'layers' => ['required', 'integer', 'between:'.CakeVariant::MIN_LAYERS.','.CakeVariant::MAX_LAYERS, Rule::unique('cake_variants', 'layers')],
        ], [
            'layers.between' => 'A cake can have '.CakeVariant::MIN_LAYERS.' to '.CakeVariant::MAX_LAYERS.' layers.',
            'layers.unique' => 'That layer count already has a drawing.',
        ]);
        $layers = (int) $validated['layers'];

        $photo = CakeVariant::whereNotNull('base_image')->whereNotNull('cut_face')->orderBy('layers')->get()
            ->first(fn (CakeVariant $variant) => $uploads->file($variant->base_image) !== null);

        $variant = CakeVariant::create([
            'layers' => $layers,
            'view_box_width' => $photo->view_box_width ?? 700,
            'view_box_height' => $photo->view_box_height ?? 196 * $layers,
        ]);

        if ($photo) {
            $file = (string) $uploads->file($photo->base_image);
            $variant->update([
                'base_image' => $uploads->storeContents((string) file_get_contents($file), 'cake-bases', strtolower(pathinfo($file, PATHINFO_EXTENSION))),
                'cut_face' => $photo->cut_face,
            ]);
            $variant->fitSlotsToCutFace();
            $variant->update(['is_published' => $variant->canPublish()]);

            return to_route('admin.cake-variants.index')->with('success', "{$layers} layers added. The layers were fitted into the cake photo, and customers can pick it now.");
        }

        return to_route('admin.cake-variants.edit', $variant)->with('success', $variant->isExtra()
            ? "{$layers} layers added. Customers can pick it now (with the layered preview until this drawing is published)."
            : "{$layers}-layer drawing started.");
    }

    /** Removes a layer count the admin added, with its drawing and pictures. The built-in counts stay. */
    public function destroy(CakeVariant $variant, PublicUploads $uploads): RedirectResponse
    {
        abort_unless($variant->isExtra(), 403, 'The built-in layer counts can\'t be removed.');

        foreach ($variant->flavorImages as $photo) {
            $uploads->delete($photo->image);
        }
        $uploads->delete($variant->base_image);
        $uploads->delete($variant->shading_image);
        $variant->delete();

        return to_route('admin.cake-variants.index')->with('success', "{$variant->layers} layers removed. Customers can no longer pick it; orders already placed keep their layers.");
    }

    public function edit(CakeVariant $variant): View
    {
        $variant->load(['slots', 'flavorImages']);

        // The cut face to start from: this drawing's, else one marked on another drawing's photo of the same size
        // (usually the same photo), so it is marked once for every layer count.
        $cutFace = $variant->cut_face ?? ($variant->base_image ? CakeVariant::whereKeyNot($variant->id)
            ->whereNotNull('base_image')->whereNotNull('cut_face')
            ->where('view_box_width', $variant->view_box_width)->where('view_box_height', $variant->view_box_height)
            ->value('cut_face') : null);

        return view('admin.cake-variants.edit', [
            'variant' => $variant,
            'cutFace' => is_string($cutFace) ? json_decode($cutFace, true) : $cutFace,
            // Every flavor, so photos of flavors taken off the menu still show a name; the upload form lists active ones.
            'flavors' => [
                'sponge' => SpongeFlavor::ordered()->get(['id', 'name', 'is_active']),
                'cream' => CreamFlavor::ordered()->get(['id', 'name', 'is_active']),
            ],
            'allowedElements' => SvgSanitizer::ELEMENTS,
            'allowedAttributes' => SvgSanitizer::ATTRIBUTES,
        ]);
    }

    public function update(UpdateCakeVariantRequest $request, CakeVariant $variant, SaveCakeVariant $save): RedirectResponse
    {
        /** @var array{view_box_width: int|string, view_box_height: int|string, base_svg?: string|null, cut_face?: array<string, mixed>|null, slots: array<int|string, array<string, array<string, mixed>>>} $input */
        $input = $request->validated();
        $unpublished = $save->handle($variant, $input);

        $redirect = to_route('admin.cake-variants.edit', $variant)->with('success', "{$variant->layers}-layer drawing saved.");

        return $unpublished
            ? $redirect->with('warning', 'It was unpublished because it is no longer complete: '.implode(' ', $variant->publishProblems()))
            : $redirect;
    }

    public function publish(CakeVariant $variant): RedirectResponse
    {
        $variant->load('slots');

        if (! $variant->canPublish()) {
            throw ValidationException::withMessages(['publish' => $variant->publishProblems()]);
        }

        $variant->update(['is_published' => true]);

        return back()->with('success', "{$variant->layers}-layer drawing published.");
    }

    public function unpublish(CakeVariant $variant): RedirectResponse
    {
        $variant->update(['is_published' => false]);

        return back()->with('success', "{$variant->layers}-layer drawing unpublished.");
    }
}
