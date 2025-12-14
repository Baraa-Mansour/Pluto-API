<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\City;
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
                'status' => fake()->randomElement([
                    User::STATUS_ACTIVE
                ])
            ])->id,
            'city_id' => fake()->randomElement(City::pluck('id')),
            'area' => $this->faker->numberBetween(30, 200),
            'rooms' => $this->faker->numberBetween(1, 5),
            'floor' => $this->faker->numberBetween(1, 20),
            'description' => $this->faker->paragraph(1),// fix it to not long string(100),            
            'price' => $this->faker->randomFloat(2, 50000, 175000),
            'photo' => 'apartment_images/home.jpg',
            'is_deleted' => false
        ];
    }
}
