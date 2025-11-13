<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'curriculum_id',
        'date',
        'duration',
        'location',
    ];

    // === RELATIONS ===
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class, 'schedule_id');
    }

}
