<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Curriculum;
use App\Models\ClassPackage;
use App\Models\StudentPackage;
use App\Models\ClassSchedule;
use App\Models\StudentClass;
use App\Models\ClassReport;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       // Users
       User::factory()->count(5)->create();

       // Teachers
       Teacher::factory()->count(5)->create();

       // Students
       Student::factory()->count(5)->create();

       // Curriculums
       Curriculum::factory()->count(5)->create();

       // Packages
       ClassPackage::factory()->count(5)->create();

       // Student Packages
       StudentPackage::factory()->count(5)->create();

       // Schedules
       ClassSchedule::factory()->count(5)->create();

       // Student Classes
       StudentClass::factory()->count(5)->create();

       // Reports
       ClassReport::factory()->count(5)->create();

       // Payments
       Payment::factory()->count(5)->create();
    }
}
