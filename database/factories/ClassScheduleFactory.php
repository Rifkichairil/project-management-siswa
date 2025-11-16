<?php

namespace Database\Factories;

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
            'student_id' => \App\Models\Student::inRandomOrder()->value('id'),
            'teacher_id' => \App\Models\Teacher::inRandomOrder()->value('id'),
            'subject_id' => \App\Models\Subject::inRandomOrder()->value('id'),
            'date' => $this->faker->date(),
            'time_start' => '10:00',
            'time_end' => '11:00',
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled']),
        ];
    }
}
