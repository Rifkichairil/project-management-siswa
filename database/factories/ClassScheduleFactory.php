<?php

namespace Database\Factories;

use App\Models\Curriculum;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSchedule>
 */
class ClassScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'curriculum_id' => Curriculum::factory(),
            'date' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'duration' => 60,
            'location' => fake()->randomElement(['Online', 'Jakarta Selatan', 'Depok', 'BSD']),
        ];

    }
}
