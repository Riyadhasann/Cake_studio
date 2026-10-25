<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCakeDesignRequest;
use App\Http\Requests\Admin\SaveCakeDesignToppingRequest;
use App\Http\Requests\Admin\StoreCakeDesignFramesRequest;
use App\Http\Requests\Admin\UpdateCakeDesignFitRequest;
use App\Models\CakeDesign;
use App\Models\CakeDesignFrame;
use App\Services\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Cake designs customers can pick on the design page, and their photos (one, or a full turn for 360°). */
class CakeDesignController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    public function index(): View
    {
        return view('admin.designs.index', [
            'designs' => CakeDesign::ordered()->with('frames')->withCount('customizations')->get(),
        ]);
    }

    public function store(SaveCakeDesignRequest $request): RedirectResponse
    {
        $design = DB::transaction(function () use ($request) {
            $design = CakeDesign::create([
                'name' => $request->string('name')->toString(),
                'extra_price' => $request->input('extra_price'),
                'is_active' => true,
                'sort_order' => (int) CakeDesign::max('sort_order') + 1,
            ]);
            $this->addFrames($design, $request->file('frames', []));

            return $design;
        });

        return to_route('admin.designs.index')->with('success', "{$design->name} added.");
    }

    /** New prices apply to new cart items only: orders already placed keep their price. */
    public function update(SaveCakeDesignRequest $request, CakeDesign $design): RedirectResponse
    {
        $design->update([
            'name' => $request->string('name')->toString(),
            'extra_price' => $request->input('extra_price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('admin.designs.index')->with('success', "{$design->name} saved.");
    }

    /** Where the cake sits in the cover photo, which the 3D cake on the design page wears. */
    public function updateFit(UpdateCakeDesignFitRequest $request, CakeDesign $design): RedirectResponse
    {
        $design->update(['fit' => array_map(floatval(...), $request->safe()->only(array_keys(CakeDesign::DEFAULT_FIT)))]);

        return to_route('admin.designs.index')->with('success', "3D fit of {$design->name} saved.");
    }

    /** A topping cut-out stood round the 3D cake's top, saved as WebP (its transparency kept). */
    public function storeTopping(SaveCakeDesignToppingRequest $request, CakeDesign $design): RedirectResponse
    {
        /** @var UploadedFile $image */
        $image = $request->file('image');
        $toppings = $design->toppingList();
        $toppings[] = ['image' => $this->uploads->storeAsWebp($image, 'cake-designs', 92), ...$this->toppingPlacement($request)];
        $design->update(['toppings' => $toppings]);

        return to_route('admin.designs.index')->with('success', "Topping added to {$design->name}.");
    }

    public function updateTopping(SaveCakeDesignToppingRequest $request, CakeDesign $design, int $index): RedirectResponse
    {
        $toppings = $design->toppingList();
        abort_unless(isset($toppings[$index]), 404);

        $toppings[$index] = [...$toppings[$index], ...$this->toppingPlacement($request)];
        $design->update(['toppings' => $toppings]);

        return to_route('admin.designs.index')->with('success', "Topping of {$design->name} saved.");
    }

    public function destroyTopping(CakeDesign $design, int $index): RedirectResponse
    {
        $toppings = $design->toppingList();
        abort_unless(isset($toppings[$index]), 404);

        $this->uploads->delete($toppings[$index]['image']);
        array_splice($toppings, $index, 1);
        $design->update(['toppings' => $toppings]);

        return to_route('admin.designs.index')->with('success', "Topping removed from {$design->name}.");
    }

    /** @return array{count: int, ring: float, size: float, angle: float} */
    private function toppingPlacement(SaveCakeDesignToppingRequest $request): array
    {
        return [
            'count' => $request->integer('count'),
            'ring' => $request->float('ring'),
            'size' => $request->float('size'),
            'angle' => $request->float('angle'),
        ];
    }

    /** Orders keep the design's name (the link to it is cleared). Its photos are deleted. */
    public function destroy(CakeDesign $design): RedirectResponse
    {
        $images = $design->frames->pluck('image')->merge(array_column($design->toppingList(), 'image'));
        $design->delete();
        $images->each(fn (string $image) => $this->uploads->delete($image));

        return to_route('admin.designs.index')->with('success', "{$design->name} deleted.");
    }

    public function storeFrames(StoreCakeDesignFramesRequest $request, CakeDesign $design): RedirectResponse
    {
        $count = count($request->file('frames', []));
        $this->addFrames($design, $request->file('frames', []));

        return to_route('admin.designs.index')->with('success', "{$count} ".str('photo')->plural($count)." added to {$design->name}.");
    }

    /** A design keeps at least one photo: customers always see what they pick. */
    public function destroyFrame(CakeDesign $design, CakeDesignFrame $frame): RedirectResponse
    {
        abort_unless($frame->cake_design_id === $design->id, 404);

        if ($design->frames()->count() <= 1) {
            return back()->withErrors(['frames' => 'A design needs at least one photo. Add another one first, or delete the design.']);
        }

        $this->uploads->delete($frame->image);
        $frame->delete();

        return to_route('admin.designs.index')->with('success', "Photo removed from {$design->name}.");
    }

    /**
     * Saves the photos after the design's last one, as WebP, in file-name order: photos of a turn around the
     * cake are usually numbered (01.jpg, 02.jpg, …), so they spin in the order they were taken.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function addFrames(CakeDesign $design, array $files): void
    {
        usort($files, fn (UploadedFile $a, UploadedFile $b) => strnatcasecmp($a->getClientOriginalName(), $b->getClientOriginalName()));
        $position = (int) $design->frames()->max('position');
        $first = ! $design->frames()->exists();

        foreach ($files as $index => $file) {
            $design->frames()->create([
                'image' => $this->uploads->storeAsWebp($file, 'cake-designs'),
                'position' => $first && $index === 0 ? 0 : ++$position,
            ]);
        }
    }
}
