<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassPackage extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name', 'total_sessions', 'price'];

    // === RELATIONS ===
    public function studentPackages()
    {
        return $this->hasMany(StudentPackage::class, 'package_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'package_id');
    }

}
