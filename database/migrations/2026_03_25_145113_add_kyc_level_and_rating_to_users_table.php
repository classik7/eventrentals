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
    Schema::table('users', function (Blueprint $table) {

        // 🎯 KYC LEVEL (0–3)
        $table->integer('kyc_level')->default(0)->after('kyc_status');

        // ⭐ USER RATING
        $table->float('rating')->default(0)->after('kyc_level');

        // 📊 TOTAL JOBS
        $table->integer('jobs_completed')->default(0)->after('rating');

    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['kyc_level', 'rating', 'jobs_completed']);
    });
}
};
