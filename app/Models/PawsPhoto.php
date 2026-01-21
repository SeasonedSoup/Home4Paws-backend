<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PawsPhoto extends Model
{
    protected $table = 'paws_photos';

    protected $fillable = [
        'paws_id',
        'photo_path',
    ];

    // Each photo belongs to a post
    public function paws()
    {
        return $this->belongsTo(PawsListing::class, 'paws_id', 'paws_id');
    }
}

