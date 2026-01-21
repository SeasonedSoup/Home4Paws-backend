<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PawsListings extends Model
{
    protected $table = 'paws_listings';

    protected $fillable = [
        'user_id',
        'caption',
        'location',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function photos()
    {
        return $this->hasMany(PawsPhoto::class, 'paws_id', 'paws_id');
    }
}
