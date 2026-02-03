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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id('reaction_id');
            $table->unsignedBigInteger('paws_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('reaction_type', ['like'])->default('like');
            $table->timestamps();

            $table->foreign('paws_id')->references('paws_id')->on('paws_listings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['paws_id', 'user_id']); // one like per user
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
