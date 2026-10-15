<?php

namespace App\Http\Requests\Admin;

use App\Models\CakeVariant;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreVariantShadingRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'shading' => [
                'required', 'file', 'mimes:png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000',
                // It is laid over the whole drawing, so it must have the drawing's proportions.
                function (string $attribute, mixed $value, Closure $fail) {
                    $variant = $this->route('variant');
                    $size = $value instanceof UploadedFile ? @getimagesize($value->getRealPath()) : false;

                    if (! $variant instanceof CakeVariant || ! $size || $size[1] < 1) {
                        return;
                    }

                    $expected = $variant->view_box_width / $variant->view_box_height;

                    if (abs($size[0] / $size[1] - $expected) > $expected * 0.02) {
                        $fail("The shading image must have the drawing's proportions ({$variant->view_box_width} × {$variant->view_box_height}); it is {$size[0]} × {$size[1]} px.");
                    }
                },
            ],
        ];
    }
}
