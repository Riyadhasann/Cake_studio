<?php

use App\Models\CreamFlavor;
use App\Models\FlavorVariantImage;
use App\Models\SpongeFlavor;
use App\Models\User;
use App\Services\PublicUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->variant = completeVariant(2); // 700 × 392
    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'color' => '#4e2e20']);
    $this->cream = CreamFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
});

/** The file on the fake public disk behind a saved "storage/..." path. */
function storedFile(?string $path): string
{
    return substr((string) $path, strlen('storage/'));
}

function uploadPhoto(object $test, array $overrides = []): TestResponse
{
    return $test->actingAs($test->admin)->post(route('admin.cake-variants.photos.store', $test->variant), array_merge([
        'layer_no' => 1,
        'flavor_type' => 'sponge',
        'flavor_id' => $test->sponge->id,
        'photo' => UploadedFile::fake()->image('chocolate.jpg', 660, 140),
    ], $overrides));
}

test('admin can upload, replace and remove a shading image with the drawing\'s proportions', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.shading.update', $this->variant), ['shading' => UploadedFile::fake()->image('shade.png', 1400, 784)])
        ->assertSessionHasNoErrors();

    $first = $this->variant->fresh()->shading_image;
    expect($first)->toStartWith('storage/cake-shading/');
    Storage::disk('public')->assertExists(storedFile($first));

    $this->post(route('admin.cake-variants.shading.update', $this->variant), ['shading' => UploadedFile::fake()->image('shade.png', 700, 392)])
        ->assertSessionHasNoErrors();

    $second = $this->variant->fresh()->shading_image;
    Storage::disk('public')->assertMissing(storedFile($first));
    Storage::disk('public')->assertExists(storedFile($second));

    $this->delete(route('admin.cake-variants.shading.destroy', $this->variant))->assertSessionHasNoErrors();

    expect($this->variant->fresh()->shading_image)->toBeNull();
    Storage::disk('public')->assertMissing(storedFile($second));
});

test('a shading image with other proportions or format is rejected', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.shading.update', $this->variant), ['shading' => $file])
        ->assertSessionHasErrors('shading');

    expect($this->variant->fresh()->shading_image)->toBeNull();
})->with([
    'square' => fn () => UploadedFile::fake()->image('shade.png', 500, 500),
    'jpeg' => fn () => UploadedFile::fake()->image('shade.jpg', 700, 392),
    'too large' => fn () => UploadedFile::fake()->image('shade.png', 700, 392)->size(3000),
]);

test('admin can upload a flavor photo for a slot, and uploading again replaces it', function () {
    uploadPhoto($this)->assertSessionHasNoErrors();

    $first = FlavorVariantImage::sole();
    expect($first->only(['cake_variant_id', 'layer_no', 'flavor_type', 'flavor_id']))
        ->toBe(['cake_variant_id' => $this->variant->id, 'layer_no' => 1, 'flavor_type' => 'sponge', 'flavor_id' => $this->sponge->id])
        ->and($first->image)->toStartWith('storage/cake-photos/');

    uploadPhoto($this)->assertSessionHasNoErrors();

    $second = FlavorVariantImage::sole();
    expect($second->id)->toBe($first->id)->and($second->image)->not->toBe($first->image);
    Storage::disk('public')->assertMissing(storedFile($first->image));
    Storage::disk('public')->assertExists(storedFile($second->image));

    // Another layer or flavor is a separate photo.
    uploadPhoto($this, ['layer_no' => 2])->assertSessionHasNoErrors();
    uploadPhoto($this, ['flavor_type' => 'cream', 'flavor_id' => $this->cream->id])->assertSessionHasNoErrors();
    expect(FlavorVariantImage::count())->toBe(3);
});

test('flavor photo validation', function (array $input, string $field) {
    $input = is_callable($input['photo'] ?? null) ? [...$input, 'photo' => $input['photo']()] : $input;

    uploadPhoto($this, $input)->assertSessionHasErrors($field);

    expect(FlavorVariantImage::count())->toBe(0);
})->with([
    'layer above the cake' => [['layer_no' => 3], 'layer_no'],
    'layer zero' => [['layer_no' => 0], 'layer_no'],
    'unknown slot type' => [['flavor_type' => 'frosting'], 'flavor_type'],
    'unknown flavor' => [['flavor_id' => 999], 'flavor_id'],
    'not an image' => [['photo' => fn () => UploadedFile::fake()->create('photo.pdf', 10, 'application/pdf')], 'photo'],
    'too large' => [['photo' => fn () => UploadedFile::fake()->image('photo.jpg', 600, 140)->size(3000)], 'photo'],
]);

test('a cream id is checked against the creams, not the sponges', function () {
    $onlySponge = SpongeFlavor::create(['name' => 'Pandan', 'extra_price' => 0, 'is_active' => true]);
    CreamFlavor::whereKey($onlySponge->id)->delete();

    uploadPhoto($this, ['flavor_type' => 'cream', 'flavor_id' => $onlySponge->id])->assertSessionHasErrors('flavor_id');
});

test('admin can remove a photo, and only through its own drawing', function () {
    uploadPhoto($this);
    $photo = FlavorVariantImage::sole();
    $other = completeVariant(3);

    $this->delete(route('admin.cake-variants.photos.destroy', [$other, $photo]))->assertNotFound();
    $this->assertModelExists($photo);

    $this->delete(route('admin.cake-variants.photos.destroy', [$this->variant, $photo]))->assertSessionHasNoErrors();
    $this->assertModelMissing($photo);
    Storage::disk('public')->assertMissing(storedFile($photo->image));
});

test('the editor lists photos and shows the shading', function () {
    uploadPhoto($this);
    $this->variant->update(['shading_image' => 'storage/cake-shading/shade.png']);

    $this->get(route('admin.cake-variants.edit', $this->variant))
        ->assertOk()
        ->assertSee('Flavor photos')
        ->assertSee('Chocolate')
        ->assertSee(FlavorVariantImage::sole()->image)
        ->assertSee('storage/cake-shading/shade.png')
        ->assertSee('mix-blend-mode: multiply', false);
});

test('uploads are only ever deleted from the app\'s own folders', function () {
    Storage::disk('public')->put('cake-photos/a.png', 'x');
    Storage::disk('public')->put('elsewhere/b.png', 'x');

    $uploads = app(PublicUploads::class);
    $uploads->delete('storage/elsewhere/b.png');
    $uploads->delete('storage/cake-photos/../elsewhere/b.png');
    $uploads->delete('storage/cake-photos/a.png');

    Storage::disk('public')->assertExists('elsewhere/b.png');
    Storage::disk('public')->assertMissing('cake-photos/a.png');
    expect(fn () => $uploads->store(UploadedFile::fake()->image('x.png'), 'elsewhere'))->toThrow(InvalidArgumentException::class);
});

test('the design page gets the photos of active flavors and the shading of each drawing', function () {
    $this->variant->update(['is_published' => true]);
    uploadPhoto($this);
    uploadPhoto($this, ['flavor_type' => 'cream', 'flavor_id' => $this->cream->id, 'layer_no' => 2]);
    $this->post(route('admin.cake-variants.shading.update', $this->variant), ['shading' => UploadedFile::fake()->image('shade.png', 700, 392)]);
    $this->cream->update(['is_active' => false]); // its photo must not be sent
    $photo = FlavorVariantImage::where('flavor_type', 'sponge')->sole();

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('variants', function (array $variants) use ($photo) {
            expect(array_keys($variants[2]['photos']))->toBe(["sponge-{$this->sponge->id}-1"])
                ->and($variants[2]['photos']["sponge-{$this->sponge->id}-1"])->toBe(['id' => $photo->id, 'url' => asset($photo->image), 'width' => 660, 'height' => 140])
                ->and($variants[2]['shading'])->toMatchArray(['width' => 700, 'height' => 392]);

            return true;
        })
        ->assertSee('id="cake-photo-'.$photo->id.'"', false)
        ->assertSee('viewBox="0 0 660 140" preserveAspectRatio="xMidYMid slice"', false)
        ->assertSee('clip-path="url(#cake-slots-2)"', false)
        ->assertDontSee('url(#cake-shade-sponge)', false);
});

test('a layer image is cropped to the slot using its pixel size', function () {
    $this->variant->update(['is_published' => true]);
    Storage::disk('public')->put('cake-layers/choc.png', UploadedFile::fake()->image('choc.png', 1200, 255)->getContent());
    $this->sponge->update(['layer_image' => 'storage/cake-layers/choc.png']);

    $this->get(route('cake-studio.index'))
        ->assertViewHas('sponges', fn ($sponges) => $sponges->first()['image_size'] === [1200, 255])
        ->assertSee('<pattern id="cake-image-sponge-'.$this->sponge->id.'" patternUnits="objectBoundingBox" width="1" height="1" viewBox="0 0 1200 255"', false);
});

test('the flavor admin previews textures on the drawings', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.cake-visuals.index'))
        ->assertOk()
        ->assertSee('id="texture-preview-sponge-'.$this->sponge->id.'"', false)
        ->assertSee('The texture on a 2-layer cake');
});

test('customers cannot manage shading or photos', function () {
    uploadPhoto($this);
    $photo = FlavorVariantImage::sole();

    $this->actingAs(User::factory()->create());

    $this->post(route('admin.cake-variants.shading.update', $this->variant), ['shading' => UploadedFile::fake()->image('s.png', 700, 392)])->assertForbidden();
    $this->delete(route('admin.cake-variants.shading.destroy', $this->variant))->assertForbidden();
    $this->post(route('admin.cake-variants.photos.store', $this->variant), [])->assertForbidden();
    $this->delete(route('admin.cake-variants.photos.destroy', [$this->variant, $photo]))->assertForbidden();

    $this->assertModelExists($photo);
});
