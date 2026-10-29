<?php

use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use Database\Seeders\CakeVariantSeeder;
use Database\Seeders\CreamFlavorSeeder;
use Database\Seeders\SpongeFlavorSeeder;

beforeEach(function () {
    $this->vanillaSponge = SpongeFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
    $this->chocolateSponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true]);
    $this->butterCream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true]);
    $this->vanillaCream = CreamFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

test('admin can set the default sponge and cream, one per type', function () {
    $this->actingAs($this->admin);

    $this->post(route('admin.cake-visuals.default', ['sponge', $this->vanillaSponge->id]))->assertRedirect();
    $this->post(route('admin.cake-visuals.default', ['sponge', $this->chocolateSponge->id]))->assertRedirect();
    $this->post(route('admin.cake-visuals.default', ['cream', $this->vanillaCream->id]))->assertRedirect();

    expect(SpongeFlavor::where('is_default', true)->pluck('id')->all())->toBe([$this->chocolateSponge->id])
        ->and(CreamFlavor::where('is_default', true)->pluck('id')->all())->toBe([$this->vanillaCream->id]);
});

test('a flavor that is off the menu cannot be the default', function () {
    $this->vanillaCream->update(['is_active' => false]);

    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.default', ['cream', $this->vanillaCream->id]))
        ->assertSessionHasErrors('default');

    expect($this->vanillaCream->fresh()->is_default)->toBeFalse();
});

test('customers cannot change the default', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.cake-visuals.default', ['sponge', $this->chocolateSponge->id]))
        ->assertForbidden();
});

test('the design page starts every layer with the admin defaults', function () {
    $this->chocolateSponge->update(['is_default' => true]);
    $this->vanillaCream->update(['is_default' => true]);

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('defaultSpongeId', $this->chocolateSponge->id)
        ->assertViewHas('defaultCreamId', $this->vanillaCream->id);
});

test('the design page falls back to the first flavor when no default is set', function () {
    $this->get(route('cake-studio.index'))
        ->assertViewHas('defaultSpongeId', $this->vanillaSponge->id)
        ->assertViewHas('defaultCreamId', $this->butterCream->id);
});

test('the seeded design page opens on chocolate sponge and butter cream', function () {
    SpongeFlavor::query()->delete();
    CreamFlavor::query()->delete();

    $this->seed([SpongeFlavorSeeder::class, CreamFlavorSeeder::class]);

    $chocolate = SpongeFlavor::where('name', 'Chocolate')->sole();
    $butter = CreamFlavor::where('name', 'Butter Cream')->sole();

    expect(SpongeFlavor::where('is_default', true)->pluck('id')->all())->toBe([$chocolate->id])
        ->and(CreamFlavor::where('is_default', true)->pluck('id')->all())->toBe([$butter->id]);

    $this->get(route('cake-studio.index'))
        ->assertViewHas('defaultSpongeId', $chocolate->id)
        ->assertViewHas('defaultCreamId', $butter->id)
        ->assertSee('defaultSpongeId: '.$chocolate->id, false)
        ->assertSee('defaultCreamId: '.$butter->id, false);
});

test('reseeding keeps the default the admin picked', function () {
    SpongeFlavor::query()->delete();
    CreamFlavor::query()->delete();
    $this->seed([SpongeFlavorSeeder::class, CreamFlavorSeeder::class]);

    $pandan = SpongeFlavor::where('name', 'Pandan')->sole();
    SpongeFlavor::query()->update(['is_default' => false]);
    $pandan->update(['is_default' => true]);

    $this->seed(SpongeFlavorSeeder::class);

    expect(SpongeFlavor::where('is_default', true)->pluck('id')->all())->toBe([$pandan->id]);
});

test('reseeding replaces a default that is no longer on the menu', function () {
    $this->vanillaCream->update(['is_default' => true]); // not a seeded flavor, so the seeder takes it off the menu

    $this->seed(CreamFlavorSeeder::class);

    expect(CreamFlavor::where('is_default', true)->pluck('name')->all())->toBe(['Butter Cream']);
});

test('the seeded shapes have no base drawing, so only the flavors paint the layers', function () {
    $this->seed(CakeVariantSeeder::class);

    expect(CakeVariant::pluck('base_svg')->filter()->all())->toBe([]);
});
