<?php

namespace App\Http\Requests\Admin;

use App\Services\Images\SvgSanitizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * The image of one sponge, cream or fixed part. Sponges and creams may also be an SVG file,
 * which is cleaned (no script or outside links) before it is saved.
 */
class UpdateLayerImageRequest extends FormRequest
{
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
            return [
                'image' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
            ];
        }

        return [
            'image' => [
                'required', 'file', 'max:2048',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! in_array($this->route('type'), ['sponge', 'cream'], true)) {
                        $fail('SVG images are for sponges and creams. Use a PNG, JPG or WebP for the frosting and plate.');

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
        return ['image' => 'image'];
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
