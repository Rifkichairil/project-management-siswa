<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data student & teacher dari tabel yang benar
        $students = Student::all();
        $teachers = Teacher::all();
        $subjects = Subject::all();

        // Safety check
        if ($students->count() == 0 || $teachers->count() == 0) {
            dd("Seeder Error: Pastikan students & teachers sudah terisi!");
        }

        // Buat 3 quota schedules
        for ($i = 0; $i < 3; $i++) {

            $student = $students[$i] ?? $students->random();
            $teacher = $teachers[$i] ?? $teachers->random();

            $start = Carbon::today()->addDays($i)->setTime(10 + $i, 0);
            $end   = (clone $start)->addHour();

            ClassSchedule::create([
                'student_id' => $student->id,   // ← BUKAN user_id
                'teacher_id' => $teacher->id,   // ← BUKAN user_id
                'subject_id' => $subjects->random()->id,
                'date' => $start->toDateString(),
                'time_start' => $start->format('H:i'),
                'time_end' => $end->format('H:i'),
                'status' => 'scheduled',
            ]);
        }

        // Buat 1 monthly schedule
        $student = $students->random();
        $teacher = $teachers->random();

        $start = Carbon::today()->setTime(14, 0);
        $end   = (clone $start)->addHour();

        ClassSchedule::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects->random()->id,
            'date' => $start->toDateString(),
            'time_start' => $start->format('H:i'),
            'time_end' => $end->format('H:i'),
            'status' => 'scheduled',
        ]);
    }


}
