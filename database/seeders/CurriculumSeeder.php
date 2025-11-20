<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $curriculums = [
            [
                'name' => 'National Curriculum',
                'description' => 'Standard national education curriculum.',
            ],
            [
                'name' => 'Cambridge',
                'description' => 'International Cambridge curriculum framework.',
            ],
            [
                'name' => 'IB',
                'description' => 'International Baccalaureate curriculum.',
            ],
            [
                'name' => 'Kurikulum Merdeka',
                'description' => 'Newest Indonesian curriculum focusing on flexibility and competency-based learning.',
            ],
        ];

        foreach ($curriculums as $data) {
            Curriculum::create($data);
        }
    }

}
