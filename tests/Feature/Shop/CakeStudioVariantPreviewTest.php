<?php

use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->sponge = SpongeFlavor::create([
        'name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true, 'color' => '#6b3f2a',
    ]);
    $this->cream = CreamFlavor::create([
        'name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true, 'color' => '#fff3d1',
        'layer_image' => 'storage/cake-layers/vanilla.png',
    ]);
});

test('inactive flavors are not sent', function () {
    SpongeFlavor::create(['name' => 'Hidden', 'extra_price' => 0, 'is_active' => false, 'layer_image' => 'storage/cake-layers/hidden.png']);

    $this->get(route('cake-studio.index'))
        ->assertViewHas('sponges', fn ($sponges) => $sponges->pluck('name')->all() === ['Chocolate'])
        ->assertDontSee('hidden.png');
});

test('published variants are sent keyed by layer count, bottom layer and sponge first', function () {
    completeVariant(3)->update(['is_published' => true, 'base_svg' => '<path d="M0 0H700" stroke="#3b2a27"/>']);
    completeVariant(2)->update(['is_published' => true]);

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('variants', function (array $variants) {
            expect(array_keys($variants))->toBe([2, 3])
                ->and($variants[3]['width'])->toBe(700)
                ->and($variants[3]['height'])->toBe(588)
                ->and($variants[3]['base_svg'])->toBe('<path d="M0 0H700" stroke="#3b2a27"/>')
                ->and(array_map(fn ($slot) => "{$slot['layer_no']} {$slot['type']}", $variants[3]['slots']))
                ->toBe(['1 sponge', '1 cream', '2 sponge', '2 cream', '3 sponge', '3 cream']);

            return true;
        })
        ->assertSee("variantLayers: JSON.parse('[2,3]')", false)
        ->assertSee('x-if="hasVariant && layerCount === 3"', false)
        ->assertSee('<path d="M0 0H700" stroke="#3b2a27"/>', false)
        ->assertSee('id="cake-image-cream-'.$this->cream->id.'"', false);
});

test('unpublished and incomplete variants never reach the design page', function () {
    completeVariant(2); // draft
    $broken = completeVariant(3);
    $broken->update(['is_published' => true]);
    $broken->slots()->where('layer_no', 3)->delete(); // published, then edited behind the editor's back

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('variants', [])
        ->assertSee('variantLayers: [],', false)
        ->assertDontSee('class="cake-svg"', false);
});

test('base markup written straight into the database is sanitized before it is drawn', function () {
    $variant = completeVariant(2);
    CakeVariant::whereKey($variant->id)->update([
        'is_published' => true,
        'base_svg' => '<script>alert(1)</script><rect width="5" height="5" onload="alert(2)"/>',
    ]);

    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertSee('<rect width="5" height="5"/>', false)
        ->assertDontSee('alert(', false);
});

test('without a published variant the layered preview is used', function () {
    $this->get(route('cake-studio.index'))
        ->assertOk()
        ->assertViewHas('variants', [])
        ->assertSee('class="cake-layers"', false)
        ->assertDontSee('class="cake-defs"', false);
});
