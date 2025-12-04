<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
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
            'location' => $this->faker->address(),
            'floor' => $this->faker->numberBetween(1, 20),
            'description' => $this->faker->paragraph(1),// fix it to not long string(100),
            'size' => $this->faker->numberBetween(30, 200),
            'rooms' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->randomFloat(2, 100, 350),
            'photo' => 'apartment_images/home.jpg'
        ];
    }
}
