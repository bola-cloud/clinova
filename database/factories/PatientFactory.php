<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'age' => fake()->numberBetween(1, 100),
            'weight' => fake()->randomFloat(2, 5, 120),
            'address' => fake()->address(),
            'family_history' => fake()->sentence(),
        ];
    }
}
