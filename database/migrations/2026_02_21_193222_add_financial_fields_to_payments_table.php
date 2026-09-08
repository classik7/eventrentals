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
        $table->decimal('subtotal', 12, 2)->after('reference');
        $table->decimal('service_fee', 12, 2)->after('subtotal');
        $table->decimal('owner_earnings', 12, 2)->after('service_fee');
        $table->decimal('total_amount', 12, 2)->after('owner_earnings');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn([
            'subtotal',
            'service_fee',
            'owner_earnings',
            'total_amount'
        ]);
    });
}
};
