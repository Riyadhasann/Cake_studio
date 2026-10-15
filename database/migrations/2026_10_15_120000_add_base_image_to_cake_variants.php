<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cake_variants', function (Blueprint $table) {
            // A photo of the cake with an empty cut face, drawn under the slots. Its pixel size is the view box.
            $table->string('base_image')->nullable()->after('base_svg');
            // The cut face marked on the photo: {"top": [[x, y] × 3], "bottom": [[x, y] × 3], "sponge_share": 0.72}
            $table->json('cut_face')->nullable()->after('base_image');
        });
    }

    public function down(): void
    {
        Schema::table('cake_variants', function (Blueprint $table) {
            $table->dropColumn(['base_image', 'cut_face']);
        });
    }
};
