<?php

use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

test('admin can upload a texture, and replacing it deletes the old file', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.texture.update', ['sponge', $this->vanilla->id]), [
            'texture' => UploadedFile::fake()->image('first.png', 512, 512),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $first = $this->vanilla->fresh()->texture_image;
    expect($first)->toStartWith('storage/cake-textures/');
    Storage::disk('public')->assertExists(substr($first, strlen('storage/')));

    $this->post(route('admin.cake-visuals.texture.update', ['sponge', $this->vanilla->id]), [
        'texture' => UploadedFile::fake()->image('second.webp', 256, 256),
    ])->assertSessionHasNoErrors();

    $second = $this->vanilla->fresh()->texture_image;
    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing(substr($first, strlen('storage/')));
    Storage::disk('public')->assertExists(substr($second, strlen('storage/')));
});

test('texture must be a square png or webp between 256 and 1024 px', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.texture.update', ['cream', $this->cream->id]), ['texture' => $file])
        ->assertSessionHasErrors('texture');

    expect($this->cream->fresh()->texture_image)->toBeNull();
})->with([
    'jpg' => fn () => UploadedFile::fake()->image('t.jpg', 512, 512),
    'not square' => fn () => UploadedFile::fake()->image('t.png', 512, 256),
    'too small' => fn () => UploadedFile::fake()->image('t.png', 128, 128),
    'too large' => fn () => UploadedFile::fake()->image('t.png', 2048, 2048),
    'too heavy' => fn () => UploadedFile::fake()->image('t.png', 512, 512)->size(3000),
]);

test('removing a texture deletes the file under cake-textures', function () {
    Storage::disk('public')->put('cake-textures/old.png', 'x');
    $this->vanilla->update(['texture_image' => 'storage/cake-textures/old.png']);

    $this->actingAs($this->admin)
        ->delete(route('admin.cake-visuals.texture.destroy', ['sponge', $this->vanilla->id]))
        ->assertRedirect();

    expect($this->vanilla->fresh()->texture_image)->toBeNull();
    Storage::disk('public')->assertMissing('cake-textures/old.png');
});

test('files outside the app prefixes are never deleted', function (string $path) {
    Storage::disk('public')->put('keep/me.png', 'x');
    Storage::disk('public')->put('cake-textures/x.png', 'x');
    $this->vanilla->update(['texture_image' => $path]);

    $this->actingAs($this->admin)
        ->delete(route('admin.cake-visuals.texture.destroy', ['sponge', $this->vanilla->id]))
        ->assertRedirect();

    expect($this->vanilla->fresh()->texture_image)->toBeNull();
    Storage::disk('public')->assertExists('keep/me.png');
})->with([
    'other folder' => 'storage/keep/me.png',
    'path traversal' => 'storage/cake-textures/../keep/me.png',
    'hand-placed public file' => 'images/me.png',
]);

test('customers cannot upload or remove textures', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->post(route('admin.cake-visuals.texture.update', ['sponge', $this->vanilla->id]), [
            'texture' => UploadedFile::fake()->image('t.png', 512, 512),
        ])
        ->assertForbidden();

    $this->actingAs($customer)
        ->delete(route('admin.cake-visuals.texture.destroy', ['sponge', $this->vanilla->id]))
        ->assertForbidden();
});

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
