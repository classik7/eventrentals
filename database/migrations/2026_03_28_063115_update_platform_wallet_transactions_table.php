<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('platform_wallet_transactions', function (Blueprint $table) {
            $table->string('reference')->unique()->change();
            $table->unsignedBigInteger('booking_id')->nullable()->after('reference');

            $table->index('type');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('platform_wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('booking_id');
            $table->dropIndex(['type']);
            $table->dropIndex(['booking_id']);
        });
    }
};
