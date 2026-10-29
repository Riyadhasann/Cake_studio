<?php

use App\Models\CakeSize;
use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\FlavorVariantImage;
use App\Models\SpongeFlavor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->size = CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 1800]);
    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true]);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true]);
});

function orderLayers(int $count): array
{
    return array_fill(0, $count, ['sponge_flavor_id' => test()->sponge->id, 'cream_flavor_id' => test()->cream->id]);
}

test('only 2, 3 and 4 layers are offered until the admin adds more', function () {
    expect(CakeVariant::offeredLayerCounts())->toBe([2, 3, 4]);

    $this->get(route('cake-studio.index'))->assertOk()->assertViewHas('layerOptions', [2, 3, 4]);

    $this->postJson(route('cart.add'), ['cake_size_id' => $this->size->id, 'layers' => orderLayers(5), 'quantity' => 1])
        ->assertJsonValidationErrors('layers');
});

test('the admin can add a layer count, which customers can then pick and order', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.cake-variants.index'))
        ->assertOk()
        ->assertSee('Add a layer count')
        ->assertSee('<option value="5"', false)
        ->assertSee('<option value="8"', false);

    $this->post(route('admin.cake-variants.store'), ['layers' => 6])->assertRedirect();

    expect(CakeVariant::offeredLayerCounts())->toBe([2, 3, 4, 6]);
    $this->get(route('cake-studio.index'))->assertViewHas('layerOptions', [2, 3, 4, 6]);

    $this->postJson(route('cart.add'), ['cake_size_id' => $this->size->id, 'layers' => orderLayers(6), 'quantity' => 1])->assertOk();

    // 5 was skipped, so a 5-layer cake is still not available.
    $this->postJson(route('cart.add'), ['cake_size_id' => $this->size->id, 'layers' => orderLayers(5), 'quantity' => 1])
        ->assertJsonValidationErrors(['layers' => 'A 5-layer cake is not available.']);
});

test('a layer count outside 2 to 8, or one that already exists, cannot be added', function () {
    $this->actingAs($this->admin);

    $this->post(route('admin.cake-variants.store'), ['layers' => 9])->assertSessionHasErrors('layers');
    $this->post(route('admin.cake-variants.store'), ['layers' => 1])->assertSessionHasErrors('layers');

    completeVariant(5);
    $this->post(route('admin.cake-variants.store'), ['layers' => 5])->assertSessionHasErrors('layers');
});

test('a new layer count gets the cake photo with its layers fitted and is published', function () {
    $photo = UploadedFile::fake()->image('cake.png', 700, 600)->store('cake-bases', 'public');
    $cutFace = ['top' => [[100, 100], [600, 100]], 'bottom' => [[100, 500], [600, 500]], 'sponge_share' => 0.7];
    CakeVariant::create(['layers' => 3, 'view_box_width' => 700, 'view_box_height' => 600, 'base_image' => 'storage/'.$photo, 'cut_face' => $cutFace]);

    $this->actingAs($this->admin)->post(route('admin.cake-variants.store'), ['layers' => 5])->assertRedirect(route('admin.cake-variants.index'));

    $five = CakeVariant::where('layers', 5)->with('slots')->firstOrFail();
    expect($five->base_image)->not->toBe('storage/'.$photo)
        ->and($five->base_image)->toStartWith('storage/cake-bases/')
        ->and($five->cut_face)->toBe($cutFace)
        ->and($five->slots)->toHaveCount(10)
        ->and($five->is_published)->toBeTrue();
    Storage::disk('public')->assertExists(substr($five->base_image, strlen('storage/')));
});

test('the admin can remove an added layer count, with its pictures, but not a built-in one', function () {
    $this->actingAs($this->admin);

    $six = completeVariant(6);
    $base = UploadedFile::fake()->image('cake.png')->store('cake-bases', 'public');
    $slotPhoto = UploadedFile::fake()->image('slot.png')->store('cake-photos', 'public');
    $six->update(['base_image' => 'storage/'.$base]);
    FlavorVariantImage::create(['cake_variant_id' => $six->id, 'flavor_type' => 'sponge', 'flavor_id' => $this->sponge->id, 'layer_no' => 1, 'image' => 'storage/'.$slotPhoto]);

    $this->delete(route('admin.cake-variants.destroy', $six))->assertRedirect(route('admin.cake-variants.index'));

    expect(CakeVariant::find($six->id))->toBeNull()
        ->and(CakeVariant::offeredLayerCounts())->toBe([2, 3, 4]);
    Storage::disk('public')->assertMissing($base);
    Storage::disk('public')->assertMissing($slotPhoto);

    $four = completeVariant(4);
    $this->delete(route('admin.cake-variants.destroy', $four))->assertForbidden();
    expect(CakeVariant::find($four->id))->not->toBeNull();
});
