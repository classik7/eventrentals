<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('items', function (Blueprint $table) {

        $table->boolean('is_rentable')->default(true);
        $table->boolean('is_sellable')->default(false);

        $table->decimal('selling_price', 15, 2)
              ->nullable()
              ->after('price_per_day');

    });
}

public function down()
{
    Schema::table('items', function (Blueprint $table) {

        $table->dropColumn([
            'is_rentable',
            'is_sellable',
            'selling_price'
        ]);

    });
}
};
