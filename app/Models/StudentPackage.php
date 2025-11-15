<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPackageFactory> */
    use HasFactory;

    protected $fillable = ['student_id','package_id','start_date','end_date','total_quota','remaining_quota'];

    public function student()
    {
    return $this->belongsTo(Student::class);
    }


    public function package()
    {
    return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
