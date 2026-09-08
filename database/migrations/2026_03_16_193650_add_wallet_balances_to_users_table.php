<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->decimal('wallet_pending',12,2)->default(0);
            $table->decimal('wallet_available',12,2)->default(0);

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('wallet_pending');
            $table->dropColumn('wallet_available');

        });
    }
};