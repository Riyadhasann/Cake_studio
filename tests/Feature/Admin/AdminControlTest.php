<?php

use App\Models\CakeCustomization;
use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\SpongeFlavor;
use App\Models\User;
use App\Services\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->size = CakeSize::create(['label' => '2 kg', 'weight_kg' => 2, 'price' => 1800]);
    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'is_default' => true, 'color' => '#5b3a29']);
    $this->cream = CreamFlavor::create(['name' => 'Butter Cream', 'extra_price' => 0, 'is_active' => true, 'is_default' => true, 'color' => '#fff1d6']);
});

test('the admin home shows the numbers, the set-up checklist and every section', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.home'))
        ->assertOk()
        ->assertSee('Orders waiting')
        ->assertSee('Shop set-up')
        ->assertSee('Upload the cake photo')
        ->assertSee(route('admin.settings.index'), false)
        ->assertSee(route('admin.cake-variants.index'), false);
});

test('customers cannot open the admin pages', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.home'))->assertForbidden();
    $this->get(route('admin.settings.index'))->assertForbidden();
    $this->post(route('admin.flavors.store', 'sponge'), ['name' => 'X', 'extra_price' => 0, 'color' => '#ffffff'])->assertForbidden();
});

test('every admin page renders in the admin menu', function () {
    $this->actingAs($this->admin);

    foreach (['admin.home', 'admin.orders.index', 'admin.sizes.index', 'admin.cake-visuals.index', 'admin.designs.index', 'admin.settings.index', 'admin.cake-variants.index'] as $route) {
        $this->get(route($route))->assertOk()->assertSee('aria-label="Admin"', false)->assertSee('Shop &amp; pictures', false);
    }
});

test('the admin changes the shop name, contact details and design page heading', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'name' => 'Sweet Layers',
        'tagline' => 'Cakes your way',
        'phone' => '+880 1700 000000',
        'email' => 'hello@sweet.test',
        'hero_title' => 'Build your dream cake',
        'hero_text' => 'Pick everything yourself.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertSee('Sweet Layers')
        ->assertSee('Build your dream <span class="text-brand">cake</span>', false)
        ->assertSee('Pick everything yourself.')
        ->assertSee('hello@sweet.test')
        ->assertSee('tel:+8801700000000', false);
});

test('an empty heading text keeps the original one with the layer counts', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), ['name' => 'Cake Studio', 'hero_text' => ''])->assertSessionHasNoErrors();

    $this->get(route('cake-studio.index'))->assertSee('Stack 2 to 4 layers');
});

test('a bad phone number or email is refused', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), ['name' => 'Shop', 'phone' => 'call me', 'email' => 'nope'])
        ->assertSessionHasErrors(['phone', 'email']);
});

test('the admin replaces the logo, banner and cake background, and can go back to the originals', function () {
    $this->actingAs($this->admin);

    foreach (['logo' => [256, 256], 'banner' => [1600, 500], 'scene' => [600, 800], 'favicon' => [64, 64]] as $key => [$w, $h]) {
        $this->post(route('admin.settings.images.update', $key), ['image' => UploadedFile::fake()->image("{$key}.png", $w, $h)])
            ->assertRedirect()->assertSessionHasNoErrors();
    }

    $settings = app(SiteSettings::class);
    foreach (['logo', 'banner', 'scene', 'favicon'] as $key) {
        expect($settings->imagePath($key))->toStartWith('storage/cake-site/');
        Storage::disk('public')->assertExists(substr($settings->imagePath($key), strlen('storage/')));
    }

    $page = $this->get(route('cake-studio.index'))->assertOk();
    $page->assertSee('--banner-image: url("'.asset($settings->imagePath('banner')).'")', false)
        ->assertSee('--scene-image: url("'.asset($settings->imagePath('scene')).'")', false)
        ->assertSee('--scene-aspect: 0.75', false)
        ->assertSee(asset($settings->imagePath('logo')), false)
        ->assertSee('<link rel="icon" href="'.asset($settings->imagePath('favicon')).'">', false);

    $this->put(route('admin.settings.scene'), ['scene_foot' => 80])->assertSessionHasNoErrors();
    $this->get(route('cake-studio.index'))->assertSee('--scene-foot: 0.8;', false);

    $banner = app(SiteSettings::class)->imagePath('banner');
    $this->delete(route('admin.settings.images.destroy', 'banner'))->assertRedirect();
    Storage::disk('public')->assertMissing(substr($banner, strlen('storage/')));
    $this->get(route('cake-studio.index'))->assertDontSee('--banner-image', false);
});

test('an SVG logo is cleaned, and the banner must be a photo', function () {
    $this->actingAs($this->admin);

    $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><script>alert(1)</script><circle cx="5" cy="5" r="4"/></svg>');
    $this->post(route('admin.settings.images.update', 'logo'), ['image' => $svg])->assertSessionHasNoErrors();

    $stored = Storage::disk('public')->get(substr(app(SiteSettings::class)->imagePath('logo'), strlen('storage/')));
    expect($stored)->toContain('<circle')->not->toContain('script');

    $svg = UploadedFile::fake()->createWithContent('banner.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
    $this->post(route('admin.settings.images.update', 'banner'), ['image' => $svg])->assertSessionHasErrors('image');
    $this->post(route('admin.settings.images.update', 'nothing'), ['image' => $svg])->assertNotFound();
});

test('the shop works with the original pictures and words before anything is set', function () {
    expect(SiteSetting::count())->toBe(0);

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertSee(config('studio.name'))
        ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
        ->assertDontSee('--scene-image', false);
});

test('the admin adds, edits, hides and deletes flavors', function () {
    $this->actingAs($this->admin);

    $this->post(route('admin.flavors.store', 'sponge'), ['name' => 'Red velvet', 'extra_price' => 120, 'color' => '#a3222b'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $velvet = SpongeFlavor::where('name', 'Red velvet')->firstOrFail();
    expect($velvet->is_active)->toBeTrue()->and((float) $velvet->extra_price)->toBe(120.0)->and($velvet->sort_order)->toBeGreaterThan($this->sponge->fresh()->sort_order);

    $this->get(route('cake-studio.index'))->assertSee('Red velvet');

    $this->put(route('admin.flavors.update', ['sponge', $velvet->id]), ['name' => 'Red Velvet', 'extra_price' => 150, 'color' => '#a3222b', 'is_active' => '0'])
        ->assertSessionHasNoErrors();
    expect($velvet->fresh())->name->toBe('Red Velvet')->is_active->toBeFalse();

    // Hidden flavors stay on the admin page but not in the shop.
    $this->get(route('admin.cake-visuals.index'))->assertSee('Red Velvet')->assertSee('Hidden');
    $this->get(route('cake-studio.index'))->assertDontSee('Red Velvet');

    $this->post(route('admin.flavors.store', 'sponge'), ['name' => 'Red Velvet', 'extra_price' => 0, 'color' => '#ffffff'])
        ->assertSessionHasErrors('name');

    $picture = UploadedFile::fake()->image('velvet.png')->store('cake-layers', 'public');
    $velvet->update(['layer_image' => 'storage/'.$picture]);
    $this->delete(route('admin.flavors.destroy', ['sponge', $velvet->id]))->assertSessionHasNoErrors();
    expect(SpongeFlavor::find($velvet->id))->toBeNull();
    Storage::disk('public')->assertMissing($picture);
});

test('the default flavor, the last one on the menu and flavors in orders are kept', function () {
    $this->actingAs($this->admin);

    $this->put(route('admin.flavors.update', ['sponge', $this->sponge->id]), ['name' => 'Chocolate', 'extra_price' => 0, 'color' => '#5b3a29', 'is_active' => '0'])
        ->assertSessionHasErrors('flavor');
    $this->delete(route('admin.flavors.destroy', ['cream', $this->cream->id]))->assertSessionHasErrors('flavor');

    $vanilla = SpongeFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true, 'color' => '#f3e2b8']);
    $order = Order::create(['customer_name' => 'A', 'customer_phone' => '1', 'customer_address' => 'B', 'total' => 1800]);
    $customization = CakeCustomization::create(['order_id' => $order->id, 'cake_size_id' => $this->size->id, 'sponge_flavor_id' => $vanilla->id, 'cream_flavor_id' => $this->cream->id, 'price' => 1800, 'quantity' => 1]);
    $customization->layers()->create(['position' => 1, 'sponge_flavor_id' => $vanilla->id, 'cream_flavor_id' => $this->cream->id]);

    $this->delete(route('admin.flavors.destroy', ['sponge', $vanilla->id]))->assertSessionHasErrors(['flavor' => 'Vanilla is in past orders, so it can\'t be deleted. Hide it from the menu instead.']);
    expect(SpongeFlavor::find($vanilla->id))->not->toBeNull();
});
