<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cart_items', 'quantity')) {

            Schema::table('cart_items', function (Blueprint $table) {

                $table->renameColumn('quantity', 'quantity_units');

            });

        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cart_items', 'quantity_units')) {

            Schema::table('cart_items', function (Blueprint $table) {

                $table->renameColumn('quantity_units', 'quantity');

            });

        }
    }
};