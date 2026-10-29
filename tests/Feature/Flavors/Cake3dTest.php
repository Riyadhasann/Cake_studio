<?php

use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use App\Services\Images\SeamlessTexture;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'color' => '#6b3e26']);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true, 'color' => '#fbefd5']);
    CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 1800]);

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

/** A PNG that runs from black on the left to white on the right, like a photo lit from one side: tiled as is, it shows a hard seam. */
function sideLitPng(int $width, int $height): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);
    for ($x = 0; $x < $width; $x++) {
        $v = (int) round(255 * $x / ($width - 1));
        imageline($image, $x, 0, $x, $height - 1, ($v << 16) | ($v << 8) | $v);
    }
    ob_start();
    imagepng($image);
    $png = (string) ob_get_clean();

    return UploadedFile::fake()->createWithContent('crumb.png', $png);
}

function savedImage(?string $path): GdImage
{
    return imagecreatefromstring(Storage::disk('public')->get(substr((string) $path, strlen('storage/'))));
}

function gray(GdImage $image, int $x, int $y): int
{
    return (imagecolorat($image, $x, $y) >> 8) & 0xFF;
}

test('admin saves a side picture: a square, seamless WebP in cake-textures, with the grain size', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.surface.update', ['sponge', $this->sponge->id]), [
            'side' => sideLitPng(400, 300),
            'tile_scale' => 1.5,
            'seamless' => 1,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $sponge = $this->sponge->fresh();
    expect($sponge->side_texture)->toStartWith('storage/cake-textures/')->toEndWith('.webp')
        ->and((float) $sponge->tile_scale)->toBe(1.5);

    $image = savedImage($sponge->side_texture);
    expect([imagesx($image), imagesy($image)])->toBe([300, 300]);

    // The left and right edges now meet (before, about 170 apart after the square crop).
    foreach ([10, 150, 290] as $y) {
        expect(abs(gray($image, 0, $y) - gray($image, 299, $y)))->toBeLessThan(40);
    }
});

test('a wide strip is kept whole instead of being cropped square', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.surface.update', ['cream', $this->cream->id]), [
            'side' => UploadedFile::fake()->image('strip.png', 2100, 300),
            'tile_scale' => 1,
            'seamless' => 1,
        ])
        ->assertSessionHasNoErrors();

    $image = savedImage($this->cream->fresh()->side_texture);
    expect([imagesx($image), imagesy($image)])->toBe([2100, 300]);
});

test('creams can have a top picture, sponges cannot', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.surface.update', ['cream', $this->cream->id]), [
            'top' => UploadedFile::fake()->image('top.jpg', 600, 400),
            'tile_scale' => 1,
        ])
        ->assertSessionHasNoErrors();

    $top = savedImage($this->cream->fresh()->top_texture);
    expect([imagesx($top), imagesy($top)])->toBe([400, 400]);

    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.surface.update', ['sponge', $this->sponge->id]), [
            'top' => UploadedFile::fake()->image('top.jpg', 600, 400),
            'tile_scale' => 1,
        ])
        ->assertSessionHasErrors('top');
});

test('pictures that are too small, the wrong type or a grain size out of range are refused', function (array $input, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.surface.update', ['sponge', $this->sponge->id]), $input + ['tile_scale' => 1])
        ->assertSessionHasErrors($field);

    expect($this->sponge->fresh()->side_texture)->toBeNull();
})->with([
    'too small' => [['side' => UploadedFile::fake()->image('tiny.png', 100, 100)], 'side'],
    'not an image' => [['side' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')], 'side'],
    'grain too fine' => [['tile_scale' => 0.1], 'tile_scale'],
    'grain too coarse' => [['tile_scale' => 9], 'tile_scale'],
]);

test('replacing or removing the 3D pictures deletes the old files', function () {
    Storage::disk('public')->put('cake-textures/old-side.webp', 'x');
    Storage::disk('public')->put('cake-textures/old-top.webp', 'x');
    $this->cream->update(['side_texture' => 'storage/cake-textures/old-side.webp', 'top_texture' => 'storage/cake-textures/old-top.webp']);

    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.surface.update', ['cream', $this->cream->id]), [
            'side' => UploadedFile::fake()->image('new.png', 300, 300),
            'tile_scale' => 1,
        ])
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('cake-textures/old-side.webp');
    Storage::disk('public')->assertExists('cake-textures/old-top.webp'); // no new top picture, so the old one stays
    $newSide = substr((string) $this->cream->fresh()->side_texture, strlen('storage/'));

    $this->actingAs($this->admin)
        ->delete(route('admin.cake-visuals.surface.destroy', ['cream', $this->cream->id]))
        ->assertRedirect();

    $cream = $this->cream->fresh();
    expect($cream->side_texture)->toBeNull()->and($cream->top_texture)->toBeNull();
    Storage::disk('public')->assertMissing($newSide);
    Storage::disk('public')->assertMissing('cake-textures/old-top.webp');
});

test('only admins can change the 3D look', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->post(route('admin.cake-visuals.surface.update', ['sponge', $this->sponge->id]), ['tile_scale' => 2])
        ->assertForbidden();

    expect((float) $this->sponge->fresh()->tile_scale)->toBe(1.0);
});

test('the cake images page offers the 3D look and its live preview for every flavor', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.cake-visuals.index'))
        ->assertOk()
        ->assertSee(route('admin.cake-visuals.surface.update', ['sponge', $this->sponge->id]), false)
        ->assertSee(route('admin.cake-visuals.surface.update', ['cream', $this->cream->id]), false)
        ->assertSee('Preview in 3D')
        ->assertSee('cakeSurfacePreview', false);
});

test('the design page gets each flavor’s 3D pictures and offers the 3D view', function () {
    $this->sponge->update(['side_texture' => 'storage/cake-textures/crumb.webp', 'tile_scale' => 0.75]);
    $this->cream->update(['top_texture' => 'storage/cake-textures/top.webp']);

    $response = $this->get(route('cake-studio.index'))->assertOk();

    expect($response->viewData('sponges')->first()['surface'])->toBe([
        'side' => asset('storage/cake-textures/crumb.webp'),
        'top' => null,
        'scale' => 0.75,
    ])->and($response->viewData('creams')->first()['surface'])->toBe([
        'side' => null,
        'top' => asset('storage/cake-textures/top.webp'),
        'scale' => 1.0,
    ]);

    $response->assertSee('show3d()', false)->assertSee('x-ref="cake3d"', false);
});

test('a photo that GD cannot read is not turned into a texture', function () {
    expect(app(SeamlessTexture::class)->make('not an image'))->toBeNull();
});
