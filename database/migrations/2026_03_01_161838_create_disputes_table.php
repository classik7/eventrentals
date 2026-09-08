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
    Schema::create('disputes', function (Blueprint $table) {
        $table->id();

        $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
        $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

        $table->text('reason');

        $table->enum('status', [
            'open',
            'under_review',
            'resolved',
            'rejected'
        ])->default('open');

        $table->decimal('refund_amount', 15, 2)->nullable();
        $table->text('admin_note')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
