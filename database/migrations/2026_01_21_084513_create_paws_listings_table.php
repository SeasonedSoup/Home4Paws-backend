<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('paws_listings', function (Blueprint $table) {
            $table->id('paws_id'); // primary key as in your spec
            $table->unsignedBigInteger('user_id'); // FK to users table
            $table->text('caption');
            $table->string('location');
            $table->enum('status', ['available', 'adopted'])->default('available');
            $table->timestamps();

            // Foreign key constraint
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

