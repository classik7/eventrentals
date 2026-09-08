<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {Schema::table('users', function (Blueprint $table) {

    if (!Schema::hasColumn('users', 'kyc_tier')) {
        $table->integer('kyc_tier')->default(1);
    }

    if (!Schema::hasColumn('users', 'kyc_status')) {
        $table->string('kyc_status')->default('not_verified');
    }

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
