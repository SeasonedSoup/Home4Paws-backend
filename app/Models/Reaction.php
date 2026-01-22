<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $fillable = [
        'paws_id',
        'reacted_by',
        'reaction_type',
    ];

    public function paws()
    {
        return $this->belongsTo(PawsListing::class, 'paws_id', 'paws_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reacted_by', 'id');
    }
}
