<?php

use App\Models\CakePart;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use App\Models\User;
use App\Services\PublicUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->sponge = SpongeFlavor::create(['name' => 'Chocolate', 'extra_price' => 0, 'is_active' => true]);
    $this->cream = CreamFlavor::create(['name' => 'Vanilla', 'extra_price' => 0, 'is_active' => true]);
});

function svgUpload(string $svg, string $name = 'layer.svg'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $svg);
}

/** The saved file's contents for a "storage/..." path. */
function savedSvg(?string $path): string
{
    return Storage::disk('public')->get(substr((string) $path, strlen('storage/')));
}

const ILLUSTRATOR_SVG = <<<'SVG'
    <?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1200 255" width="1200" height="255">
      <defs>
        <style>.cls-1{fill:#4e2e20;}.cls-2{fill:url(#crumb);opacity:.4}</style>
        <linearGradient id="crumb" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#000"/></linearGradient>
        <clipPath id="edge"><rect width="1200" height="255" rx="24"/></clipPath>
        <filter id="soft"><feGaussianBlur stdDeviation="2"/></filter>
        <circle id="dot" r="4" fill="#2b170f"/>
      </defs>
      <title>Chocolate sponge</title>
      <g clip-path="url(#edge)">
        <rect class="cls-1" width="1200" height="255"/>
        <rect class="cls-2" width="1200" height="255" style="mix-blend-mode:multiply"/>
        <use xlink:href="#dot" x="40" y="60" filter="url(#soft)"/>
        <use href="#dot" x="90" y="120"/>
        <image href="data:image/png;base64,iVBORw0KGgo=" width="10" height="10"/>
      </g>
    </svg>
    SVG;

test('admin can upload an SVG sponge image, and drawing-app features are kept', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.update', ['sponge', $this->sponge->id]), ['image' => svgUpload(ILLUSTRATOR_SVG)])
        ->assertSessionHasNoErrors();

    $path = $this->sponge->fresh()->layer_image;
    expect($path)->toStartWith('storage/cake-layers/')->toEndWith('.svg');

    expect(savedSvg($path))
        ->toContain('viewBox="0 0 1200 255"')
        ->toContain('<style>.cls-1{fill:#4e2e20;}.cls-2{fill:url(#crumb);opacity:.4}</style>')
        ->toContain('class="cls-1"')
        ->toContain('clip-path="url(#edge)"')
        ->toContain('<feGaussianBlur stdDeviation="2"/>')
        ->toContain('xlink:href="#dot"')
        ->toContain('<use href="#dot"')
        ->toContain('href="data:image/png;base64,iVBORw0KGgo="')
        ->toContain('<title>Chocolate sponge</title>')
        ->toContain('style="mix-blend-mode:multiply"')
        ->not->toContain('DOCTYPE');

    // Its size comes from the view box, so the design page can crop it to a slot.
    expect(app(PublicUploads::class)->size($path))->toBe([1200, 255]);
});

test('scripts, handlers, outside links and unsafe styles are removed from an uploaded SVG', function () {
    $svg = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 20" onload="alert(1)">
          <script>alert(2)</script>
          <style>@import url(https://evil.test/x.css); rect{fill:red}</style>
          <style>rect{background:url(https://evil.test/track.png)}</style>
          <rect width="100" height="20" fill="#fff3d1" onclick="alert(3)" style="fill:url(https://evil.test/a)"/>
          <a href="javascript:alert(4)"><circle r="5"/></a>
          <use xlink:href="https://evil.test/sprite.svg#icon"/>
          <use href="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4="/>
          <image href="https://evil.test/pixel.png" width="1" height="1"/>
          <image href="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=" width="1" height="1"/>
          <foreignObject><div xmlns="http://www.w3.org/1999/xhtml">hi</div></foreignObject>
          <animate attributeName="href" to="javascript:alert(5)"/>
          <set attributeName="onmouseover" to="alert(6)"/>
          <rect width="1" height="1" fill="url(#ok)"/>
        </svg>
        SVG;

    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.update', ['cream', $this->cream->id]), ['image' => svgUpload($svg)])
        ->assertSessionHasNoErrors();

    $saved = savedSvg($this->cream->fresh()->layer_image);

    expect($saved)
        ->toContain('<rect width="100" height="20" fill="#fff3d1"/>')
        ->toContain('fill="url(#ok)"')
        ->not->toContain('alert')
        ->not->toContain('script')
        ->not->toContain('onload')
        ->not->toContain('evil')
        ->not->toContain('@import')
        ->not->toContain('<style')
        ->not->toContain('data:image/svg')
        ->not->toContain('<a')
        ->not->toContain('<circle')
        ->not->toContain('foreignObject')
        ->not->toContain('animate')
        ->not->toContain('<set');
});

test('an SVG file renamed to .png is still cleaned rather than stored as uploaded', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.update', ['sponge', $this->sponge->id]), [
            'image' => UploadedFile::fake()->createWithContent('innocent.png', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><script>alert(1)</script><rect width="10" height="10"/></svg>'),
        ])
        ->assertSessionHasNoErrors();

    $path = $this->sponge->fresh()->layer_image;
    expect($path)->toEndWith('.svg')
        ->and(savedSvg($path))->not->toContain('script');
});

test('unusable SVG files are rejected', function (string $svg) {
    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.update', ['sponge', $this->sponge->id]), ['image' => svgUpload($svg)])
        ->assertSessionHasErrors('image');

    expect($this->sponge->fresh()->layer_image)->toBeNull();
})->with([
    'not well-formed' => ['<svg xmlns="http://www.w3.org/2000/svg"><rect></svg>'],
    'not svg at all' => ['hello'],
    'html root' => ['<html xmlns="http://www.w3.org/1999/xhtml"><body/></html>'],
    'entity bomb' => ['<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY a "aaaa"><!ENTITY b "&a;&a;&a;">]><svg xmlns="http://www.w3.org/2000/svg"><text>&b;</text></svg>'],
    'external entity' => ['<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg"><text>&x;</text></svg>'],
    'nothing left' => ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
]);

test('frosting and plate stay raster images', function () {
    $plate = CakePart::firstOrCreate(['key' => 'plate'], ['name' => 'Plate']);

    $this->actingAs($this->admin)
        ->post(route('admin.cake-visuals.update', ['part', $plate->id]), ['image' => svgUpload('<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1"/></svg>')])
        ->assertSessionHasErrors('image');

    expect($plate->fresh()->layer_image)->toBeNull();
});

test('replacing an SVG with a PNG deletes the SVG, and PNG uploads still work', function () {
    $this->actingAs($this->admin)->post(route('admin.cake-visuals.update', ['sponge', $this->sponge->id]), ['image' => svgUpload(ILLUSTRATOR_SVG)]);
    $svgPath = $this->sponge->fresh()->layer_image;

    $this->post(route('admin.cake-visuals.update', ['sponge', $this->sponge->id]), ['image' => UploadedFile::fake()->image('layer.png', 1200, 255)])
        ->assertSessionHasNoErrors();

    expect($this->sponge->fresh()->layer_image)->toEndWith('.png');
    Storage::disk('public')->assertMissing(substr($svgPath, strlen('storage/')));
});

test('the design page crops an SVG layer image using its view box', function () {
    $this->actingAs($this->admin)->post(route('admin.cake-visuals.update', ['sponge', $this->sponge->id]), ['image' => svgUpload(ILLUSTRATOR_SVG)]);
    completeVariant(2)->update(['is_published' => true]);

    $this->get(route('cake-studio.index'))
        ->assertViewHas('sponges', fn ($sponges) => $sponges->first()['image_size'] === [1200, 255] && str_ends_with($sponges->first()['image'], '.svg'))
        ->assertSee('viewBox="0 0 1200 255" preserveAspectRatio="xMidYMid slice"', false);
});

test('small SVG view boxes are scaled up so their proportions survive rounding', function () {
    Storage::disk('public')->put('cake-layers/tiny.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1.5 0.3"><rect width="1.5" height="0.3"/></svg>');

    expect(app(PublicUploads::class)->size('storage/cake-layers/tiny.svg'))->toBe([1000, 200]);
});
