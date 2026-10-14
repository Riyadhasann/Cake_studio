<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlavorTextureRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            // A square tile repeated across every slot of the cake, so it must be square and not huge.
            'texture' => [
                'required', 'file', 'mimes:png,webp', 'max:2048',
                'dimensions:ratio=1,min_width=256,max_width=1024',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'texture.dimensions' => 'The texture must be a square image between 256 and 1024 px wide.',
        ];
    }
}
