<?php

namespace Database\Factories;

use App\Models\PawsListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PawsListingFactory extends Factory
{
    protected $model = PawsListing::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->randomElement(['Caloocan', 'Metro Manila', 'Quezon City']),
            'status' => 'available',
        ];
    }
}

