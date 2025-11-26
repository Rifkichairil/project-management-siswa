<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class NamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // === Generate 5 Teachers ===
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name'     => $faker->name(),
                'email'    => "teacher{$i}@example.com",
                'password' => Hash::make('password'),  // default: password
                'role'     => 'teacher',
                'isActive' => true,                    // aktif
            ]);
        }

        // === Generate 5 Students ===
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name'     => $faker->name(),
                'email'    => "student{$i}@example.com",
                'password' => Hash::make('password'),
                'role'     => 'student',
                'isActive' => true,
            ]);
        }
    }
}
