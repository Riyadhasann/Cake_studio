<?php

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use App\Services\CutFaceFinder;
use App\Services\LayerStrips;
use App\Services\SvgSanitizer;
use Database\Seeders\CakePhotoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->variant = completeVariant(2); // 700 × 392
    $this->variant->update(['is_published' => true]);
});

/** The file on the fake public disk behind a saved "storage/..." path. */
function diskPath(?string $path): string
{
    return substr((string) $path, strlen('storage/'));
}

function cutFace(): array
{
    return CakePhotoSeeder::CUT_FACE;
}

test('admin can upload a cake photo: the view box becomes its size and the drawing waits for the cut face', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.base.update', $this->variant), ['base' => UploadedFile::fake()->image('cake.png', 1180, 1020)])
        ->assertRedirect()
        ->assertSessionHas('warning');

    $variant = $this->variant->fresh();

    expect($variant->base_image)->toStartWith('storage/cake-bases/')
        ->and($variant->base_image)->toEndWith('.webp')
        ->and([$variant->view_box_width, $variant->view_box_height])->toBe([1180, 1020])
        ->and($variant->is_published)->toBeFalse()
        ->and($variant->cut_face)->toBeNull();
    Storage::disk('public')->assertExists(diskPath($variant->base_image));
});

test('one upload can be used for every layer count, each with its own file', function () {
    $three = completeVariant(3);
    $four = completeVariant(4);

    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.base.update', $this->variant), [
            'base' => UploadedFile::fake()->image('cake.png', 1180, 1020),
            'all_layers' => '1',
        ])
        ->assertRedirect();

    $paths = CakeVariant::orderBy('layers')->pluck('base_image');

    expect($paths)->toHaveCount(3)
        ->and($paths->filter()->unique())->toHaveCount(3)
        ->and($three->fresh()->view_box_width)->toBe(1180)
        ->and($four->fresh()->view_box_height)->toBe(1020);
});

test('replacing the photo with one of the same size keeps the cut face and the publish state', function () {
    $this->variant->update(['base_image' => 'storage/cake-bases/old.webp', 'view_box_width' => 1180, 'view_box_height' => 1020, 'cut_face' => cutFace()]);
    Storage::disk('public')->put('cake-bases/old.webp', 'old');

    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.base.update', $this->variant), ['base' => UploadedFile::fake()->image('cake.png', 1180, 1020)])
        ->assertSessionHas('warning'); // a blank test image has no cut to find, so the marked one is kept

    $variant = $this->variant->fresh();

    expect($variant->cut_face)->toBe(cutFace())
        ->and($variant->is_published)->toBeTrue()
        ->and($variant->base_image)->not->toBe('storage/cake-bases/old.webp');
    Storage::disk('public')->assertMissing('cake-bases/old.webp');
});

test('removing the photo deletes its file and unpublishes the drawing', function () {
    $this->variant->update(['base_image' => 'storage/cake-bases/cake.webp', 'cut_face' => cutFace()]);
    Storage::disk('public')->put('cake-bases/cake.webp', 'photo');

    $this->actingAs($this->admin)
        ->delete(route('admin.cake-variants.base.destroy', $this->variant))
        ->assertRedirect();

    $variant = $this->variant->fresh();

    expect($variant->base_image)->toBeNull()
        ->and($variant->cut_face)->toBeNull()
        ->and($variant->is_published)->toBeFalse();
    Storage::disk('public')->assertMissing('cake-bases/cake.webp');
});

test('the cake photo must be an image of a sensible size', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.base.update', $this->variant), ['base' => $file])
        ->assertSessionHasErrors('base');

    expect($this->variant->fresh()->base_image)->toBeNull();
})->with([
    'not an image' => fn () => UploadedFile::fake()->create('cake.pdf', 100, 'application/pdf'),
    'svg' => fn () => UploadedFile::fake()->createWithContent('cake.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'),
    'too small' => fn () => UploadedFile::fake()->image('cake.png', 100, 100),
    'too large a file' => fn () => UploadedFile::fake()->image('cake.png', 1200, 1000)->size(5000),
]);

test('customers cannot change the cake photo', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.cake-variants.base.update', $this->variant), ['base' => UploadedFile::fake()->image('cake.png', 1180, 1020)])
        ->assertForbidden();

    $this->delete(route('admin.cake-variants.base.destroy', $this->variant))->assertForbidden();
});

test('the cut face fills with one sponge and one cream band per layer, top to bottom', function () {
    $paths = CakeVariantSlot::cutFacePaths(cutFace(), 3);
    $sanitizer = app(SvgSanitizer::class);

    expect(array_keys($paths))->toBe([1, 2, 3]);

    foreach ($paths as $bands) {
        expect(array_keys($bands))->toBe(['sponge', 'cream'])
            ->and($sanitizer->isValidPath($bands['sponge']))->toBeTrue()
            ->and($sanitizer->isValidPath($bands['cream']))->toBeTrue();
    }

    // The top cream starts on the top edge; the bottom sponge ends on the bottom edge.
    expect($paths[3]['cream'])->toStartWith('M218 371L555 219L901 374L')
        ->and($paths[1]['sponge'])->toContain('L904 789L555 647L221 801Z');
});

test('saving the editor keeps the photo size and stores the cut face', function () {
    $this->variant->update(['base_image' => 'storage/cake-bases/cake.webp', 'view_box_width' => 1180, 'view_box_height' => 1020]);
    $bands = CakeVariantSlot::cutFacePaths(cutFace(), 2);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->variant), [
            'view_box_width' => 700,
            'view_box_height' => 392,
            'cut_face' => cutFace(),
            'slots' => array_map(fn ($types) => array_map(fn ($path) => ['mode' => 'path', 'path' => $path], $types), $bands),
        ])
        ->assertSessionHasNoErrors();

    $variant = $this->variant->fresh('slots');

    expect([$variant->view_box_width, $variant->view_box_height])->toBe([1180, 1020])
        ->and($variant->cut_face['top'])->toEqual(cutFace()['top'])
        ->and($variant->cut_face['sponge_share'])->toBe(0.72)
        ->and($variant->slots->firstWhere('layer_no', 2)->svg_path)->toBe($bands[2]['cream'])
        ->and($variant->canPublish())->toBeTrue();
});

test('a cut face needs three points on each edge', function () {
    $cut = cutFace();
    array_pop($cut['bottom']);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->variant), [
            'view_box_width' => 700, 'view_box_height' => 392, 'cut_face' => $cut, 'slots' => [],
        ])
        ->assertSessionHasErrors('cut_face.bottom');
});

test('the editor shows the photo and suggests the cut face marked on another drawing of the same photo', function () {
    $this->variant->update(['base_image' => 'storage/cake-bases/two.webp', 'view_box_width' => 1180, 'view_box_height' => 1020, 'cut_face' => cutFace()]);
    $three = completeVariant(3);
    $three->update(['base_image' => 'storage/cake-bases/three.webp', 'view_box_width' => 1180, 'view_box_height' => 1020]);

    $this->actingAs($this->admin)
        ->get(route('admin.cake-variants.edit', $three))
        ->assertOk()
        ->assertSee('The cut')
        ->assertSee(asset('storage/cake-bases/three.webp'), false)
        ->assertViewHas('cutFace', fn ($cut) => $cut['top'] === cutFace()['top']);
});

test('the seeder puts the photo on bare drawings, fits the cut and publishes them', function () {
    $drawn = completeVariant(3);
    $drawn->update(['base_svg' => '<path d="M0 0H10"/>']);

    $this->seed(CakePhotoSeeder::class);

    $variant = $this->variant->fresh('slots');

    expect($variant->base_image)->toStartWith('storage/cake-bases/')
        ->and([$variant->view_box_width, $variant->view_box_height])->toBe([1180, 1020])
        ->and($variant->is_published)->toBeTrue()
        ->and($variant->slots)->toHaveCount(4)
        ->and($variant->slots->firstWhere(fn ($slot) => $slot->layer_no === 2 && $slot->type === 'cream')->svg_path)
        ->toBe(CakeVariantSlot::cutFacePaths(cutFace(), 2)[2]['cream'])
        // A drawing the admin worked on is left alone.
        ->and($drawn->fresh()->base_image)->toBeNull();
    Storage::disk('public')->assertExists(diskPath($variant->base_image));
});

test('customers see the photo with the default sponge and cream in every layer', function () {
    $chocolate = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'is_default' => true]);
    $butter = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true, 'is_default' => true]);
    $this->seed(CakePhotoSeeder::class);
    $url = asset(CakeVariant::where('layers', 2)->value('base_image'));

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('variants', fn (array $variants) => $variants[2]['base_image']['url'] === $url && $variants[2]['width'] === 1180)
        ->assertViewHas('defaultSpongeId', $chocolate->id)
        ->assertViewHas('defaultCreamId', $butter->id)
        ->assertSee('<image href="'.$url.'"', false)
        ->assertSee('cake-photo', false)
        ->assertSee("slotFill(1, 'sponge')", false)
        ->assertSee("slotFill(2, 'cream')", false);
});

/**
 * A PNG with an opaque V-shaped strip on a transparent background, like a slice of the cut: its top edge runs
 * from (0, 65) up to (100, 5) and down to (200, 65), 30 px thick. A ragged one has a wavy, crumbly top edge
 * around that line, so its thickness varies.
 */
function vStripPng(int $width = 200, int $height = 100, int $thickness = 30, bool $ragged = false): string
{
    $image = imagecreatetruecolor($width, $height);
    imagesavealpha($image, true);
    imagealphablending($image, false);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    $brown = imagecolorallocatealpha($image, 80, 40, 30, 0);
    $apex = intdiv($width, 2);
    $rise = $height - $thickness - 10;

    foreach (range(0, $width - 1) as $x) {
        $line = 5 + $rise * abs($x - $apex) / $apex;
        $top = (int) round($line + ($ragged ? 4 * sin($x / 3) : 0));
        imageline($image, $x, max(0, $top), $x, (int) round($line) + $thickness - 1, $brown);
    }

    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

test('a layer image with a V-shaped strip is measured by its average edges', function (bool $ragged) {
    Storage::disk('public')->put('cake-layers/v.png', vStripPng(ragged: $ragged));

    $strip = app(LayerStrips::class)->of('storage/cake-layers/v.png');
    $near = fn (array $point, array $expected, float $by) => abs($point[0] - $expected[0]) <= $by && abs($point[1] - $expected[1]) <= $by;

    expect($strip['width'])->toBe(200)
        ->and($strip['height'])->toBe(100)
        ->and($near($strip['top'][1], [100, 5], 3))->toBeTrue()
        ->and($near($strip['top'][0], [0, 65], 3))->toBeTrue()
        ->and($near($strip['bottom'][1], [100, 35], 3))->toBeTrue()
        ->and($near($strip['bottom'][2], [200, 95], 3))->toBeTrue();
})->with(['clean edges' => false, 'wavy, ragged top edge' => true]);

test('images without a strip across them, or not raster, are not mapped', function () {
    $image = imagecreatetruecolor(100, 100);
    imagesavealpha($image, true);
    imagealphablending($image, false);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagefilledrectangle($image, 0, 0, 30, 99, imagecolorallocate($image, 200, 0, 0)); // nothing in the right part
    ob_start();
    imagepng($image);
    Storage::disk('public')->put('cake-layers/half.png', (string) ob_get_clean());
    Storage::disk('public')->put('cake-layers/x.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"/>');

    expect(app(LayerStrips::class)->of('storage/cake-layers/half.png'))->toBeNull()
        ->and(app(LayerStrips::class)->of('storage/cake-layers/x.svg'))->toBeNull()
        ->and(app(LayerStrips::class)->of('storage/cake-layers/missing.png'))->toBeNull();
});

test('fitted bands give their six corners; other shapes give none', function () {
    $slot = new CakeVariantSlot(['svg_path' => CakeVariantSlot::cutFacePaths(cutFace(), 2)[2]['cream']]);

    expect($slot->bandPoints())->toHaveCount(6)
        ->and($slot->bandPoints()[1][0])->toBe(555.0)
        ->and((new CakeVariantSlot(['svg_path' => 'M0 0H10V10H0Z']))->bandPoints())->toBeNull();
});

test('the design page maps a strip layer image onto the bands of the photo', function () {
    Storage::disk('public')->put('cake-layers/v.png', vStripPng());
    SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'layer_image' => 'storage/cake-layers/v.png']);
    $this->seed(CakePhotoSeeder::class);

    $this->get(route('cake-studio.index'))
        ->assertViewHas('sponges', fn ($sponges) => abs($sponges->first()['strip']['top'][1][1] - 5) <= 3)
        ->assertViewHas('variants', fn (array $variants) => count($variants[2]['slots'][0]['band']) === 6)
        // Clipped only by the outline of the cut, so the art keeps its own edges
        ->assertSee('clip-path="url(#cake-cut-2-0)"', false)
        // Drawn from the top of the cake down, so each layer's top edge lies over the layer above
        ->assertSeeInOrder(["armTransform(2, 'cream', 0,", "armTransform(2, 'sponge', 0,", "armTransform(1, 'cream', 0,", "armTransform(1, 'sponge', 0,"], false);
});

/**
 * A cake photo: an opaque cake with an empty V-shaped cut (see-through, or a painted gray checkerboard) inside it.
 * The cut's corners are top (150, 200) (300, 120) (450, 200) and bottom (150, 400) (300, 320) (450, 400).
 */
function cakePhotoPng(bool $checkerboard = false): UploadedFile
{
    $image = imagecreatetruecolor(600, 500);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocate($image, 120, 70, 50));
    $cut = [150, 200, 300, 120, 450, 200, 450, 400, 300, 320, 150, 400];

    if ($checkerboard) {
        $mask = imagecreatetruecolor(600, 500);
        imagefilledpolygon($mask, $cut, imagecolorallocate($mask, 255, 255, 255));
        [$light, $gray] = [imagecolorallocate($image, 255, 255, 255), imagecolorallocate($image, 214, 214, 214)];

        foreach (range(0, 599) as $x) {
            foreach (range(0, 499) as $y) {
                if (imagecolorat($mask, $x, $y) !== 0) {
                    imagesetpixel($image, $x, $y, (intdiv($x, 20) + intdiv($y, 20)) % 2 ? $gray : $light);
                }
            }
        }
    } else {
        imagefilledpolygon($image, $cut, imagecolorallocatealpha($image, 0, 0, 0, 127));
    }

    $file = tempnam(sys_get_temp_dir(), 'cake').'.png';
    imagepng($image, $file);

    return new UploadedFile($file, 'cake.png', 'image/png', null, true);
}

test('the cut is found in a photo, see-through or painted as a checkerboard', function (bool $checkerboard) {
    $cut = app(CutFaceFinder::class)->find(cakePhotoPng($checkerboard)->getRealPath());

    $near = fn (array $points, array $expected) => collect($points)->every(
        fn ($point, $i) => abs($point[0] - $expected[$i][0]) <= 6 && abs($point[1] - $expected[$i][1]) <= 6,
    );

    expect($cut)->not->toBeNull()
        ->and($near($cut['top'], [[150, 200], [300, 120], [450, 200]]))->toBeTrue()
        ->and($near($cut['bottom'], [[150, 400], [300, 320], [450, 400]]))->toBeTrue();
})->with(['see-through' => false, 'checkerboard' => true]);

test('a photo with no empty cut has none to find', function () {
    expect(app(CutFaceFinder::class)->find(UploadedFile::fake()->image('cake.png', 600, 500)->getRealPath()))->toBeNull();
});

test('admin uploads the cake photo once on the cake images page and customers see it for every layer count', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-photo.update'), ['base' => cakePhotoPng()])
        ->assertRedirect()
        ->assertSessionHas('success')
        ->assertSessionMissing('warning');

    $variants = CakeVariant::with('slots')->orderBy('layers')->get();

    expect($variants->pluck('layers')->all())->toBe([2, 3, 4]);

    foreach ($variants as $variant) {
        expect($variant->base_image)->toStartWith('storage/cake-bases/')
            ->and([$variant->view_box_width, $variant->view_box_height])->toBe([600, 500])
            ->and($variant->slots)->toHaveCount(2 * $variant->layers)
            ->and($variant->is_published)->toBeTrue();
    }

    $this->get(route('cake-studio.index'))
        ->assertViewHas('variants', fn (array $drawings) => array_keys($drawings) === [2, 3, 4] && $drawings[4]['base_image'] !== null);

    $this->get(route('admin.cake-visuals.index'))
        ->assertSee('Customers see this photo now');
});

test('a photo whose cut cannot be found is saved but not shown, with a warning', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-photo.update'), ['base' => UploadedFile::fake()->image('cake.png', 600, 500)])
        ->assertSessionHas('warning');

    expect(CakeVariant::whereNotNull('base_image')->count())->toBe(3)
        ->and(CakeVariant::where('is_published', true)->exists())->toBeFalse();
});

test('admin can remove the cake photo from the cake images page', function () {
    $this->actingAs($this->admin)->post(route('admin.cake-photo.update'), ['base' => cakePhotoPng()]);
    $paths = CakeVariant::pluck('base_image');

    $this->delete(route('admin.cake-photo.destroy'))->assertRedirect();

    expect(CakeVariant::whereNotNull('base_image')->exists())->toBeFalse()
        ->and(CakeVariant::where('is_published', true)->exists())->toBeFalse();

    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing(diskPath($path));
    }
});

test('customers cannot change the cake photo from the cake images page', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.cake-photo.update'), ['base' => cakePhotoPng()])
        ->assertForbidden();

    $this->delete(route('admin.cake-photo.destroy'))->assertForbidden();
});
