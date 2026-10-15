<?php

namespace App\Http\Requests\Admin;

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantPhotoRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $variant = $this->route('variant');
        $layers = $variant instanceof CakeVariant ? $variant->layers : 0;
        $table = $this->input('flavor_type') === 'cream' ? 'cream_flavors' : 'sponge_flavors';

        return [
            'layer_no' => ['required', 'integer', 'between:1,'.max(1, $layers)],
            'flavor_type' => ['required', Rule::in(CakeVariantSlot::TYPES)],
            // A sponge photo can only be for a sponge, a cream photo for a cream.
            'flavor_id' => ['required', 'integer', Rule::exists($table, 'id')],
            'photo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['layer_no' => 'layer', 'flavor_type' => 'slot type', 'flavor_id' => 'flavor'];
    }
}
