<?php

namespace App\Rules;

use App\Models\ClassSchedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoScheduleOverlap implements ValidationRule
{
    protected $date;
    protected $teacherId;
    protected $studentId;
    protected $timeStart;
    protected $ignoringId;

    public function __construct($date, $teacherId, $studentId, $timeStart, $ignoringId = null)
    {
        $this->date = $date;
        $this->teacherId = $teacherId;
        $this->studentId = $studentId;
        $this->timeStart = $timeStart;
        $this->ignoringId = $ignoringId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $newDate = $this->date;
        $newTeacherId = $this->teacherId;
        $newStudentId = $this->studentId;
        $newTimeStart = $this->timeStart;
        $newTimeEnd = $value; // time_end

        // 1. Cek jika data esensial tidak ada
        if (is_null($newDate) || is_null($newTeacherId) || is_null($newStudentId) || is_null($newTimeStart)) {
            $fail('Schedule data is incomplete for conflict validation.');
            return;
        }

        // 2. Query untuk mencari jadwal yang bentrok
        $overlapExists = ClassSchedule::query()
            ->where('date', $newDate)
            ->where(function ($query) use ($newTeacherId, $newStudentId) {
                // Bentrok untuk guru ATAU siswa
                $query->where('teacher_id', $newTeacherId)
                      ->orWhere('student_id', $newStudentId);
            })
            ->where(function ($query) use ($newTimeStart, $newTimeEnd) {
                // Logika Overlapping Waktu: (New Start < Existing End) AND (New End > Existing Start)
                $query->where('time_start', '<', $newTimeEnd)
                      ->where('time_end', '>', $newTimeStart);
            })
            ->when($this->ignoringId, function ($query, $id) {
                // Abaikan jadwal yang sedang diedit
                return $query->where('id', '!=', $id);
            })
            ->exists();

        // 3. Jika ditemukan overlap, gagal
        if ($overlapExists) {
            $fail('Schedule conflict detected with an existing class for either the teacher or the student on this date.');
        }
    }
}
