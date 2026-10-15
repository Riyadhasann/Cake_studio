<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An optional grayscale image the size of the drawing, multiplied over the slots for light and shadow.
        Schema::table('cake_variants', function (Blueprint $table) {
            $table->string('shading_image')->nullable()->after('base_svg');
        });

        // A photo of one flavor for one slot of one drawing. When present it replaces the flavor's texture there.
        Schema::create('flavor_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('layer_no'); // 1 = bottom
            $table->string('flavor_type', 10); // sponge | cream: also the slot's type
            $table->unsignedBigInteger('flavor_id'); // sponge_flavors.id or cream_flavors.id
            $table->string('image'); // path inside public/, e.g. storage/cake-photos/x.webp
            $table->timestamps();

            $table->unique(['cake_variant_id', 'layer_no', 'flavor_type', 'flavor_id']);
            $table->index(['flavor_type', 'flavor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flavor_variant_images');

        Schema::table('cake_variants', function (Blueprint $table) {
            $table->dropColumn('shading_image');
        });
    }
};
