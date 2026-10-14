<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Variants\SaveCakeVariant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCakeVariantRequest;
use App\Models\CakeVariant;
use App\Support\SvgSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CakeVariantController extends Controller
{
    public function index(): View
    {
        $variants = CakeVariant::with('slots')->orderBy('layers')->get()->keyBy('layers');

        return view('admin.cake-variants.index', [
            'variants' => $variants,
            'missingLayers' => array_values(array_diff(CakeVariant::LAYER_COUNTS, $variants->keys()->all())),
        ]);
    }

    /** Starts an empty, unpublished drawing for a layer count that has none. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'layers' => ['required', 'integer', Rule::in(CakeVariant::LAYER_COUNTS), Rule::unique('cake_variants', 'layers')],
        ]);

        $variant = CakeVariant::create([
            'layers' => (int) $data['layers'],
            'view_box_width' => 700,
            'view_box_height' => 196 * (int) $data['layers'],
        ]);

        return to_route('admin.cake-variants.edit', $variant)->with('success', "{$variant->layers}-layer cake created. Draw its slots, then publish it.");
    }

    public function edit(CakeVariant $variant): View
    {
        return view('admin.cake-variants.edit', [
            'variant' => $variant->load('slots'),
            'allowedElements' => SvgSanitizer::ELEMENTS,
            'allowedAttributes' => SvgSanitizer::ATTRIBUTES,
        ]);
    }

    public function update(SaveCakeVariantRequest $request, CakeVariant $variant, SaveCakeVariant $save): RedirectResponse
    {
        $unpublished = $save->handle(
            $variant,
            $request->integer('view_box_width'),
            $request->integer('view_box_height'),
            $request->input('base_svg'),
            $request->array('slots'),
        );

        $redirect = to_route('admin.cake-variants.edit', $variant)->with('success', "{$variant->layers}-layer cake saved.");

        return $unpublished
            ? $redirect->with('warning', 'It was unpublished because it no longer passes the publish check: '.implode(' ', $variant->publishProblems()))
            : $redirect;
    }

    public function publish(CakeVariant $variant): RedirectResponse
    {
        $variant->load('slots');

        if (! $variant->canPublish()) {
            throw ValidationException::withMessages(['publish' => $variant->publishProblems()]);
        }

        $variant->update(['is_published' => true]);

        return back()->with('success', "{$variant->layers}-layer cake published. Customers can see it.");
    }

    public function unpublish(CakeVariant $variant): RedirectResponse
    {
        $variant->update(['is_published' => false]);

        return back()->with('success', "{$variant->layers}-layer cake unpublished. The design page uses the default drawing for it.");
    }
}
