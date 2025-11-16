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

        if ($type === 'quota') {
            $quotaOptions = [10, 20, 30];
            $quota = $this->faker->randomElement($quotaOptions);

            return [
                'name' => "{$quota} Classes",
                'type' => 'quota',
                'quota_classes' => $quota,
                'price' => $this->faker->numberBetween(500000, 2000000),
            ];
        }

        // type = monthly
        $monthlyOptions = ['4 Meetings', '6 Meetings', '8 Meetings', '12 Meetings', 'Unlimited'];
        $meetingName = $this->faker->randomElement($monthlyOptions);

        return [
            'name' => "Monthly {$meetingName}",
            'type' => 'monthly',
            'quota_classes' => null,
            'price' => $this->faker->numberBetween(500000, 2000000),
        ];
    }
}
