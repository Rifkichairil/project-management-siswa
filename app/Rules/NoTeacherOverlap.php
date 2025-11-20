<?php

namespace App\Rules;

use App\Models\ClassSchedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoTeacherOverlap implements ValidationRule
{
    protected $teacherId;
    protected $date;
    protected $timeStart;
    protected $timeEnd;
    protected $ignoreId;

    public function __construct($teacherId, $date, $timeStart, $timeEnd, $ignoreId = null)
    {
        $this->teacherId = $teacherId;
        $this->date = $date;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
        $this->ignoreId = $ignoreId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // stop jika field belum lengkap
        if (!$this->teacherId || !$this->date || !$this->timeStart || !$this->timeEnd) {
            return;
        }

        $exists = ClassSchedule::where('teacher_id', $this->teacherId)
            ->where('date', $this->date)
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
            $fail("This teacher already has a conflicting schedule at that time.");
        }
    }
}

