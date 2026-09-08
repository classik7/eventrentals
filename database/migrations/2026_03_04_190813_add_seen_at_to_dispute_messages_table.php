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
    Schema::table('dispute_messages', function (Blueprint $table) {
        $table->timestamp('seen_at')->nullable()->after('attachment');
    });
}

public function down()
{
    Schema::table('dispute_messages', function (Blueprint $table) {
        $table->dropColumn('seen_at');
    });
}
};
