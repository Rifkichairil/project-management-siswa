<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassPackage>
 */
class ClassPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Paket 10 Kelas', 'Paket 20 Kelas', 'Paket 30 Kelas']),
            'total_sessions' => fake()->randomElement([10, 20, 30]),
            'price' => fake()->numberBetween(300000, 1000000),
        ];

    }
}
