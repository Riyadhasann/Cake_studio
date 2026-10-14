<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // create_orders_table already creates `total` on fresh installs; only rename on old databases.
        if (! Schema::hasColumn('orders', 'total_price')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('total_price', 'total');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'total')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('total', 'total_price');
        });
    }
};
