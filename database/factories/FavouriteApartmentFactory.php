<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Apartment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FavouriteApartment>
 */
class FavouriteApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create([
                'status' => fake()->randomElement([User::STATUS_ACTIVE , User::STATUS_DELETED])
            ])->id,
            'apartment_id' => Apartment::factory()->create()->id,
        ];
    }
}
