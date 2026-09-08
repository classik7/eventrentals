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
    Schema::table('payments', function (Blueprint $table) {
        $table->boolean('escrow_released')->default(false);
        $table->date('escrow_release_date')->nullable();
        $table->decimal('withdrawable_amount', 15, 2)->default(0);
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn([
            'escrow_released',
            'escrow_release_date',
            'withdrawable_amount'
        ]);
    });
}
};
