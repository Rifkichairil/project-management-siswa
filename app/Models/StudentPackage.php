<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPackageFactory> */
    use HasFactory;

    protected $fillable = ['student_id','package_id','start_date','end_date','total_quota','remaining_quota'];
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Hanya jalankan jika start_date benar2 berubah
            if ($model->isDirty('start_date')) {

                // dd($model);
                $old = StudentPackage::where('id',  $model->id)
                    ->orderByDesc('start_date')
                    ->first();

                if ($old && $model->start_date > $old->start_date) {

                    // Ambil value lama dari database
                    $oldRemaining = $old->remaining_quota ?? 0;
                    $model->remaining_quota = $oldRemaining + $model->total_quota;
                }
            }
        });
    }


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
