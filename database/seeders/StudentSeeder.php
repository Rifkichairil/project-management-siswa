<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Ambil semua user dengan role "student"
        $students = User::where('role', 'student')->get();

        foreach ($students as $user) {
            Student::create([
                'user_id'        => $user->id,
                'school'         => $faker->randomElement([
                    'SMPN 1 Jakarta',
                    'SMPN 12 Bandung',
                    'SMA 5 Jakarta',
                    'SMA 3 Bandung',
                    'SMAN 1 Surabaya'
                ]),
                'grade'          => $faker->randomElement(['7', '8', '9', '10', '11', '12']),
                'parent_name'    => $faker->name(),
                'parent_contact' => $faker->phoneNumber(),
            ]);
        }
    }
}
