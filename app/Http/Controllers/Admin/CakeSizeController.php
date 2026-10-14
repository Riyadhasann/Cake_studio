<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Sizes\DeleteCakeSize;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCakeSizeRequest;
use App\Models\CakeSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CakeSizeController extends Controller
{
    public function index(): View
    {
        return view('admin.sizes.index', [
            'sizes' => CakeSize::ordered()->withCount('cakeCustomizations')->get(),
        ]);
    }

    public function store(SaveCakeSizeRequest $request): RedirectResponse
    {
        $size = CakeSize::create($request->validated());

        return to_route('admin.sizes.index')->with('success', "{$size->label} added.");
    }

    public function edit(CakeSize $size): View
    {
        return view('admin.sizes.edit', ['size' => $size]);
    }

    /** New prices apply to new cart items only: orders already placed keep the price they were placed at. */
    public function update(SaveCakeSizeRequest $request, CakeSize $size): RedirectResponse
    {
        $size->update($request->validated());

        return to_route('admin.sizes.index')->with('success', "{$size->label} saved.");
    }

    public function destroy(CakeSize $size, DeleteCakeSize $delete): RedirectResponse
    {
        $delete->handle($size);

        return to_route('admin.sizes.index')->with('success', "{$size->label} deleted.");
    }
}
