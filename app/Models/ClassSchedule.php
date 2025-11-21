<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $fillable = ['student_id','teacher_id', 'subject_id','date','time_start','time_end','status'];

    protected static function booted()
    {
        static::updated(function ($schedule) {

            $old = $schedule->getOriginal('status');
            $new = $schedule->status;

            $durationMinutes = \Carbon\Carbon::parse($schedule->time_start)
                ->diffInMinutes(\Carbon\Carbon::parse($schedule->time_end));

            $quota = ceil($durationMinutes / 60);

            $studentPackage = \App\Models\StudentPackage::where('student_id', $schedule->student_id)
                ->orderByDesc('id')
                ->first();

            if (!$studentPackage) return;

            // scheduled → completed = kurangi
            if ($old !== 'completed' && $new === 'completed') {
                $studentPackage->remaining_quota -= $quota;
                $studentPackage->used_quota += $quota;
                $studentPackage->save();
            }

            // completed → cancelled/scheduled = restore
            if ($old === 'completed' && $new !== 'completed') {
                $studentPackage->remaining_quota += $quota;
                $studentPackage->used_quota -= $quota;
                $studentPackage->save();
            }
        });
    }


    public function student()
    {
    return $this->belongsTo(Student::class);
    }

    public function subject()
    {
    return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
    return $this->belongsTo(Teacher::class);
    }

    public function classReport()
    {
    return $this->hasOne(ClassReport::class);
    }
}
