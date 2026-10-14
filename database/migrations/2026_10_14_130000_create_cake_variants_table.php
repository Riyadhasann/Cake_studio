<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One cake drawing per layer count (2, 3 or 4), in SVG user units.
        Schema::create('cake_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('layers')->unique();
            $table->unsignedInteger('view_box_width');
            $table->unsignedInteger('view_box_height');
            $table->text('base_svg')->nullable(); // outline/decoration markup drawn above the slots
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // The shapes filled with a flavor: one sponge and one cream per layer.
        Schema::create('cake_variant_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('layer_no'); // 1 = bottom
            $table->string('type', 10); // sponge | cream
            $table->text('svg_path'); // the d attribute of an SVG <path>
            $table->timestamps();

            $table->unique(['cake_variant_id', 'layer_no', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_variant_slots');
        Schema::dropIfExists('cake_variants');
    }
};
