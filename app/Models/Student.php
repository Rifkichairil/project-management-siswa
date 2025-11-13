<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school',
        'grade',
        'phone',
    ];

    // === RELATIONS ===
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packages()
    {
        return $this->hasMany(StudentPackage::class);
    }

    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}
