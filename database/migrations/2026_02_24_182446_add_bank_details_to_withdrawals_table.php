<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {

            $table->string('account_name')->nullable()->after('amount');
            $table->string('account_number')->nullable()->after('account_name');
            $table->string('bank_code')->nullable()->after('account_number');

            $table->string('recipient_code')->nullable()->change();
            $table->string('transfer_reference')->nullable()->change();

            $table->longText('transfer_response')->nullable()->change();

            $table->text('admin_note')->nullable()->after('transfer_response');

            // If your status is ENUM, you may need to modify it separately
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'account_name',
                'account_number',
                'bank_code',
                'admin_note'
            ]);
        });
    }
};