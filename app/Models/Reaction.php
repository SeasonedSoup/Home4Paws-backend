<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $table = 'reactions';
    protected $primaryKey = 'reaction_id';
    protected $fillable = [
        'paws_id',
        'user_id',
        'reaction_type',
    ];

    public function paws()
    {
        return $this->belongsTo(PawsListing::class, 'paws_id', 'paws_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
