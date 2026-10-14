<?php

namespace App\Actions\Sizes;

use App\Models\CakeSize;
use Illuminate\Validation\ValidationException;

class DeleteCakeSize
{
    public function handle(CakeSize $size): void
    {
        // Past orders point at the size, so it must stay. Editing it is fine: orders keep their own price.
        if ($size->cakeCustomizations()->exists()) {
            throw ValidationException::withMessages([
                'size' => "{$size->label} is used by past orders, so it can't be deleted. Edit it instead.",
            ]);
        }

        // The design page needs at least one size to choose.
        if (CakeSize::count() === 1) {
            throw ValidationException::withMessages([
                'size' => "{$size->label} is the only size. Add another one before deleting it.",
            ]);
        }

        $size->delete();
    }
}
