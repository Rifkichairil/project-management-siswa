<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Student;
use App\Models\StudentPackage;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $packages = Package::all();

        if ($students->count() == 0 || $packages->count() == 0) {
            dd("Seeder Error: Students atau Packages belum ada datanya!");
        }

        // Ambil packages berdasarkan type
        $quotaPackages   = $packages->where('type', 'quota')->take(3);
        $monthlyPackage  = $packages->where('type', 'monthly')->first();

        // Buat 3 package quota
        foreach ($quotaPackages as $index => $package) {
            $student = $students[$index] ?? $students->random();

            StudentPackage::create([
                'student_id'      => $student->id,
                'package_id'      => $package->id,
                'start_date'      => Carbon::today()->toDateString(),
                'end_date'        => null,
                'total_quota'     => $package->quota_classes,
                'remaining_quota' => $package->quota_classes,
            ]);
        }

        // Buat 1 package monthly
        if ($monthlyPackage) {
            $student = $students->random();
            $start   = Carbon::today();
            $end     = $start->copy()->addMonth();

            StudentPackage::create([
                'student_id'      => $student->id,
                'package_id'      => $monthlyPackage->id,
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'total_quota'     => null,
                'remaining_quota' => null,
            ]);
        }
    }

}
