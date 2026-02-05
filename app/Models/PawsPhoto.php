<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Important for URL logic

class PawsPhoto extends Model
{
    protected $table = 'paws_photos';

    protected $fillable = [
        'paws_id',
        'photo_path',
    ];

    // This tells Laravel to include 'photo_url' in your JSON response automatically
    protected $appends = ['photo_url'];

    /**
     * Accessor: Automatically generates the full URL for the frontend.
     * Your frontend will see "photo_url": "https://home4paws-backend.test..."
     */
    public function getPhotoUrlAttribute() {
    // Converts "paws_images/file.jpg" to "https://home4paws-backend.test"
    return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    public function paws()
    {
        return $this->belongsTo(PawsListing::class, 'paws_id', 'paws_id');
    }
}
