<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassReport extends Model
{
    /** @use HasFactory<\Database\Factories\ClassReportFactory> */
    use HasFactory;

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

}
