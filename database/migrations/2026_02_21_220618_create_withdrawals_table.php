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
    Schema::create('withdrawals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
        $table->decimal('amount', 15, 2);
        $table->string('bank_name')->nullable();
        $table->string('account_number')->nullable();
        $table->string('account_name')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('admin_note')->nullable();
        $table->string('transfer_reference')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('withdrawals', function (Blueprint $table) {
        $table->dropColumn([
            'bank_name',
            'account_number',
            'account_name',
            'admin_note',
            'transfer_reference'
        ]);
    });
}
};
