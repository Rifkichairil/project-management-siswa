<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'school' => $this->faker->company() . ' School',
            'grade' => $this->faker->randomElement(['7', '8', '9', '10', '11', '12']),
            'parent_name' => $this->faker->name(),
            'parent_contact' => $this->faker->phoneNumber(),
        ];
    }
}
