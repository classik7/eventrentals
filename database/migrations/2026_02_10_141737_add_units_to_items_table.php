<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('quantity_units')->default(1)->after('price_per_day');
            $table->integer('unit_size')->default(1)->after('quantity_units');
            $table->string('unit_label')->default('unit')->after('unit_size');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['quantity_units', 'unit_size', 'unit_label']);
        });
    }
};
