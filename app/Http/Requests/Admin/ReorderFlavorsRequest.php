<?php

namespace App\Http\Requests\Admin;

use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReorderFlavorsRequest extends FormRequest
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
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ];
    }

    /**
     * The list must be exactly the flavors on the menu, each once, so no flavor is lost or invented.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $activeIds = $this->modelClass()::where('is_active', true)->pluck('id')->sort()->values()->all();
                $sentIds = collect($this->orderedIds())->sort()->values()->all();

                if ($sentIds !== $activeIds) {
                    $validator->errors()->add('ids', 'The new order must list every flavor on the menu exactly once.');
                }
            },
        ];
    }

    /** @return list<int> */
    public function orderedIds(): array
    {
        return array_values(array_map('intval', (array) $this->input('ids')));
    }

    /** @return class-string<SpongeFlavor|CreamFlavor> */
    public function modelClass(): string
    {
        return $this->route('type') === 'sponge' ? SpongeFlavor::class : CreamFlavor::class;
    }
}
