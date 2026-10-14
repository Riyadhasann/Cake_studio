<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCakeCustomizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cake_size_id' => ['required', 'exists:cake_sizes,id'],
            'message' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],

            // The layer count IS the size of this array: 2, 3 or 4 entries.
            'layers' => ['required', 'array', 'min:2', 'max:4'],
            'layers.*.sponge_flavor_id' => [
                'required', 'integer',
                Rule::exists('sponge_flavors', 'id')->where('is_active', true),
            ],
            'layers.*.cream_flavor_id' => [
                'required', 'integer',
                Rule::exists('cream_flavors', 'id')->where('is_active', true),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'layers.required' => 'Please choose your cake layers.',
            'layers.min' => 'A cake needs at least 2 layers.',
            'layers.max' => 'A cake can have at most 4 layers.',
            'layers.*.sponge_flavor_id.required' => 'Choose a sponge flavor for every layer.',
            'layers.*.sponge_flavor_id.exists' => 'One of the chosen sponge flavors is not available.',
            'layers.*.cream_flavor_id.required' => 'Choose a cream flavor for every layer.',
            'layers.*.cream_flavor_id.exists' => 'One of the chosen cream flavors is not available.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $layers = $this->input('layers');

            // Only a plain 0,1,2... list is accepted, so a stray key like layers[7] can't sneak in.
            if (is_array($layers) && ! array_is_list($layers)) {
                $validator->errors()->add('layers', 'The cake layers were not sent in a valid format.');
            }
        });
    }
}
