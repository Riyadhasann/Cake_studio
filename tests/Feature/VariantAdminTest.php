<?php

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Models\User;
use App\Support\SvgSanitizer;
use Database\Seeders\CakeVariantSeeder;

beforeEach(function () {
    $this->seed(CakeVariantSeeder::class);
    $this->two = CakeVariant::where('layers', 2)->firstOrFail();

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

/** The editor form for $variant with every slot as a path, plus $overrides. */
function variantForm(CakeVariant $variant, array $overrides = []): array
{
    $slots = [];
    foreach ($variant->slots as $slot) {
        $slots[$slot->layer_no][$slot->type] = ['mode' => 'path', 'd' => $slot->svg_path];
    }

    return array_replace_recursive([
        'view_box_width' => $variant->view_box_width,
        'view_box_height' => $variant->view_box_height,
        'base_svg' => '',
        'slots' => $slots,
    ], $overrides);
}

test('guests are sent to login and customers get a 403', function () {
    $this->get(route('admin.cake-variants.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $this->actingAs($customer)->get(route('admin.cake-variants.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('admin.cake-variants.edit', $this->two))->assertForbidden();
    $this->actingAs($customer)->put(route('admin.cake-variants.update', $this->two), variantForm($this->two))->assertForbidden();
    $this->actingAs($customer)->post(route('admin.cake-variants.publish', $this->two))->assertForbidden();
});

test('admin sees every variant with its publish status', function () {
    CakeVariant::where('layers', 4)->update(['is_published' => false]);

    $this->actingAs($this->admin)
        ->get(route('admin.cake-variants.index'))
        ->assertOk()
        ->assertSeeInOrder(['2 layers', 'Published', '3 layers', 'Published', '4 layers', 'Ready to publish']);
});

test('admin can start a drawing for a missing layer count, only once', function () {
    CakeVariant::where('layers', 3)->delete();

    $this->actingAs($this->admin)->get(route('admin.cake-variants.index'))->assertSee('Start a drawing');

    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.store'), ['layers' => 3])
        ->assertRedirect();

    $three = CakeVariant::where('layers', 3)->firstOrFail();
    expect($three->is_published)->toBeFalse()->and($three->slots)->toHaveCount(0);

    $this->actingAs($this->admin)->post(route('admin.cake-variants.store'), ['layers' => 3])->assertSessionHasErrors('layers');
    $this->actingAs($this->admin)->post(route('admin.cake-variants.store'), ['layers' => 5])->assertSessionHasErrors('layers');
});

test('the edit page shows a row per slot', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.cake-variants.edit', $this->two))
        ->assertOk()
        ->assertSee('variantEditor', false)
        ->assertSee($this->two->slots->first()->svg_path);
});

test('admin can save the view box, a path and a rectangle', function () {
    $form = variantForm($this->two, [
        'view_box_width' => 800,
        'view_box_height' => 400,
        'slots' => [
            1 => ['sponge' => ['mode' => 'path', 'd' => ' M0 0H10V10H0Z ']],
            2 => ['cream' => ['mode' => 'rect', 'x' => 0, 'y' => 0, 'width' => 100, 'height' => 20, 'radius' => 10]],
        ],
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), $form)
        ->assertRedirect(route('admin.cake-variants.edit', $this->two))
        ->assertSessionHasNoErrors();

    $variant = $this->two->fresh('slots');
    $path = fn (int $layer, string $type) => $variant->slots->first(fn ($s) => $s->layer_no === $layer && $s->type === $type)->svg_path;

    expect($variant->view_box_width)->toBe(800)
        ->and($variant->view_box_height)->toBe(400)
        ->and($path(1, 'sponge'))->toBe('M0 0H10V10H0Z')
        ->and($path(2, 'cream'))->toBe(CakeVariantSlot::roundedRectPath(0, 0, 100, 20, 10))
        ->and($variant->is_published)->toBeTrue();
});

test('the base SVG keeps shapes but loses scripts, handlers, styles and external links', function () {
    $markup = '<script>alert(1)</script>'
        .'<path d="M0 0H700" stroke="#75483f" onclick="alert(2)" style="fill:url(https://evil.test/x)" />'
        .'<a href="javascript:alert(3)"><rect width="5" height="5" /></a>'
        .'<foreignObject><div>hi</div></foreignObject>'
        .'<rect width="10" height="10" fill="url(https://evil.test/a.svg#p)" />'
        .'<use href="https://evil.test/sprite.svg#x" />'
        .'<linearGradient id="shine"><stop offset="0" stop-color="#fff" /></linearGradient>'
        .'<circle r="4" fill="url(#shine)" />';

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), variantForm($this->two, ['base_svg' => $markup]))
        ->assertSessionHasNoErrors();

    $saved = $this->two->fresh()->base_svg;

    expect($saved)
        ->not->toContain('script')
        ->not->toContain('alert')
        ->not->toContain('onclick')
        ->not->toContain('style')
        ->not->toContain('evil.test')
        ->not->toContain('foreignObject')
        ->not->toContain('<a')
        ->not->toContain('<use')
        ->toContain('<path d="M0 0H700" stroke="#75483f"/>')
        ->toContain('<rect width="10" height="10"/>')
        ->toContain('fill="url(#shine)"')
        ->toContain('<linearGradient id="shine">');
});

test('unreadable base SVG and entities are refused', function (string $markup) {
    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), variantForm($this->two, ['base_svg' => $markup]))
        ->assertSessionHasErrors('base_svg');
})->with([
    'unclosed tag' => ['<path d="M0 0"'],
    'doctype with entity' => ['<!DOCTYPE x [<!ENTITY e SYSTEM "file:///etc/passwd">]><path d="&e;"/>'],
]);

test('a slot path may only hold path commands', function () {
    $form = variantForm($this->two, ['slots' => [1 => ['sponge' => ['mode' => 'path', 'd' => 'M0 0"/><script>alert(1)</script>']]]]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), $form)
        ->assertSessionHasErrors('slots.1.sponge.d');

    expect(SvgSanitizer::isPathData('M0 0L10 10Z'))->toBeTrue()
        ->and(SvgSanitizer::isPathData('m1.5e2,-3 a1 1 0 0 1 2 2z'))->toBeTrue()
        ->and(SvgSanitizer::isPathData('L0 0'))->toBeFalse()
        ->and(SvgSanitizer::isPathData('M0 0 url(x)'))->toBeFalse();
});

test('slots for layers the variant does not have are refused', function () {
    $form = variantForm($this->two, ['slots' => [3 => ['sponge' => ['mode' => 'path', 'd' => 'M0 0Z']], 1 => ['jam' => ['mode' => 'path', 'd' => 'M0 0Z']]]]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), $form)
        ->assertSessionHasErrors('slots');

    expect($this->two->slots()->count())->toBe(4);
});

test('a rectangle needs its size', function () {
    $form = variantForm($this->two, ['slots' => [1 => ['cream' => ['mode' => 'rect', 'x' => 0, 'y' => 0, 'width' => 0]]]]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), $form)
        ->assertSessionHasErrors(['slots.1.cream.width', 'slots.1.cream.height']);
});

test('emptying a slot of a published variant removes it and unpublishes the variant', function () {
    $form = variantForm($this->two, ['slots' => [2 => ['cream' => ['mode' => 'path', 'd' => '']]]]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $this->two), $form)
        ->assertSessionHas('warning', fn ($warning) => str_contains($warning, 'Layer 2 cream is missing.'));

    $variant = $this->two->fresh('slots');
    expect($variant->slots)->toHaveCount(3)
        ->and($variant->is_published)->toBeFalse()
        ->and(CakeVariant::published()->pluck('layers')->all())->not->toContain(2);
});

test('publishing an incomplete variant fails with the reasons', function () {
    $this->two->update(['is_published' => false]);
    $this->two->slots()->where('layer_no', 1)->where('type', 'sponge')->delete();

    $this->actingAs($this->admin)
        ->from(route('admin.cake-variants.edit', $this->two))
        ->post(route('admin.cake-variants.publish', $this->two))
        ->assertRedirect(route('admin.cake-variants.edit', $this->two))
        ->assertSessionHasErrors(['publish']);

    expect(session('errors')->get('publish'))->toContain('Layer 1 sponge is missing.')
        ->and($this->two->fresh()->is_published)->toBeFalse();
});

test('admin can unpublish and publish a complete variant', function () {
    $this->actingAs($this->admin)->post(route('admin.cake-variants.unpublish', $this->two))->assertSessionHasNoErrors();
    expect($this->two->fresh()->is_published)->toBeFalse();

    $this->actingAs($this->admin)->post(route('admin.cake-variants.publish', $this->two))->assertSessionHasNoErrors();
    expect($this->two->fresh()->is_published)->toBeTrue();
});
