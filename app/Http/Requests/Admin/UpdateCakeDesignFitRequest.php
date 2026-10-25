<?php

namespace App\Http\Requests\Admin;

use App\Models\CakeDesign;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Where the cake sits in a design's cover photo (see CakeDesign::FIT): shares of the photo's width (x, r)
 * and height (the rest). The side's bottom edge must be below its top edge.
 */
class UpdateCakeDesignFitRequest extends FormRequest
{
    /** The admin middleware on the route already checks who is asking. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $rules = array_map(fn () => ['required', 'numeric', 'min:0', 'max:1'], CakeDesign::DEFAULT_FIT);
        $rules['r'] = ['required', 'numeric', 'min:0.05', 'max:0.5'];
        $rules['bottom'][] = 'gt:top';
        $rules['top_curve'] = ['required', 'numeric', 'min:0', 'max:0.3'];
        $rules['bottom_curve'] = ['required', 'numeric', 'min:0', 'max:0.3'];

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['x' => 'middle', 'r' => 'half width', 'top' => 'top edge', 'bottom' => 'bottom edge', 'top_curve' => 'top curve', 'bottom_curve' => 'bottom curve'];
    }
}
