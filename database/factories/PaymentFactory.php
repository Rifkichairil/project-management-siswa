<?php

namespace Database\Factories;

use App\Models\ClassPackage;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'amount' => fake()->numberBetween(300000, 1000000),
            'date' => now()->subDays(fake()->numberBetween(1, 30)),
            'status' => fake()->randomElement(['paid', 'pending']),
        ];

    }
}
