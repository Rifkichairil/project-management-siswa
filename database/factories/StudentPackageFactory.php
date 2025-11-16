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
       $package = \App\Models\Package::inRandomOrder()->first();

        return [
            'student_id' => \App\Models\Student::inRandomOrder()->value('id'),
            'package_id' => $package->id,
            'start_date' => now(),
            'end_date' => $package->type === 'monthly' ? now()->addMonth() : null,
            'total_quota' => $package->quota_classes,
            'remaining_quota' => $package->quota_classes,
        ];
    }
}
