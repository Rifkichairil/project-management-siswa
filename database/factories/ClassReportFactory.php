<?php

namespace Database\Factories;

use App\Models\StudentClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassReport>
 */
class ClassReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_class_id' => StudentClass::factory(),
            'notes' => fake()->sentence(),
            'score' => fake()->numberBetween(60, 100),
            'created_by' => Teacher::factory(),
        ];

    }
}
