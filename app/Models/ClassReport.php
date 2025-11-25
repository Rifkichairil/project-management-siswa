<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassReport extends Model
{
    /** @use HasFactory<\Database\Factories\ClassReportFactory> */
    use HasFactory;

    protected $fillable = ['class_schedule_id', 'topic', 'progress', 'notes', 'teacher_feedback'];

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

}

