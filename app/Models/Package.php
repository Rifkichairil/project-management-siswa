<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /** @use HasFactory<\Database\Factories\PackageFactory> */
    use HasFactory;

    protected $fillable = ['name','type','quota_classes','price'];

    public function studentPackages()
    {
        return $this->hasMany(StudentPackage::class);
    }
}
