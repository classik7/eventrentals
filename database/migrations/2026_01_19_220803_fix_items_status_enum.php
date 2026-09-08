<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE items 
            MODIFY status 
            ENUM('active', 'paused') 
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE items 
            MODIFY status 
            ENUM('active') 
            NOT NULL DEFAULT 'active'
        ");
    }
};
