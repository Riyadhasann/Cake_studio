<?php

use App\Models\CakeCustomization;
use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;

beforeEach(function () {
    $this->size = CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 1800]);
    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'color' => '#5b3a29']);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true, 'color' => '#fff1d6']);
});

/** @param  array<string, mixed>  $extra */
function messageCake(array $extra): array
{
    return [
        'cake_size_id' => test()->size->id,
        'layers' => [
            ['sponge_flavor_id' => test()->sponge->id, 'cream_flavor_id' => test()->cream->id],
            ['sponge_flavor_id' => test()->sponge->id, 'cream_flavor_id' => test()->cream->id],
        ],
        'quantity' => 1,
        ...$extra,
    ];
}

function placeMessageOrder(): void
{
    test()->post(route('cart.place-order'), [
        'customer_name' => 'Mina', 'customer_phone' => '01700000000', 'customer_address' => 'Dhaka',
        'delivery_date' => now()->addDays(3)->toDateString(),
    ])->assertRedirect();
}

test('the studio offers the shop\'s icing colors for the message', function () {
    $this->get(route('cake-studio.index'))->assertOk()->assertSee('messageColors:', false)->assertSee('#e0608a', false);
});

test('the picked message color is kept on the order', function () {
    $this->postJson(route('cart.add'), messageCake(['message' => 'Happy Birthday!', 'message_color' => '#e0608a']))->assertOk();
    $this->get(route('cart.index'))->assertSee('Pink icing');

    placeMessageOrder();

    expect(CakeCustomization::sole())->message->toBe('Happy Birthday!')->message_color->toBe('#e0608a');
});

test('a message without a picked color gets the first icing color', function () {
    $this->postJson(route('cart.add'), messageCake(['message' => 'With love']))->assertOk();
    placeMessageOrder();

    expect(CakeCustomization::sole()->message_color)->toBe(array_values(config('studio.message_colors'))[0]);
});

test('no message keeps no color', function () {
    $this->postJson(route('cart.add'), messageCake(['message_color' => '#e0608a']))->assertOk();
    placeMessageOrder();

    expect(CakeCustomization::sole()->message_color)->toBeNull();
});

test('a color outside the shop\'s icing colors is refused', function () {
    $this->postJson(route('cart.add'), messageCake(['message' => 'Hi', 'message_color' => '#123456']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message_color');
});
