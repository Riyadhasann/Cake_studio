<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A cake design's name, extra price and whether customers see it; when adding a design, also its photos.
 * Photos: PNG, JPG or WebP, up to 20 per upload (PHP's usual limit on files per request).
 */
class SaveCakeDesignRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $adding = $this->isMethod('post');

        return [
            'name' => ['required', 'string', 'max:80'],
            'extra_price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
            'frames' => $adding ? ['required', 'array', 'min:1', 'max:20'] : ['prohibited'],
            'frames.*' => ['file', 'mimes:png,jpg,jpeg,webp', 'max:4096', 'dimensions:min_width=200,min_height=200,max_width=4000,max_height=4000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['frames' => 'photos', 'frames.*' => 'photo', 'extra_price' => 'extra price'];
    }
}
