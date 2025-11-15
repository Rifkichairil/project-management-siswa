<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $fillable = ['student_id','teacher_id','date','time_start','time_end','status'];

    public function student()
    {
    return $this->belongsTo(Student::class);
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
