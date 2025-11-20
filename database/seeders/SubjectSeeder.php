<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            'Mathematics',
            'Science',
            'English',
            'Physics',
            'Biology',
            'Chemistry',
            'History',
            'Geography',
        ];

        foreach ($subjects as $name) {
            Subject::create([
                'name' => $name,
            ]);
        }
    }

}
