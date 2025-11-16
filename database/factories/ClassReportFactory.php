<?php

namespace Database\Factories;

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
            // 'class_schedule_id' => \App\Models\ClassSchedule::inRandomOrder()->value('id'),
            'class_schedule_id' => \App\Models\ClassSchedule::inRandomOrder()->value('id'),
            'topic' => $this->faker->sentence(),
            'progress' => $this->faker->sentence(),
            'notes' => $this->faker->paragraph(),
            'teacher_feedback' => $this->faker->sentence(),
        ];
    }
}
