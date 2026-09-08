<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformWalletTransactionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('platform_wallet_transactions', function (Blueprint $table) {
            $table->id();

            // credit = money in, debit = money out
            $table->enum('type', ['credit', 'debit']);

            // amount of transaction
            $table->decimal('amount', 15, 2);

            // running balance after this transaction
            $table->decimal('balance_after', 15, 2)->default(0);

            // description (e.g. "Commission from booking #12")
            $table->string('description')->nullable();

            // UNIQUE reference (IMPORTANT for idempotency)
            $table->string('reference')->unique();

            // Optional: link to booking
            $table->unsignedBigInteger('booking_id')->nullable();

            $table->timestamps();

            // Index for faster queries
            $table->index('type');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_wallet_transactions');
    }
}