<?php

namespace App\Actions\Flavors;

use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\Support\Facades\DB;

class ReorderFlavors
{
    /**
     * Save the menu order: the first ID gets position 1, the next 2, and so on.
     *
     * @param  class-string<SpongeFlavor|CreamFlavor>  $modelClass
     * @param  list<int>  $ids
     */
    public function handle(string $modelClass, array $ids): void
    {
        DB::transaction(function () use ($modelClass, $ids) {
            foreach ($ids as $index => $id) {
                $modelClass::whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
