<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
{
    Schema::create('paws_listings', function (Blueprint $table) {
        $table->id('paws_id'); // Standard Laravel ID (or keep $table->id('paws_id') if preferred)
        $table->unsignedBigInteger('user_id');
        $table->string('title'); // Changed from caption to title
        $table->text('description'); // Added description since it's in your fillable/query
        $table->string('location');
        $table->enum('status', ['available', 'adopted'])->default('available');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paws_listings');
    }
};

