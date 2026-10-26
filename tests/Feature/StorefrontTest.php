<?php

use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\Order;
use App\Models\SpongeFlavor;

beforeEach(function () {
    $this->size = CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 1800]);
    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 50, 'is_active' => true, 'color' => '#5b3a29']);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true, 'color' => '#fff1d6']);
});

function addStorefrontCake(int $quantity = 1): void
{
    test()->postJson(route('cart.add'), [
        'cake_size_id' => test()->size->id,
        'layers' => [
            ['sponge_flavor_id' => test()->sponge->id, 'cream_flavor_id' => test()->cream->id],
            ['sponge_flavor_id' => test()->sponge->id, 'cream_flavor_id' => test()->cream->id],
        ],
        'quantity' => $quantity,
    ])->assertOk();
}

test('a design linked with ?design= starts selected in the studio', function () {
    $this->get(route('cake-studio.index', ['design' => 7]))->assertOk()->assertSee('initialDesignId: 7', false);
});

test('after ordering, the studio shows the order number with a link to track it', function () {
    addStorefrontCake();

    $this->post(route('cart.place-order'), [
        'customer_name' => 'Mina', 'customer_phone' => '01700000000', 'customer_address' => 'Dhaka',
        'delivery_date' => now()->addDays(3)->toDateString(),
    ])->assertRedirect(route('cake-studio.index'));

    $order = Order::sole();

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertSee('Order #'.$order->id.' placed.')
        ->assertSee(route('orders.track', ['order_id' => $order->id]), false);
});

test('the cart shows each cake with its layers and a checkout form', function () {
    addStorefrontCake(2);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Order summary')
        ->assertSee('background-color: #5b3a29', false)
        ->assertSee('autocomplete="tel"', false)
        ->assertSee('৳3,800.00');
});

test('the design slider lists the plain cake first', function () {
    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertSee('class="slider', false)
        ->assertSeeInOrder(['Plain', 'Just your layers']);
});
