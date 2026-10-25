<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The 3D look of one sponge or cream: a close-up side picture, for creams an optional top picture,
 * the grain size, and whether the side picture is made seamless.
 */
class UpdateFlavorSurfaceRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $picture = ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096', 'dimensions:min_width=256,max_width=4000,max_height=4000'];

        return [
            'side' => $picture,
            'top' => $this->route('type') === 'cream'
                ? ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096', 'dimensions:min_width=256,min_height=256,max_width=4000,max_height=4000']
                : ['prohibited'],
            'tile_scale' => ['required', 'numeric', 'between:0.25,4'],
            'seamless' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'top.prohibited' => 'Only creams have a top picture.',
            'side.dimensions' => 'The side picture must be at least 256 px and at most 4000 px on each side.',
            'top.dimensions' => 'The top picture must be at least 256 × 256 px and at most 4000 px on each side.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['side' => 'side picture', 'top' => 'top picture', 'tile_scale' => 'grain size'];
    }
}
