<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Variants\SaveCakeVariant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCakeVariantRequest;
use App\Models\CakeVariant;
use App\Services\SvgSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CakeVariantController extends Controller
{
    /** One row per layer count, whether or not its drawing exists yet. */
    public function index(): View
    {
        $variants = CakeVariant::with('slots')->get()->keyBy('layers');

        return view('admin.cake-variants.index', [
            'layerCounts' => CakeVariant::LAYER_COUNTS,
            'variants' => $variants,
        ]);
    }

    /** Starts an empty, unpublished drawing for a layer count that has none. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'layers' => ['required', 'integer', Rule::in(CakeVariant::LAYER_COUNTS), Rule::unique('cake_variants', 'layers')],
        ]);

        $variant = CakeVariant::create([
            'layers' => $validated['layers'],
            'view_box_width' => 700,
            'view_box_height' => 196 * $validated['layers'],
        ]);

        return to_route('admin.cake-variants.edit', $variant)->with('success', "{$variant->layers}-layer drawing started.");
    }

    public function edit(CakeVariant $variant): View
    {
        $variant->load('slots');

        return view('admin.cake-variants.edit', [
            'variant' => $variant,
            'allowedElements' => SvgSanitizer::ELEMENTS,
            'allowedAttributes' => SvgSanitizer::ATTRIBUTES,
        ]);
    }

    public function update(UpdateCakeVariantRequest $request, CakeVariant $variant, SaveCakeVariant $save): RedirectResponse
    {
        /** @var array{view_box_width: int|string, view_box_height: int|string, base_svg?: string|null, slots: array<int|string, array<string, array<string, mixed>>>} $input */
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
