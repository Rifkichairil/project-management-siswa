<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['quota', 'monthly']);
        return [
            'name' => $type === 'quota'
                ? $this->faker->randomElement(['10 Classes', '20 Classes', '30 Classes'])
                : 'Monthly Unlimited',

            'type' => $type,
            'quota_classes' => $type === 'quota' ? $this->faker->randomElement([10, 20, 30]) : null,
            'price' => $this->faker->numberBetween(500000, 2000000),
        ];
    }
}
