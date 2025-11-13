<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'student_id',
        'package_id',
        'start_date',
        'remaining_sessions',
        'status',
    ];

    // === RELATIONS ===
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function package()
    {
        return $this->belongsTo(ClassPackage::class, 'package_id');
    }

}
