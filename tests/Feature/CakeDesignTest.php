<?php

use App\Models\CakeCustomization;
use App\Models\CakeDesign;
use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use Database\Seeders\CakeDesignSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->size = CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 1800]);
    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 50, 'is_active' => true]);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true]);

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

/** A design with one photo per given file name, stored like an upload. */
function designWithPhotos(string $name, int $photos = 1, float $price = 0, bool $active = true): CakeDesign
{
    $design = CakeDesign::create(['name' => $name, 'extra_price' => $price, 'is_active' => $active]);
    foreach (range(0, $photos - 1) as $position) {
        Storage::disk('public')->put("cake-designs/{$name}-{$position}.webp", 'x');
        $design->frames()->create(['image' => "storage/cake-designs/{$name}-{$position}.webp", 'position' => $position]);
    }

    return $design;
}

function cartPayload(array $extra = []): array
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

test('the seeder adds the four bundled designs once, each with its photo', function () {
    $this->seed(CakeDesignSeeder::class);
    $this->seed(CakeDesignSeeder::class);

    $designs = CakeDesign::ordered()->with('frames')->get();
    expect($designs->pluck('name')->all())->toBe(array_keys(CakeDesignSeeder::DESIGNS))
        ->and($designs->every(fn (CakeDesign $design) => $design->frames->count() === 1))->toBeTrue()
        ->and($designs->first()->fitOrDefault())->toEqual(CakeDesignSeeder::FITS['Caramel Chocolate Drip'])
        // Every bundled design has its toppings cut out, stored and placed
        ->and($designs->every(fn (CakeDesign $design) => count($design->toppingList()) === count(CakeDesignSeeder::TOPPINGS[$design->name])))->toBeTrue();
    Storage::disk('public')->assertExists(substr($designs->first()->toppingList()[0]['image'], strlen('storage/')));

    Storage::disk('public')->assertExists(substr($designs->first()->frames->first()->image, strlen('storage/')));
});

test('the design page offers active designs that have a photo, with every photo in spin order', function () {
    $spinning = designWithPhotos('Drip', photos: 3, price: 250);
    designWithPhotos('Hidden', active: false);
    CakeDesign::create(['name' => 'No photo yet', 'extra_price' => 0, 'is_active' => true]);

    $response = $this->get(route('cake-studio.index'))->assertOk()->assertSee('Pick a design');

    expect($response->viewData('designs')->all())->toBe([[
        'id' => $spinning->id,
        'name' => 'Drip',
        'extra_price' => '250.00',
        'frames' => collect(range(0, 2))->map(fn ($i) => asset("storage/cake-designs/Drip-{$i}.webp"))->all(),
        // Not fitted yet: the 3D cake wears the photo from the usual place, with no toppings stood on it
        'fit' => CakeDesign::DEFAULT_FIT,
        'toppings' => [],
    ]]);
});

test('admin fits a design photo for the 3D cake, which the design page then uses', function () {
    $design = designWithPhotos('Drip');
    $fit = ['x' => 0.49, 'r' => 0.42, 'top' => 0.34, 'bottom' => 0.65, 'top_curve' => 0.05, 'bottom_curve' => 0.08];

    $this->actingAs($this->admin)
        ->put(route('admin.designs.fit', $design), $fit)
        ->assertRedirect(route('admin.designs.index'))
        ->assertSessionHasNoErrors();

    expect($design->fresh()->fitOrDefault())->toEqual($fit)
        ->and($this->get(route('cake-studio.index'))->viewData('designs')->first()['fit'])->toEqual($fit);

    // The bottom edge must be below the top edge, and the cake no wider than the photo
    $this->actingAs($this->admin)
        ->put(route('admin.designs.fit', $design), [...$fit, 'bottom' => 0.3, 'r' => 0.9])
        ->assertSessionHasErrors(['bottom', 'r']);
    $this->actingAs(User::factory()->create())->put(route('admin.designs.fit', $design), $fit)->assertForbidden();
});

test('a picked design adds its price on the server and is saved with the order', function () {
    $design = designWithPhotos('Drip', price: 250);

    $this->postJson(route('cart.add'), cartPayload(['cake_design_id' => $design->id]))->assertOk();

    $item = session('cake_cart')[0];
    // 1800 for the size, 2 × 50 for the chocolate sponges, 250 for the design
    expect($item['price'])->toEqual(2150.0)
        ->and($item['design_name'])->toBe('Drip');

    $this->post(route('cart.place-order'), [
        'customer_name' => 'Mina', 'customer_phone' => '01700000000', 'customer_address' => 'Dhaka',
        'delivery_date' => now()->addDays(3)->toDateString(),
    ])->assertRedirect();

    $cake = CakeCustomization::sole();
    expect($cake->cake_design_id)->toBe($design->id)
        ->and($cake->design_name)->toBe('Drip')
        ->and((float) $cake->price)->toBe(2150.0);
});

test('a plain cake has no design, and a hidden design is refused', function () {
    $hidden = designWithPhotos('Hidden', price: 999, active: false);

    $this->postJson(route('cart.add'), cartPayload())->assertOk();
    expect(session('cake_cart')[0]['design_name'])->toBeNull()
        ->and(session('cake_cart')[0]['price'])->toEqual(1900.0);

    $this->postJson(route('cart.add'), cartPayload(['cake_design_id' => $hidden->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cake_design_id');
});

test('admin adds a design with photos, saved as WebP in file-name order', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.designs.store'), [
            'name' => 'Rose Drip',
            'extra_price' => 300,
            'frames' => [
                UploadedFile::fake()->image('frame-10.png', 400, 400),
                UploadedFile::fake()->image('frame-2.png', 400, 400),
                UploadedFile::fake()->image('frame-1.png', 400, 400),
            ],
        ])
        ->assertRedirect(route('admin.designs.index'))
        ->assertSessionHasNoErrors();

    $design = CakeDesign::with('frames')->sole();
    expect($design->name)->toBe('Rose Drip')
        ->and((float) $design->extra_price)->toBe(300.0)
        ->and($design->frames->pluck('position')->all())->toBe([0, 1, 2])
        ->and($design->frames->every(fn ($frame) => str_starts_with($frame->image, 'storage/cake-designs/') && str_ends_with($frame->image, '.webp')))->toBeTrue();
});

test('admin adds and removes photos, but a design keeps at least one', function () {
    $design = designWithPhotos('Drip');

    $this->actingAs($this->admin)
        ->post(route('admin.designs.frames.store', $design), ['frames' => [UploadedFile::fake()->image('next.png', 400, 400)]])
        ->assertSessionHasNoErrors();
    expect($design->frames()->pluck('position')->all())->toBe([0, 1]);

    $first = $design->frames()->first();
    $this->actingAs($this->admin)->delete(route('admin.designs.frames.destroy', [$design, $first]))->assertRedirect();
    Storage::disk('public')->assertMissing('cake-designs/Drip-0.webp');

    $last = $design->frames()->sole();
    $this->actingAs($this->admin)->delete(route('admin.designs.frames.destroy', [$design, $last]))->assertSessionHasErrors('frames');
    expect($design->frames()->count())->toBe(1);
});

test('deleting a design removes its photos but orders keep its name', function () {
    $design = designWithPhotos('Drip');
    $this->postJson(route('cart.add'), cartPayload(['cake_design_id' => $design->id]));
    $this->post(route('cart.place-order'), [
        'customer_name' => 'Mina', 'customer_phone' => '01700000000', 'customer_address' => 'Dhaka',
        'delivery_date' => now()->addDays(3)->toDateString(),
    ]);

    $this->actingAs($this->admin)->delete(route('admin.designs.destroy', $design))->assertRedirect();

    expect(CakeDesign::count())->toBe(0)
        ->and(CakeCustomization::sole()->cake_design_id)->toBeNull()
        ->and(CakeCustomization::sole()->design_name)->toBe('Drip');
    Storage::disk('public')->assertMissing('cake-designs/Drip-0.webp');
});

test('admin can rename, reprice and hide a design; customers cannot reach the page', function () {
    $design = designWithPhotos('Drip');

    $this->actingAs($this->admin)
        ->put(route('admin.designs.update', $design), ['name' => 'Chocolate Drip', 'extra_price' => 120, 'is_active' => 0])
        ->assertSessionHasNoErrors();
    expect($design->fresh())->name->toBe('Chocolate Drip')->is_active->toBeFalse();

    $this->actingAs($this->admin)->get(route('admin.designs.index'))->assertOk()->assertSee('Chocolate Drip');
    $this->actingAs(User::factory()->create())->get(route('admin.designs.index'))->assertForbidden();
});

test('admin adds, places and removes a topping, which the 3D cake stands round its top', function () {
    $design = designWithPhotos('Drip');

    $this->actingAs($this->admin)
        ->post(route('admin.designs.toppings.store', $design), [
            'image' => UploadedFile::fake()->image('kiss.png', 60, 60),
            'count' => 12, 'ring' => 0.8, 'size' => 0.3, 'angle' => 15,
        ])
        ->assertRedirect(route('admin.designs.index'))
        ->assertSessionHasNoErrors();

    $topping = $design->fresh()->toppingList()[0];
    expect($topping)->toMatchArray(['count' => 12, 'ring' => 0.8, 'size' => 0.3, 'angle' => 15.0])
        ->and($topping['image'])->toStartWith('storage/cake-designs/')->toEndWith('.webp');

    $this->actingAs($this->admin)
        ->put(route('admin.designs.toppings.update', [$design, 0]), ['count' => 6, 'ring' => 0.5, 'size' => 0.4, 'angle' => 0])
        ->assertSessionHasNoErrors();
    expect($design->fresh()->toppingList()[0])->toMatchArray(['count' => 6, 'ring' => 0.5, 'image' => $topping['image']]);

    $page = $this->get(route('cake-studio.index'))->viewData('designs')->first();
    expect($page['toppings'][0]['image'])->toBe(asset($topping['image']));

    // A JPG has no transparency, and placing a topping that isn't there is a 404
    $this->actingAs($this->admin)
        ->post(route('admin.designs.toppings.store', $design), ['image' => UploadedFile::fake()->image('kiss.jpg'), 'count' => 1, 'ring' => 0, 'size' => 0.3, 'angle' => 0])
        ->assertSessionHasErrors('image');
    $this->actingAs($this->admin)->delete(route('admin.designs.toppings.destroy', [$design, 5]))->assertNotFound();

    $this->actingAs($this->admin)->delete(route('admin.designs.toppings.destroy', [$design, 0]))->assertRedirect();
    expect($design->fresh()->toppingList())->toBe([]);
    Storage::disk('public')->assertMissing(substr($topping['image'], strlen('storage/')));
});
