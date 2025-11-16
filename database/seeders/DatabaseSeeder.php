<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Curriculum;
use App\Models\StudentPackage;
use App\Models\ClassSchedule;
use App\Models\ClassReport;
use App\Models\Package;
use App\Models\Subject;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // USERS
        User::factory()->count(10)->create();

        // TEACHERS
        Teacher::factory()->count(10)->create();

        // STUDENTS
        Student::factory()->count(10)->create();

        // SUBJECT
        Subject::factory()->count(10)->create();

        // CURRICULUMS
        Curriculum::factory()->count(10)->create();

        // PACKAGES
        Package::factory()->count(10)->create();

        // STUDENT PACKAGES
        StudentPackage::factory()->count(10)->create();

        // CLASS SCHEDULES
        ClassSchedule::factory()->count(10)->create();

        // CLASS REPORTS
        ClassReport::factory()->count(10)->create();
    }
}
