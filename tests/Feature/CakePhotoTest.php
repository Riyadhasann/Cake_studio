<?php

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
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
        ->assertSessionMissing('warning');

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
        ->assertSee('Cut face')
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

/** A PNG with an opaque V-shaped strip of the given thickness on a transparent background, like a slice of the cut. */
function vStripPng(int $width = 200, int $height = 100, int $thickness = 30): string
{
    $image = imagecreatetruecolor($width, $height);
    imagesavealpha($image, true);
    imagealphablending($image, false);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    $brown = imagecolorallocatealpha($image, 80, 40, 30, 0);
    $apex = intdiv($width, 2);
    $rise = $height - $thickness - 10;

    foreach (range(0, $width - 1) as $x) {
        $top = 5 + (int) round($rise * abs($x - $apex) / $apex);
        imageline($image, $x, $top, $x, $top + $thickness - 1, $brown);
    }

    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

test('a layer image with a V-shaped strip is measured for mapping onto the bands', function () {
    Storage::disk('public')->put('cake-layers/v.png', vStripPng());

    $strip = app(LayerStrips::class)->of('storage/cake-layers/v.png');

    expect($strip['width'])->toBe(200)
        ->and($strip['height'])->toBe(100)
        ->and($strip['top'][1])->toBe([100, 5])
        ->and($strip['top'][0][1])->toBe(65)
        ->and($strip['bottom'][1])->toBe([100, 35])
        ->and($strip['bottom'][2])->toBe([200, 94]);
});

test('images that are not one even strip, or not raster, are not mapped', function () {
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
        ->assertViewHas('sponges', fn ($sponges) => $sponges->first()['strip']['top'][1] === [100, 5])
        ->assertViewHas('variants', fn (array $variants) => count($variants[2]['slots'][0]['band']) === 6)
        ->assertSee('cake-arm-2-1-sponge-0', false)
        ->assertSee("armTransform(1, 'sponge', 0,", false);
});
