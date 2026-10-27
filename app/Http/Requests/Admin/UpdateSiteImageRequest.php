<?php

namespace App\Http\Requests\Admin;

use App\Services\SvgSanitizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * One of the shop's pictures (logo, favicon, title banner, cake background). The logo, favicon and background may also
 * be an SVG file, which is cleaned (no script or outside links) before it is saved; the banner is a photo or drawing.
 */
class UpdateSiteImageRequest extends FormRequest
{
    /** The pictures that may be an SVG. */
    public const SVG_ALLOWED = ['logo', 'favicon', 'scene'];

    /** The cleaned SVG, set during validation when the upload is an SVG. */
    private ?string $sanitizedSvg = null;

    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        if (! $this->isSvg()) {
            $size = match ($this->route('key')) {
                'banner' => 'dimensions:min_width=800,max_width=4000,max_height=4000',
                'favicon' => 'dimensions:min_width=32,min_height=32,max_width=1024,max_height=1024',
                default => 'dimensions:min_width=64,min_height=64,max_width=4000,max_height=4000',
            };

            return ['image' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:5120', $size]];
        }

        return [
            'image' => [
                'required', 'file', 'max:2048',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! in_array($this->route('key'), self::SVG_ALLOWED, true)) {
                        $fail('Use a PNG, JPG or WebP for the title banner.');

                        return;
                    }

                    try {
                        $this->sanitizedSvg = app(SvgSanitizer::class)->sanitizeFile((string) file_get_contents((string) $value->getRealPath()));
                    } catch (InvalidArgumentException $e) {
                        $fail("This SVG can't be used: {$e->getMessage()}");
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['image' => 'picture'];
    }

    /** By file name, detected type or content, so a renamed SVG is still cleaned rather than stored raw. */
    public function isSvg(): bool
    {
        $file = $this->file('image');

        if (! $file instanceof UploadedFile) {
            return false;
        }

        if (strtolower($file->getClientOriginalExtension()) === 'svg' || in_array($file->getMimeType(), ['image/svg+xml', 'image/svg'], true)) {
            return true;
        }

        $start = (string) @file_get_contents((string) $file->getRealPath(), length: 1024);

        return preg_match('/<svg[\s>]/i', $start) === 1;
    }

    /** The cleaned SVG markup, once validation has passed for an SVG upload. */
    public function sanitizedSvg(): ?string
    {
        return $this->sanitizedSvg;
    }
}
