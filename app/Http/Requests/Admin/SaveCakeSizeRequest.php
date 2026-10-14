<?php

namespace App\Http\Requests\Admin;

use App\Models\CakeSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Used for both creating and editing a size. */
class SaveCakeSizeRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $size = $this->route('size');

        return [
            'label' => [
                'required', 'string', 'max:50',
                Rule::unique('cake_sizes', 'label')->ignore($size instanceof CakeSize ? $size->id : null),
            ],
            'weight_kg' => ['required', 'numeric', 'decimal:0,2', 'min:0.1', 'max:100'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['weight_kg' => 'weight (kg)'];
    }
}
