<?php

namespace App\Http\Requests\Admin;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use App\Support\SvgSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * The variant editor form. Slots come in as slots[layer_no][type], each either a path
 * (mode=path, d) or a rectangle (mode=rect, x, y, width, height, radius). An empty path removes the slot.
 */
class SaveCakeVariantRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'view_box_width' => ['required', 'integer', 'min:1', 'max:5000'],
            'view_box_height' => ['required', 'integer', 'min:1', 'max:5000'],
            'base_svg' => ['nullable', 'string', 'max:20000'],
            'slots' => ['present', 'array'],
            'slots.*' => ['array'],
            'slots.*.*' => ['array'],
            'slots.*.*.mode' => ['required', 'in:path,rect'],
            'slots.*.*.d' => ['nullable', 'string', 'max:10000', 'regex:'.SvgSanitizer::PATH_PATTERN],
            'slots.*.*.x' => ['exclude_unless:slots.*.*.mode,rect', 'required', 'numeric', 'min:0'],
            'slots.*.*.y' => ['exclude_unless:slots.*.*.mode,rect', 'required', 'numeric', 'min:0'],
            'slots.*.*.width' => ['exclude_unless:slots.*.*.mode,rect', 'required', 'numeric', 'gt:0'],
            'slots.*.*.height' => ['exclude_unless:slots.*.*.mode,rect', 'required', 'numeric', 'gt:0'],
            'slots.*.*.radius' => ['exclude_unless:slots.*.*.mode,rect', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $variant = $this->route('variant');
                $layers = $variant instanceof CakeVariant ? $variant->layers : 0;

                foreach ((array) $this->input('slots', []) as $layerNo => $types) {
                    foreach ((array) $types as $type => $slot) {
                        if (! ctype_digit((string) $layerNo) || $layerNo < 1 || $layerNo > $layers
                            || ! in_array($type, CakeVariantSlot::TYPES, true)) {
                            $validator->errors()->add('slots', "There is no layer {$layerNo} {$type} on a {$layers}-layer cake.");
                        }
                    }
                }

                $baseSvg = $this->input('base_svg');
                if (is_string($baseSvg) && (new SvgSanitizer)->sanitize($baseSvg) === null) {
                    $validator->errors()->add('base_svg', 'The base SVG is not valid markup. Check that every tag is closed.');
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
            'slots.*.*.d' => 'path',
            'slots.*.*.x' => 'x',
            'slots.*.*.y' => 'y',
            'slots.*.*.width' => 'width',
            'slots.*.*.height' => 'height',
            'slots.*.*.radius' => 'corner radius',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['slots.*.*.d.regex' => 'A path may only hold SVG path commands (M, L, H, V, C, S, Q, T, A, Z) and numbers.'];
    }
}
