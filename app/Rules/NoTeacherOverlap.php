<?php

namespace App\Rules;

use App\Models\ClassSchedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoTeacherOverlap implements ValidationRule
{
    public function __construct(
        protected $teacherId,
        protected $date,
        protected $timeStart,
        protected $timeEnd,
        protected $ignoreId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip jika input kosong
        if (!$this->teacherId || !$this->date || !$this->timeStart || !$this->timeEnd) {
            return;
        }

        // Jika update, cek apakah hanya status yang berubah
        if ($this->ignoreId) {
            $existing = ClassSchedule::find($this->ignoreId);

            if ($existing) {
                $sameTime      = $existing->time_start == $this->timeStart
                              && $existing->time_end   == $this->timeEnd;

                $sameDate      = $existing->date == $this->date;
                $sameTeacher   = $existing->teacher_id == $this->teacherId;

                // 🟢 User hanya ubah status, bukan waktu/durasi → skip validasi overlap
                if ($sameTime && $sameDate && $sameTeacher) {
                    return;
                }
            }
        }
// 🔥 Jalankan validasi overlap hanya terhadap jadwal aktif & tanggal kedepan
$exists = ClassSchedule::where('teacher_id', $this->teacherId)
    ->where('status', 'scheduled') // abaikan completed
    ->whereDate('date', '>=', now()->toDateString()) // abaikan masa lalu
    ->whereDate('date', $this->date) // hanya cek record tanggal sama
    ->when($this->ignoreId, fn ($q) => $q->where('id', '!=', $this->ignoreId))
    ->where(function ($q) {
        $q->whereBetween('time_start', [$this->timeStart, $this->timeEnd])
          ->orWhereBetween('time_end', [$this->timeStart, $this->timeEnd])
          ->orWhere(function ($q2) {
              $q2->where('time_start', '<=', $this->timeStart)
                 ->where('time_end', '>=', $this->timeEnd);
          });
    })
    ->exists();

if ($exists) {
    $fail("This teacher already has a conflicting schedule at this time.");
}

    }
}
