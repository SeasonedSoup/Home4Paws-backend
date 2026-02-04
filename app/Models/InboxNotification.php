<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboxNotification extends Model
{
    protected $fillable = ['receiver_id', 'sender_id', 'paws_id', 'type', 'message', 'is_read'];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function paws() {
        return $this->belongsTo(PawsListing::class, 'paws_id', 'paws_id');
    }
}
