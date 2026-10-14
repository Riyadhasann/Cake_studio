<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cake_customization_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_customization_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position'); // 1 = bottom layer
            $table->foreignId('sponge_flavor_id')->constrained();
            $table->foreignId('cream_flavor_id')->constrained();
            $table->timestamps();

            $table->unique(['cake_customization_id', 'position']);
        });

        // Cakes ordered before this change had one sponge and one cream.
        // Keep them as a one-layer cake so every order has at least one layer row.
        $now = now();

        DB::table('cake_customizations')->orderBy('id')->each(function ($row) use ($now) {
            DB::table('cake_customization_layers')->insert([
                'cake_customization_id' => $row->id,
                'position' => 1,
                'sponge_flavor_id' => $row->sponge_flavor_id,
                'cream_flavor_id' => $row->cream_flavor_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_customization_layers');
    }
};
