<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** More photos for a cake design, added after its last photo (up to 20 per upload). */
class StoreCakeDesignFramesRequest extends FormRequest
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
            'frames' => ['required', 'array', 'min:1', 'max:20'],
            'frames.*' => ['file', 'mimes:png,jpg,jpeg,webp', 'max:4096', 'dimensions:min_width=200,min_height=200,max_width=4000,max_height=4000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['frames' => 'photos', 'frames.*' => 'photo'];
    }
}
