<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A topping on a design's 3D cake: when adding it, its cut-out picture (PNG or WebP with a transparent background);
 * how many stand round the cake, how far in (a share of the radius, 0 = the middle), how wide each is (a share of
 * the radius) and where the first one stands (degrees round from the front).
 */
class SaveCakeDesignToppingRequest extends FormRequest
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
            'image' => $this->isMethod('post')
                ? ['required', 'file', 'mimes:png,webp', 'max:2048', 'dimensions:min_width=16,min_height=16,max_width=1000,max_height=1000']
                : ['prohibited'],
            'count' => ['required', 'integer', 'min:1', 'max:40'],
            'ring' => ['required', 'numeric', 'min:0', 'max:0.95'],
            'size' => ['required', 'numeric', 'min:0.05', 'max:1'],
            'angle' => ['required', 'numeric', 'min:0', 'max:360'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['image' => 'cut-out picture', 'ring' => 'distance from the middle', 'size' => 'width', 'angle' => 'first position'];
    }
}
