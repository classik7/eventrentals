<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // 🪪 ID INFORMATION
            $table->string('id_type')->nullable()->after('kyc_status');
            $table->string('id_number')->nullable()->after('id_type');

            // 📂 DOCUMENTS
            $table->string('id_document')->nullable()->after('id_number');
            $table->string('selfie')->nullable()->after('id_document');

            // 📅 VERIFICATION
            $table->timestamp('kyc_verified_at')->nullable()->after('selfie');

            // ❌ REJECTION REASON
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'id_type',
                'id_number',
                'id_document',
                'selfie',
                'kyc_verified_at',
                'kyc_rejection_reason'
            ]);
        });
    }
};