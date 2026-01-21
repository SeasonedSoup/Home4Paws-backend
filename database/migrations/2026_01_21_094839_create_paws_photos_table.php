<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paws_photos', function (Blueprint $table) {
            $table->id('photo_id');               // Primary key
            $table->unsignedBigInteger('paws_id'); // FK to posts
            $table->string('photo_path');         // Local path or cloud URL
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('paws_id')
                  ->references('paws_id')
                  ->on('paws_listings')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paws_photos');
    }
};
