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

        $teachers = User::with('teacher')->where('role', 'teacher')->get();


        foreach ($teachers as $user) {
            if ($user->teacher != null) {
                # code...
                // Tentukan apakah teacher mengajar kedua tipe atau salah satu saja
                $teaching_type = $faker->boolean(30)
                    ? ['private', 'group']   // 30% guru ngajar keduanya
                    : [$faker->randomElement(['private', 'group'])]; // 70% hanya satu tipe

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
                    'teaching_type' => $teaching_type,
                ]);
            }

        }
    }

}
