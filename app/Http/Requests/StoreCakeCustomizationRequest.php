<?php

namespace App\Http\Requests;

use App\Models\CakeVariant;
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
            // A finished design for the outside of the cake, or none for a plain cake.
            'cake_design_id' => ['nullable', 'integer', Rule::exists('cake_designs', 'id')->where('is_active', true)],

            // The layer count IS the size of this array: one of the offered layer counts (2 to 4, plus any the admin added).
            'layers' => ['required', 'array', 'min:'.CakeVariant::MIN_LAYERS, 'max:'.max(CakeVariant::offeredLayerCounts())],
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
            'layers.min' => 'A cake needs at least '.CakeVariant::MIN_LAYERS.' layers.',
            'layers.max' => 'A cake can have at most '.max(CakeVariant::offeredLayerCounts()).' layers.',
            'layers.*.sponge_flavor_id.required' => 'Choose a sponge flavor for every layer.',
            'layers.*.sponge_flavor_id.exists' => 'One of the chosen sponge flavors is not available.',
            'layers.*.cream_flavor_id.required' => 'Choose a cream flavor for every layer.',
            'layers.*.cream_flavor_id.exists' => 'One of the chosen cream flavors is not available.',
            'cake_design_id.exists' => 'The chosen cake design is not available.',
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

            // The admin may offer e.g. 6 layers without 5, so the count must be one of the offered ones.
            if (is_array($layers) && ! $validator->errors()->has('layers')
                && ! in_array(count($layers), CakeVariant::offeredLayerCounts(), true)) {
                $validator->errors()->add('layers', 'A '.count($layers).'-layer cake is not available.');
            }
        });
    }
}
