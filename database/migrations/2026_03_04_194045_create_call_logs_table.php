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
    Schema::create('call_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('dispute_id')->constrained()->cascadeOnDelete();
        $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
        $table->string('phone_called');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
