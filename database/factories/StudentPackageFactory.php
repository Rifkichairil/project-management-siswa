<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentPackage>
 */
class StudentPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'package_id' => ClassPackage::factory(),
            'start_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'remaining_sessions' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(['active', 'finished', 'expired']),
        ];

    }
}
