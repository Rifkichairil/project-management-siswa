<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Hitung jumlah role yang sudah ada
        $adminCount   = \App\Models\User::where('role', 'admin')->count();
        $teacherCount = \App\Models\User::where('role', 'teacher')->count();
        $studentCount = \App\Models\User::where('role', 'student')->count();

        // Tentukan role berdasarkan quota
        if ($adminCount < 2) {
            $role = 'admin';
        } elseif ($teacherCount < 5) {
            $role = 'teacher';
        } elseif ($studentCount < 5) {
            $role = 'student';
        } else {
            $role = fake()->randomElement(['admin', 'teacher', 'student']);
        }

        // Tentukan prefix name berdasarkan role
        $prefix = match($role) {
            'admin'   => 'Admin ',
            'teacher' => 'Teacher ',
            'student' => 'Student ',
            default   => '',
        };

        return [
            'name' => $prefix . fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => $role,
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
    }


    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
