<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'student_id',
        'schedule_id',
        'status',
    ];

    // === RELATIONS ===
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public function report()
    {
        return $this->hasOne(ClassReport::class, 'student_class_id');
    }

}
