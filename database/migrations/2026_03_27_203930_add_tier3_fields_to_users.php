<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('business_name')->nullable();
            $table->string('cac_number')->nullable();
            $table->text('business_address')->nullable();
            $table->string('proof_of_address')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'business_name',
                'cac_number',
                'business_address',
                'proof_of_address'
            ]);

        });
    }
};
