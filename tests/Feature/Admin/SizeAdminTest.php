<?php

use App\Models\CakeCustomization;
use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;

beforeEach(function () {
    $this->small = CakeSize::create(['label' => '1 kg', 'weight_kg' => 1, 'price' => 1200]);
    $this->large = CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 2300]);

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

function useSizeInOrder(CakeSize $size): void
{
    CakeCustomization::create([
        'cake_size_id' => $size->id,
        'sponge_flavor_id' => SpongeFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true])->id,
        'cream_flavor_id' => CreamFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true])->id,
        'quantity' => 1,
        'price' => $size->price,
    ]);
}

test('admin sees the sizes lightest first', function () {
    CakeSize::create(['label' => 'Half kg', 'weight_kg' => 0.5, 'price' => 700]);

    $this->actingAs($this->admin)
        ->get(route('admin.sizes.index'))
        ->assertOk()
        ->assertSeeInOrder(['Half kg', '1 kg', '2 kg']);
});

test('admin can add a size and the design page shows it in weight order', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.sizes.store'), ['label' => '1.5 kg', 'weight_kg' => '1.5', 'price' => '1750.50'])
        ->assertRedirect(route('admin.sizes.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('cake_sizes', ['label' => '1.5 kg', 'weight_kg' => 1.5, 'price' => 1750.5]);

    $this->get(route('cake-studio.index'))
        ->assertViewHas('sizes', fn ($sizes) => $sizes->pluck('label')->all() === ['1 kg', '1.5 kg', '2 kg']);
});

test('admin can edit a size', function () {
    $this->actingAs($this->admin)->get(route('admin.sizes.edit', $this->small))->assertOk()->assertSee('1200');

    $this->put(route('admin.sizes.update', $this->small), ['label' => '1 kg', 'weight_kg' => 1, 'price' => 1300])
        ->assertRedirect(route('admin.sizes.index'))
        ->assertSessionHasNoErrors();

    expect($this->small->fresh()->price)->toBe('1300.00');
});

test('the cart uses the edited price', function () {
    $this->small->update(['price' => 1500]);
    $sponge = SpongeFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
    $cream = CreamFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
    $layer = ['sponge_flavor_id' => $sponge->id, 'cream_flavor_id' => $cream->id];

    $this->postJson(route('cart.add'), [
        'cake_size_id' => $this->small->id,
        'quantity' => 1,
        'layers' => [$layer, $layer],
        'price' => 1, // ignored: the server recomputes it
    ])->assertOk();

    expect(collect(session('cake_cart'))->first()['price'])->toEqual(1500);
});

test('size validation', function (array $input, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.sizes.store'), array_merge(['label' => '3 kg', 'weight_kg' => 3, 'price' => 3300], $input))
        ->assertSessionHasErrors($field);

    expect(CakeSize::count())->toBe(2);
})->with([
    'label missing' => [['label' => ''], 'label'],
    'label taken' => [['label' => '1 kg'], 'label'],
    'label too long' => [['label' => str_repeat('a', 51)], 'label'],
    'weight missing' => [['weight_kg' => ''], 'weight_kg'],
    'weight zero' => [['weight_kg' => 0], 'weight_kg'],
    'weight too precise' => [['weight_kg' => '1.234'], 'weight_kg'],
    'price missing' => [['price' => ''], 'price'],
    'price negative' => [['price' => -1], 'price'],
    'price not a number' => [['price' => 'free'], 'price'],
]);

test('a size can keep its own label when edited', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.sizes.update', $this->small), ['label' => '1 kg', 'weight_kg' => 1, 'price' => 1200])
        ->assertSessionHasNoErrors();
});

test('admin can delete an unused size', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.sizes.destroy', $this->large))
        ->assertRedirect(route('admin.sizes.index'))
        ->assertSessionHasNoErrors();

    $this->assertModelMissing($this->large);
});

test('a size used by an order cannot be deleted', function () {
    useSizeInOrder($this->large);

    $this->actingAs($this->admin)
        ->from(route('admin.sizes.index'))
        ->delete(route('admin.sizes.destroy', $this->large))
        ->assertRedirect(route('admin.sizes.index'))
        ->assertSessionHasErrors('size');

    $this->assertModelExists($this->large);
});

test('the last size cannot be deleted', function () {
    $this->large->delete();

    $this->actingAs($this->admin)
        ->delete(route('admin.sizes.destroy', $this->small))
        ->assertSessionHasErrors('size');

    $this->assertModelExists($this->small);
});

test('customers and guests cannot manage sizes', function () {
    $this->get(route('admin.sizes.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $this->actingAs($customer);

    $this->get(route('admin.sizes.index'))->assertForbidden();
    $this->post(route('admin.sizes.store'), ['label' => '3 kg', 'weight_kg' => 3, 'price' => 3300])->assertForbidden();
    $this->put(route('admin.sizes.update', $this->small), ['label' => '1 kg', 'weight_kg' => 1, 'price' => 1])->assertForbidden();
    $this->delete(route('admin.sizes.destroy', $this->large))->assertForbidden();

    expect(CakeSize::count())->toBe(2)->and($this->small->fresh()->price)->toBe('1200.00');
});
