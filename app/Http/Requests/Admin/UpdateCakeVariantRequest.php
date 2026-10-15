<?php

namespace App\Http\Requests\Admin;

use App\Services\SvgSanitizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * The cake drawing editor: view box, base markup and one shape per slot.
 * Each slot is sent as slots[layer_no][type] with a mode of "path" (a d attribute)
 * or "rect" (x, y, width, height and an optional corner radius, turned into a path on save).
 * A slot with an empty path is removed, so a draft can be saved before every slot is drawn.
 */
class UpdateCakeVariantRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $sanitizer = app(SvgSanitizer::class);
        $number = ['numeric', 'between:-10000,10000'];

        return [
            'view_box_width' => ['required', 'integer', 'min:1', 'max:5000'],
            'view_box_height' => ['required', 'integer', 'min:1', 'max:5000'],
            'base_svg' => ['nullable', 'string', 'max:20000'],
            // The cut face marked on the photo: three points along its top edge and three along its bottom edge.
            'cut_face' => ['nullable', 'array:top,bottom,sponge_share'],
            'cut_face.top' => ['required_with:cut_face', 'array', 'size:3'],
            'cut_face.bottom' => ['required_with:cut_face', 'array', 'size:3'],
            'cut_face.top.*' => ['array', 'size:2'],
            'cut_face.bottom.*' => ['array', 'size:2'],
            'cut_face.top.*.*' => ['required', ...$number],
            'cut_face.bottom.*.*' => ['required', ...$number],
            'cut_face.sponge_share' => ['required_with:cut_face', 'numeric', 'between:0.3,0.95'],
            'slots' => ['required', 'array'],
            'slots.*' => ['array'],
            'slots.*.*.mode' => ['required', 'in:path,rect'],
            'slots.*.*.path' => [
                'exclude_unless:slots.*.*.mode,path', 'nullable', 'string', 'max:10000',
                function (string $attribute, mixed $value, Closure $fail) use ($sanitizer) {
                    if (is_string($value) && trim($value) !== '' && ! $sanitizer->isValidPath($value)) {
                        $fail('A path may only contain SVG path commands and numbers, starting with M.');
                    }
                },
            ],
            'slots.*.*.x' => ['exclude_unless:slots.*.*.mode,rect', 'required', ...$number],
            'slots.*.*.y' => ['exclude_unless:slots.*.*.mode,rect', 'required', ...$number],
            'slots.*.*.width' => ['exclude_unless:slots.*.*.mode,rect', 'required', 'numeric', 'min:0.1', 'max:10000'],
            'slots.*.*.height' => ['exclude_unless:slots.*.*.mode,rect', 'required', 'numeric', 'min:0.1', 'max:10000'],
            'slots.*.*.radius' => ['exclude_unless:slots.*.*.mode,rect', 'nullable', 'numeric', 'min:0', 'max:5000'],
        ];
    }

    /** @return list<Closure> */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    app(SvgSanitizer::class)->sanitize((string) $this->input('base_svg', ''));
                } catch (InvalidArgumentException) {
                    $validator->errors()->add('base_svg', 'The base SVG is not well-formed markup. Check that every tag is closed.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'view_box_width' => 'view box width',
            'view_box_height' => 'view box height',
            'base_svg' => 'base SVG',
            'cut_face.top.*.*' => 'cut face point',
            'cut_face.bottom.*.*' => 'cut face point',
            'cut_face.sponge_share' => 'sponge share',
            'slots.*.*.path' => 'path',
            'slots.*.*.x' => 'x',
            'slots.*.*.y' => 'y',
            'slots.*.*.width' => 'width',
            'slots.*.*.height' => 'height',
            'slots.*.*.radius' => 'corner radius',
        ];
    }
}
