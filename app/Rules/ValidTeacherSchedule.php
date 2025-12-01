<?php

namespace App\Rules;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\Teacher;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTeacherSchedule implements ValidationRule
{
    protected int $teacherId;
    protected int $studentId;
    protected string $timeStart;
    protected string $timeEnd;
    protected string $date;
    protected ?int $ignoringId;

    public function __construct(int $teacherId, int $studentId, string $timeStart, string $timeEnd, string $date, ?int $ignoringId = null)
    {
        $this->teacherId = $teacherId;
        $this->studentId = $studentId;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
        $this->date = $date;
        $this->ignoringId = $ignoringId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $teacher = Teacher::find($this->teacherId);
        $student = Student::with(['activePackage.package'])->whereId($this->studentId)->first();

        if (!$teacher || !$student) {
            $fail("Guru atau murid tidak ditemukan.");
            return;
        }

        $packageType = $student->activePackage->package->type; // quota, monthly, group
        // dd($student->activePackage->package->type, $teacher->teaching_type);
        // 1️⃣ Cek teacher type vs package type
        if ($teacher->teaching_type === 'private' && $packageType === 'group') {
            $fail("Guru ini hanya bisa mengajar private.");
            return;
        }

        if ($teacher->teaching_type === 'group' && in_array($packageType, ['quota','monthly'])) {
            $fail("Guru ini hanya bisa mengajar group.");
            return;
        }

        // 2️⃣ Cek overlapping jadwal
        $query = ClassSchedule::where('teacher_id', $this->teacherId)
            ->where('date', $this->date)
            ->where(function ($q) {
                $q->whereBetween('time_start', [$this->timeStart, $this->timeEnd])
                  ->orWhereBetween('time_end', [$this->timeStart, $this->timeEnd])
                  ->orWhere(fn($q2) => $q2->where('time_start', '<=', $this->timeStart)
                                            ->where('time_end', '>=', $this->timeEnd));
            });

        if ($this->ignoringId) {
            $query->where('id', '!=', $this->ignoringId);
        }

        $existingSchedules = $query->get();

        foreach ($existingSchedules as $schedule) {
            $existingPackageType = $schedule->student->activePackage->package->type;
            $existingTeacherId = $schedule->teacher_id;
            dd($schedule->student,$schedule->student->activePackage->package, $schedule->teacher_id, $schedule->teacher->teaching_type);
            // dd($schedule->student->activePackage->package->type);


            // Private slot: blok semua overlapping (private & group)
            if (in_array($existingPackageType, ['quota','monthly']) || in_array($packageType, ['quota','monthly'])) {
                $fail("Slot waktu bertabrakan dengan jadwal private.");
                return;
            }

            // 2️⃣ Group slot: bisa sharing hanya jika:
            // - existing schedule = group
            // - student package = group
            // - teacher sama
            if ($existingPackageType === 'group' && $packageType === 'group') {
                if ($schedule->teacher_id !== $this->teacherId) {
                    // beda guru → tidak boleh sharing
                    $fail("Slot waktu grup bertabrakan dengan jadwal guru lain.");
                    return;
                }

                // optional: cek jam overlapping jika mau batasi slot group
                if (!($schedule->time_start === $this->timeStart && $schedule->time_end === $this->timeEnd)) {
                    $fail("Slot waktu grup tidak cocok dengan jadwal guru.");
                    return;
                }

                // kalau teacher sama dan jam sama → boleh masuk
                // bisa cek kapasitas maksimum group di sini
            }
        }
    }
}
