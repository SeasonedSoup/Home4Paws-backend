<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PawsListing;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 posts (and 10 users automatically)
        PawsListing::factory(10)->create();
    }
}
