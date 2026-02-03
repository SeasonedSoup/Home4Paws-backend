<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawsListing extends Model
{   
     use HasFactory;
    protected $table = 'paws_listings';

     protected $primaryKey = 'paws_id';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'status',
    ];

    protected $appends = ['reactions_count'];

    public function getReactionsCountAttribute()
    {
    return $this->reactions()->count();
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function photos()
    {
        return $this->hasMany(PawsPhoto::class, 'paws_id', 'paws_id');
    }

    public function reactions()
        {
            return $this->hasMany(Reaction::class, 'paws_id', 'paws_id');
        }
}
