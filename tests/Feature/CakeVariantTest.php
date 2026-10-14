<?php

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use Database\Seeders\CakeVariantSeeder;
use Illuminate\Database\UniqueConstraintViolationException;

/** A variant with a sponge and a cream slot for every layer. */
function completeVariant(int $layers = 2): CakeVariant
{
    $variant = CakeVariant::create(['layers' => $layers, 'view_box_width' => 700, 'view_box_height' => 196 * $layers]);

    foreach (range(1, $layers) as $layerNo) {
        foreach (CakeVariantSlot::TYPES as $type) {
            $variant->slots()->create(['layer_no' => $layerNo, 'type' => $type, 'svg_path' => 'M0 0H10V10H0Z']);
        }
    }

    return $variant->fresh();
}

test('a variant with a sponge and a cream for every layer can be published', function (int $layers) {
    $variant = completeVariant($layers);

    expect($variant->canPublish())->toBeTrue()
        ->and($variant->publishProblems())->toBe([])
        ->and($variant->slots)->toHaveCount(2 * $layers);
})->with(CakeVariant::LAYER_COUNTS);

test('a missing slot blocks publishing', function () {
    $variant = completeVariant(3);
    $variant->slots()->where('layer_no', 2)->where('type', 'cream')->delete();
    $variant->load('slots');

    expect($variant->canPublish())->toBeFalse()
        ->and($variant->publishProblems())->toContain('Layer 2 cream is missing.');
});

test('a duplicate slot blocks publishing', function () {
    $variant = completeVariant(2);
    $variant->slots()->where('layer_no', 2)->where('type', 'sponge')->delete();

    // The database refuses a true duplicate, so check the rule on slots that were not saved yet.
    $variant->setRelation('slots', $variant->slots()->get()->push(
        new CakeVariantSlot(['layer_no' => 1, 'type' => 'sponge', 'svg_path' => 'M0 0Z'])
    ));

    expect($variant->canPublish())->toBeFalse()
        ->and($variant->publishProblems())->toContain('Layer 1 sponge is defined 2 times.', 'Layer 2 sponge is missing.');
});

test('the database refuses two slots for the same layer and type', function () {
    $variant = completeVariant(2);

    $variant->slots()->create(['layer_no' => 1, 'type' => 'sponge', 'svg_path' => 'M0 0Z']);
})->throws(UniqueConstraintViolationException::class);

test('a slot outside the layer range blocks publishing', function () {
    $variant = completeVariant(2);
    $variant->slots()->where('layer_no', 2)->where('type', 'cream')->update(['layer_no' => 3]);
    $variant->load('slots');

    expect($variant->canPublish())->toBeFalse()
        ->and($variant->publishProblems())->toContain('Slot "Layer 3 cream" is outside layers 1 to 2.', 'Layer 2 cream is missing.');
});

test('an empty shape blocks publishing', function () {
    $variant = completeVariant(2);
    $variant->slots()->where('layer_no', 1)->where('type', 'sponge')->update(['svg_path' => '  ']);
    $variant->load('slots');

    expect($variant->canPublish())->toBeFalse()
        ->and($variant->publishProblems())->toContain('Slot "Layer 1 sponge" has no shape.');
});

test('an unknown slot type or an unsupported layer count blocks publishing', function () {
    $variant = completeVariant(2);
    $variant->slots()->where('layer_no', 1)->where('type', 'cream')->update(['type' => 'jam']);
    $variant->load('slots');

    expect($variant->canPublish())->toBeFalse();

    $five = CakeVariant::create(['layers' => 5, 'view_box_width' => 700, 'view_box_height' => 980]);
    expect($five->publishProblems())->toContain('The layer count must be 2, 3 or 4.');
});

test('deleting a variant deletes its slots', function () {
    $variant = completeVariant(2);

    $variant->delete();

    expect(CakeVariantSlot::count())->toBe(0);
});

test('the published scope only returns published variants', function () {
    completeVariant(2)->update(['is_published' => true]);
    completeVariant(3);

    expect(CakeVariant::published()->pluck('layers')->all())->toBe([2]);
});

test('rounded rectangle paths', function () {
    expect(CakeVariantSlot::roundedRectPath(0, 0, 100, 50))->toBe('M0 0H100V50H0Z')
        ->and(CakeVariantSlot::roundedRectPath(0, 0, 100, 50, 10))
        ->toBe('M10 0H90A10 10 0 0 1 100 10V40A10 10 0 0 1 90 50H10A10 10 0 0 1 0 40V10A10 10 0 0 1 10 0Z')
        // A radius larger than half the height makes round ends.
        ->and(CakeVariantSlot::roundedRectPath(0, 0, 100, 20, 99))->toContain('A10 10');
});

test('the seeder creates published 2, 3 and 4 layer cakes that match the default drawing', function () {
    $this->seed(CakeVariantSeeder::class);

    $variants = CakeVariant::with('slots')->orderBy('layers')->get();

    expect($variants->pluck('layers')->all())->toBe([2, 3, 4]);

    foreach ($variants as $variant) {
        expect($variant->is_published)->toBeTrue()
            ->and($variant->canPublish())->toBeTrue()
            ->and($variant->view_box_width)->toBe(700)
            ->and($variant->view_box_height)->toBe(196 * $variant->layers);
    }

    $two = $variants->first();
    // Bottom sponge: 660 wide, inset 20, sitting on the bottom edge. Top cream: full width at the top.
    expect($two->slots->firstWhere(fn ($s) => $s->layer_no === 1 && $s->type === 'sponge')->svg_path)
        ->toBe(CakeVariantSlot::roundedRectPath(20, 252, 660, 140, 22))
        ->and($two->slots->firstWhere(fn ($s) => $s->layer_no === 2 && $s->type === 'cream')->svg_path)
        ->toBe(CakeVariantSlot::roundedRectPath(0, 0, 700, 56, 28));
});

test('running the seeder again keeps an edited variant', function () {
    $this->seed(CakeVariantSeeder::class);
    CakeVariant::where('layers', 2)->update(['view_box_width' => 900, 'is_published' => false]);

    $this->seed(CakeVariantSeeder::class);

    expect(CakeVariant::count())->toBe(3)
        ->and(CakeVariantSlot::count())->toBe(2 * (2 + 3 + 4))
        ->and(CakeVariant::where('layers', 2)->value('view_box_width'))->toBe(900);
});
