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
    Schema::table('paws_listings', function (Blueprint $table) {
        // String is enough for a URL; nullable so it's optional
        $table->string('fb_link')->nullable()->after('location');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paws_listings', function (Blueprint $table) {
            //
        });
    }
};
