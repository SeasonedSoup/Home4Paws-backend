<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_notifications', function (Blueprint $table) {
            $table->id();
            
            // The person getting the notification (the post owner)
            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // The person who did the action
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // The specific post involved
            $table->unsignedBigInteger('paws_id');
            // Make sure this foreign key constraint is correct for your schema
            $table->foreign('paws_id')
                  ->references('paws_id') 
                  ->on('paws_listings')
                  ->onDelete('cascade');

            $table->string('type'); // e.g., 'like', 'email_copy'
            $table->text('message');
            $table->boolean('is_read')->default(false); // Simple read status
            $table->timestamps(); // Provides 'created_at' for the "7 minutes ago" text

            // Prevents the same sender from notifying the same receiver 
            // about the same post for the same action twice.
            $table->unique(
                ['receiver_id', 'sender_id', 'paws_id', 'type'], 
                'unique_inbox_entry'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_notifications');
    }
};
    
