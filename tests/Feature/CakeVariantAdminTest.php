<?php

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Models\User;
use App\Services\SvgSanitizer;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

/**
 * The editor's form input for a variant: every slot as a path, merged with $overrides.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function variantInput(int $layers, array $overrides = []): array
{
    $slots = [];
    foreach (range(1, $layers) as $layerNo) {
        foreach (CakeVariantSlot::TYPES as $type) {
            $slots[$layerNo][$type] = ['mode' => 'path', 'path' => "M0 {$layerNo}H10V20H0Z"];
        }
    }

    return array_replace_recursive(['view_box_width' => 700, 'view_box_height' => 196 * $layers, 'base_svg' => '', 'slots' => $slots], $overrides);
}

test('admin sees every layer count with its status', function () {
    completeVariant(2)->update(['is_published' => true]);
    CakeVariant::create(['layers' => 3, 'view_box_width' => 700, 'view_box_height' => 588]);

    $this->actingAs($this->admin)
        ->get(route('admin.cake-variants.index'))
        ->assertOk()
        ->assertSeeInOrder(['2 layers', 'Published', '3 layers', 'Draft', 'Layer 1 sponge is missing.', '4 layers', 'Start drawing']);
});

test('admin can start a drawing for a missing layer count', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.store'), ['layers' => 4])
        ->assertRedirect(route('admin.cake-variants.edit', CakeVariant::firstWhere('layers', 4)));

    expect(CakeVariant::firstWhere('layers', 4))->is_published->toBeFalse()->view_box_height->toBe(784);

    $this->post(route('admin.cake-variants.store'), ['layers' => 4])->assertSessionHasErrors('layers');
    $this->post(route('admin.cake-variants.store'), ['layers' => 5])->assertSessionHasErrors('layers');
    expect(CakeVariant::count())->toBe(1);
});

test('admin can open the editor', function () {
    $variant = completeVariant(2);

    $this->actingAs($this->admin)
        ->get(route('admin.cake-variants.edit', $variant))
        ->assertOk()
        ->assertSee('Layer 2 cream')
        ->assertSee('M0 0H10V10H0Z');
});

test('admin can save slot paths and rectangles', function () {
    $variant = CakeVariant::create(['layers' => 2, 'view_box_width' => 700, 'view_box_height' => 392]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $variant), variantInput(2, [
            'view_box_width' => 720,
            'slots' => [
                1 => ['sponge' => ['path' => "  M0 0\n  H10 V10 H0 Z "]],
                2 => ['cream' => ['mode' => 'rect', 'path' => 'not used', 'x' => 0, 'y' => 0, 'width' => 700, 'height' => 56, 'radius' => 28]],
            ],
        ]))
        ->assertRedirect(route('admin.cake-variants.edit', $variant))
        ->assertSessionHasNoErrors();

    $variant->refresh()->load('slots');
    $slot = fn (int $layerNo, string $type) => $variant->slots->first(fn ($s) => $s->layer_no === $layerNo && $s->type === $type);

    expect($variant->view_box_width)->toBe(720)
        ->and($variant->slots)->toHaveCount(4)
        ->and($slot(1, 'sponge')->svg_path)->toBe('M0 0 H10 V10 H0 Z')
        ->and($slot(2, 'cream')->svg_path)->toBe(CakeVariantSlot::roundedRectPath(0, 0, 700, 56, 28))
        ->and($variant->canPublish())->toBeTrue();
});

test('an empty path removes the slot', function () {
    $variant = completeVariant(2);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $variant), variantInput(2, ['slots' => [2 => ['cream' => ['path' => '']]]]))
        ->assertSessionHasNoErrors();

    expect($variant->slots()->count())->toBe(3);
});

test('scripts, event handlers and outside links are stripped from the base SVG', function () {
    $variant = completeVariant(2);
    $markup = '<script>alert(1)</script>'
        .'<defs><linearGradient id="shine"><stop offset="0" stop-color="#fff"/></linearGradient></defs>'
        .'<path d="M0 0H10" stroke="#3b2a27" onclick="alert(2)" style="fill:red"/>'
        .'<rect width="5" height="5" fill="url(#shine)"/>'
        .'<rect width="5" height="5" fill="url(https://evil.test/x.svg#a)"/>'
        .'<a href="javascript:alert(3)"><circle r="4"/></a>'
        .'<image href="https://evil.test/x.png"/>'
        .'<foreignObject><div>hi</div></foreignObject>';

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $variant), variantInput(2, ['base_svg' => $markup]))
        ->assertSessionHasNoErrors();

    $saved = $variant->fresh()->base_svg;

    expect($saved)
        ->toContain('<path d="M0 0H10" stroke="#3b2a27"/>')
        ->toContain('fill="url(#shine)"')
        ->toContain('<stop offset="0" stop-color="#fff"/>')
        ->not->toContain('script')
        ->not->toContain('alert')
        ->not->toContain('onclick')
        ->not->toContain('style')
        ->not->toContain('evil')
        ->not->toContain('<a')
        ->not->toContain('<circle')
        ->not->toContain('foreignObject')
        ->not->toContain('xmlns');
});

test('the sanitizer drops elements from other namespaces', function () {
    $clean = app(SvgSanitizer::class)->sanitize(
        '<svg:script xmlns:svg="http://www.w3.org/2000/svg">x</svg:script><g><p xmlns="http://www.w3.org/1999/xhtml">x</p><rect width="1"/></g>',
    );

    expect($clean)->toBe('<g><rect width="1"/></g>');
});

test('malformed base SVG and unsafe paths are rejected', function (array $input, string $field) {
    $variant = completeVariant(2);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $variant), variantInput(2, $input))
        ->assertSessionHasErrors($field);

    expect($variant->fresh()->base_svg)->toBeNull();
})->with([
    'unclosed tag' => [['base_svg' => '<g><path d="M0 0"></g>'], 'base_svg'],
    'markup in a path' => [['slots' => [1 => ['sponge' => ['path' => 'M0 0"/><script>alert(1)</script>']]]], 'slots.1.sponge.path'],
    'path not starting with M' => [['slots' => [1 => ['sponge' => ['path' => 'L0 0']]]], 'slots.1.sponge.path'],
    'rect without width' => [['slots' => [1 => ['cream' => ['mode' => 'rect', 'x' => 0, 'y' => 0, 'height' => 5]]]], 'slots.1.cream.width'],
    'unknown mode' => [['slots' => [1 => ['cream' => ['mode' => 'circle']]]], 'slots.1.cream.mode'],
    'zero width view box' => [['view_box_width' => 0], 'view_box_width'],
]);

test('a complete variant can be published and unpublished', function () {
    $variant = completeVariant(3);

    $this->actingAs($this->admin)
        ->post(route('admin.cake-variants.publish', $variant))
        ->assertSessionHasNoErrors();

    expect($variant->fresh()->is_published)->toBeTrue()
        ->and(CakeVariant::published()->pluck('layers')->all())->toBe([3]);

    $this->delete(route('admin.cake-variants.unpublish', $variant))->assertSessionHasNoErrors();

    expect($variant->fresh()->is_published)->toBeFalse()
        ->and(CakeVariant::published()->count())->toBe(0);
});

test('an incomplete variant cannot be published and the admin is told why', function () {
    $variant = completeVariant(2);
    $variant->slots()->where('layer_no', 2)->where('type', 'sponge')->delete();

    $this->actingAs($this->admin)
        ->from(route('admin.cake-variants.edit', $variant))
        ->post(route('admin.cake-variants.publish', $variant))
        ->assertRedirect(route('admin.cake-variants.edit', $variant))
        ->assertSessionHasErrors(['publish' => 'Layer 2 sponge is missing.']);

    expect($variant->fresh()->is_published)->toBeFalse();
});

test('saving a published variant without all its shapes unpublishes it', function () {
    $variant = completeVariant(2);
    $variant->update(['is_published' => true]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $variant), variantInput(2, ['slots' => [1 => ['sponge' => ['path' => '']]]]))
        ->assertSessionHas('warning');

    expect($variant->fresh()->is_published)->toBeFalse()
        ->and(CakeVariant::published()->count())->toBe(0);
});

test('saving a complete published variant keeps it published', function () {
    $variant = completeVariant(2);
    $variant->update(['is_published' => true]);

    $this->actingAs($this->admin)
        ->put(route('admin.cake-variants.update', $variant), variantInput(2))
        ->assertSessionMissing('warning');

    expect($variant->fresh()->is_published)->toBeTrue();
});

test('customers and guests cannot manage cake shapes', function () {
    $variant = completeVariant(2);

    $this->get(route('admin.cake-variants.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.cake-variants.index'))->assertForbidden();
    $this->post(route('admin.cake-variants.store'), ['layers' => 3])->assertForbidden();
    $this->get(route('admin.cake-variants.edit', $variant))->assertForbidden();
    $this->put(route('admin.cake-variants.update', $variant), variantInput(2))->assertForbidden();
    $this->post(route('admin.cake-variants.publish', $variant))->assertForbidden();
    $this->delete(route('admin.cake-variants.unpublish', $variant))->assertForbidden();

    expect(CakeVariant::count())->toBe(1)->and($variant->fresh()->is_published)->toBeFalse();
});
