<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** A sponge or cream flavor's name, extra price, color and whether it is on the menu. */
class SaveFlavorRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $table = $this->route('type') === 'cream' ? 'cream_flavors' : 'sponge_flavors';

        return [
            'name' => ['required', 'string', 'max:60', Rule::unique($table, 'name')->ignore($this->route('id'))],
            'extra_price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.unique' => 'There is already a flavor with that name.',
            'color.regex' => 'Pick a color.',
        ];
    }
}
