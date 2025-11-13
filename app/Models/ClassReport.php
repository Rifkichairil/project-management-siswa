<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassReport extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'student_class_id',
        'notes',
        'score',
        'created_by',
    ];

    // === RELATIONS ===
    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'created_by');
    }

}
