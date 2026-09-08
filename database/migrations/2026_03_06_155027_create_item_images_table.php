<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemImagesTable extends Migration
{
    public function up(): void
{
    if (!Schema::hasTable('item_images')) {

        Schema::create('item_images', function (Blueprint $table) {

            $table->id();

            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image_path');

            $table->timestamps();

        });

    }
}

    public function down(): void
    {
        Schema::dropIfExists('item_images');
    }
}