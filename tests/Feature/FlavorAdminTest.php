<?php

use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->vanilla = SpongeFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
    $this->chocolate = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true]);
    $this->redVelvet = SpongeFlavor::create(['name' => 'Red Velvet', 'extra_price' => 0, 'is_active' => true]);
    $this->offMenu = SpongeFlavor::create(['name' => 'Old', 'extra_price' => 0, 'is_active' => false]);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true]);

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

// The 2D texture_image is gone; the 3D cake's side_texture / top_texture are a separate feature (Cake3dTest).
test('the old 2D flavor textures are gone: no columns, no routes', function () {
    expect(Schema::hasColumn('sponge_flavors', 'texture_image'))->toBeFalse()
        ->and(Schema::hasColumn('cream_flavors', 'texture_image'))->toBeFalse()
        ->and(Route::has('admin.cake-visuals.texture.update'))->toBeFalse();
});

test('the cake images page walks through the photo, the default cake and the flavor pictures', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.cake-visuals.index'))
        ->assertOk()
        ->assertSeeInOrder(['Cake photo', 'Default cake', 'Sponges', 'Creams', 'Frosting and plate'])
        ->assertSee(route('admin.cake-photo.update'), false)
        ->assertSee('Upload photo');
});

test('removing a flavor picture deletes its file, but never a file outside the app folders', function (string $path, bool $deleted) {
    Storage::disk('public')->put('keep/me.png', 'x');
    Storage::disk('public')->put('cake-layers/x.png', 'x');
    $this->vanilla->update(['layer_image' => $path]);

    $this->actingAs($this->admin)
        ->delete(route('admin.cake-visuals.destroy', ['sponge', $this->vanilla->id]))
        ->assertRedirect();

    expect($this->vanilla->fresh()->layer_image)->toBeNull();
    Storage::disk('public')->assertExists('keep/me.png');
    $deleted ? Storage::disk('public')->assertMissing('cake-layers/x.png') : Storage::disk('public')->assertExists('cake-layers/x.png');
})->with([
    'own folder' => ['storage/cake-layers/x.png', true],
    'other folder' => ['storage/keep/me.png', false],
    'path traversal' => ['storage/cake-layers/../keep/me.png', false],
    'hand-placed public file' => ['images/me.png', false],
]);

test('admin can reorder flavors and the design page follows the new order', function () {
    $ids = [$this->redVelvet->id, $this->vanilla->id, $this->chocolate->id];

    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.reorder', 'sponge'), ['ids' => $ids])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(SpongeFlavor::where('is_active', true)->ordered()->pluck('id')->all())->toBe($ids);

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('sponges', fn ($sponges) => $sponges->pluck('id')->all() === $ids);
});

test('reorder rejects unknown, missing or inactive ids', function (array $ids) {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.reorder', 'sponge'), ['ids' => $ids])
        ->assertSessionHasErrors('ids');

    expect(SpongeFlavor::where('sort_order', '>', 0)->exists())->toBeFalse();
})->with([
    'unknown id' => fn () => [$this->vanilla->id, $this->chocolate->id, $this->redVelvet->id, 9999],
    'missing one' => fn () => [$this->vanilla->id, $this->chocolate->id],
    'inactive id' => fn () => [$this->vanilla->id, $this->chocolate->id, $this->redVelvet->id, $this->offMenu->id],
]);

test('reorder rejects duplicate ids', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.reorder', 'sponge'), [
            'ids' => [$this->vanilla->id, $this->vanilla->id, $this->chocolate->id],
        ])
        ->assertSessionHasErrors('ids.0');
});

test('customers cannot reorder flavors', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.cake-visuals.reorder', 'sponge'), ['ids' => [$this->vanilla->id]])
        ->assertForbidden();
});

test('the admin page lists flavors in their saved order', function () {
    $this->vanilla->update(['sort_order' => 3]);
    $this->chocolate->update(['sort_order' => 1]);
    $this->redVelvet->update(['sort_order' => 2]);

    $this->actingAs($this->admin)
        ->get(route('admin.cake-visuals.index'))
        ->assertOk()
        ->assertSeeInOrder(['Chocolate', 'Red Velvet', 'Vanilla']);
});
