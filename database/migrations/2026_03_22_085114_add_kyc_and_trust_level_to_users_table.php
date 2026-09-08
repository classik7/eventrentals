<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('kyc_status')->default('unverified');
            // values: unverified, pending, verified

            $table->integer('trust_level')->default(1);
            // Level 1, 2, 3

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('kyc_status');
            $table->dropColumn('trust_level');

        });
    }
};