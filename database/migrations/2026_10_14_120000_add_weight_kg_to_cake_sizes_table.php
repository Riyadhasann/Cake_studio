<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cake_sizes', function (Blueprint $table) {
            $table->decimal('weight_kg', 5, 2)->nullable(); // shown to customers; sizes are listed lightest first
        });

        // Existing sizes are labelled like "2 kg": take the weight from the label.
        foreach (DB::table('cake_sizes')->get(['id', 'label']) as $size) {
            if (preg_match('/(\d+(?:\.\d+)?)\s*kg/i', $size->label, $match)) {
                DB::table('cake_sizes')->where('id', $size->id)->update(['weight_kg' => $match[1]]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('cake_sizes', function (Blueprint $table) {
            $table->dropColumn('weight_kg');
        });
    }
};
