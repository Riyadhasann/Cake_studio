<?php

use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->vanilla = SpongeFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
    $this->chocolate = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true]);
    CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true]);
});

test('the per-combination previews are retired', function () {
    expect(Schema::hasTable('cake_previews'))->toBeFalse();

    $this->actingAs($this->admin)->get('/admin/cake-previews')->assertNotFound();
});

test('textures, photos and shading are stored as WebP', function () {
    $variant = completeVariant(2);
    $this->actingAs($this->admin);

    $this->post(route('admin.cake-visuals.texture.update', ['sponge', $this->vanilla->id]), ['texture' => UploadedFile::fake()->image('t.png', 256, 256)]);
    $this->post(route('admin.cake-variants.photos.store', $variant), [
        'layer_no' => 1, 'flavor_type' => 'sponge', 'flavor_id' => $this->vanilla->id, 'photo' => UploadedFile::fake()->image('p.jpg', 660, 140),
    ]);
    $this->post(route('admin.cake-variants.shading.update', $variant), ['shading' => UploadedFile::fake()->image('s.png', 700, 392)]);

    $paths = [
        $this->vanilla->fresh()->texture_image,
        $variant->flavorImages()->sole()->image,
        $variant->fresh()->shading_image,
    ];

    foreach ($paths as $path) {
        expect($path)->toEndWith('.webp');
        $file = Storage::disk('public')->path(substr($path, strlen('storage/')));
        expect(getimagesize($file)[2])->toBe(IMAGETYPE_WEBP);
    }

    // The pixel size still reaches the design page, for cropping.
    $variant->update(['is_published' => true]);
    $this->get(route('cake-studio.index'))
        ->assertViewHas('variants', fn (array $variants) => array_values($variants[2]['photos'])[0]['width'] === 660);
});

test('very large photos are kept as uploaded instead of converted', function () {
    $variant = completeVariant(2);

    $this->actingAs($this->admin)->post(route('admin.cake-variants.photos.store', $variant), [
        'layer_no' => 1, 'flavor_type' => 'sponge', 'flavor_id' => $this->vanilla->id, 'photo' => UploadedFile::fake()->image('big.jpg', 2100, 2100),
    ])->assertSessionHasNoErrors();

    expect($variant->flavorImages()->sole()->image)->toEndWith('.jpg');
});

test('after moving a flavor, focus goes back to the arrow that was pressed', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.cake-visuals.index'))
        ->post(route('admin.cake-visuals.reorder', 'sponge'), [
            'ids' => [$this->chocolate->id, $this->vanilla->id],
            'focus' => "sponge-{$this->chocolate->id}-up",
        ])
        ->assertSessionHas('focus', "sponge-{$this->chocolate->id}-up");

    // Chocolate is now first, so it has no "up" arrow: its "down" arrow gets the focus.
    $html = $this->get(route('admin.cake-visuals.index'))->getContent();
    $autofocused = fn (string $label) => preg_match('/aria-label="'.preg_quote($label, '/').'"[^>]*\sautofocus\s*>/', $html) === 1;

    expect($autofocused('Move Chocolate down'))->toBeTrue()
        ->and($autofocused('Move Vanilla up'))->toBeFalse()
        ->and(substr_count($html, 'autofocus'))->toBe(1);
});

test('a malformed focus value is ignored', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.reorder', 'sponge'), [
            'ids' => [$this->chocolate->id, $this->vanilla->id],
            'focus' => '"><script>alert(1)</script>',
        ])
        ->assertSessionMissing('focus');
});

test('the design page labels its pickers and its dialog', function () {
    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertSee('role="group" aria-labelledby="size-heading"', false)
        ->assertSee(':aria-label="layerTitle(index) + \': sponge\'"', false)
        ->assertSee(':aria-label="layerTitle(index) + \': cream\'"', false)
        ->assertSee('aria-labelledby="confirm-title"', false)
        ->assertSee('x-ref="confirmButton"', false);
});

test('the admin pages still render for every drawing', function () {
    foreach (CakeVariant::LAYER_COUNTS as $layers) {
        $variant = completeVariant($layers);
        $this->actingAs($this->admin)->get(route('admin.cake-variants.edit', $variant))->assertOk();
    }

    $this->get(route('admin.cake-variants.index'))->assertOk();
    $this->get(route('admin.cake-visuals.index'))->assertOk();
    $this->get(route('admin.sizes.index'))->assertOk();
    $this->get(route('admin.orders.index'))->assertOk();
});
