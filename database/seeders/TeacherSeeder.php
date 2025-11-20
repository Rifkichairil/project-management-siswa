<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Ambil semua user yang rolenya "teacher"
        $teachers = User::where('role', 'teacher')->get();

        foreach ($teachers as $user) {
            Teacher::create([
                'user_id' => $user->id,
                'expertise'  => $faker->randomElement([
                    'Mathematics', 'Science', 'English',
                    'Physics', 'Biology', 'Chemistry',
                    'History', 'Geography'
                ]),
                'curriculum' => $faker->randomElement([
                    'National Curriculum',
                    'Cambridge',
                    'IB',
                    'Kurikulum Merdeka'
                ]),
            ]);
        }
    }
}
