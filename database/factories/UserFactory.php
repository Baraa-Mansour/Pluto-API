<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->unique()->numerify('09########'),
            'role' => fake()->randomElement([User::ROLE_ADMIN , User::ROLE_USER]),
            'status' => fake()->randomElement([User::STATUS_ACTIVE,User::STATUS_DELETED ,User::STATUS_REGISTERED]),
            'birth_date' => fake()->date(),
            'password' => static::$password ??= Hash::make('password'),
            'profile_image' => fake()->randomElement(['profile_images/man.jpg' , 'profile_images/woman.jpg']),
            'id_image' => 'id_images/id.jpg',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
