<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPackageFactory> */
    use HasFactory;

    protected $fillable = ['student_id','package_id','start_date','end_date','total_quota','used_quota','remaining_quota'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            $isUpdate = $model->exists;

            // Ambil data lama jika update
            $old = $isUpdate ? StudentPackage::find($model->id) : null;

            // Ambil type package sekarang
            $package = Package::find($model->package_id);
            if (!$package) return;

            $packageType = $package->type; // 'monthly' atau 'quota'


            // -------------------------------------------------------
            // ===============  CREATE LOGIC  ========================
            // -------------------------------------------------------
            if (!$isUpdate) {
                $model->remaining_quota = $model->total_quota ?? 0;
                return;
            }


            // -------------------------------------------------------
            // ===============  UPDATE LOGIC  ========================
            // -------------------------------------------------------

            $packageChanged     = $model->isDirty('package_id');
            $startDateChanged   = $model->isDirty('start_date');


            // ====== Jika PACKAGE berubah ======
            if ($packageChanged) {

                // Jika ganti paket → reset remaining seperti paket baru
                $model->remaining_quota = $model->total_quota ?? 0;
                return;
            }


            // ====== Jika TYPE = QUOTA → remaining tidak berubah ======
            if ($packageType === 'quota') {
                // Tidak boleh mengubah remaining quota
                // $model->remaining_quota = 100 ;
                $oldRemaining = $old->remaining_quota ?? 0;
                $model->remaining_quota = $oldRemaining + $model->total_quota;
                return;
            } else {
                if ($model->start_date <= $old->start_date) {
                    $model->remaining_quota = $model->total_quota + $old->remaining_quota;
                    return;
                }

                // Jika start_date naik = perpanjangan bulan
                $model->remaining_quota = ($old->remaining_quota ?? 0) + ($model->total_quota ?? 0);
                return;
            }


            // ====== TYPE = MONTHLY ======

            // if ($startDateChanged) {

            //     // Jika mundur → tidak boleh update remaining
            //     if ($model->start_date <= $old->start_date) {
            //         $model->remaining_quota = $old->remaining_quota;
            //         return;
            //     }

            //     // Jika start_date naik = perpanjangan bulan
            //     $model->remaining_quota = ($old->remaining_quota ?? 0) + ($model->total_quota ?? 0);
            //     return;
            // }

            // Default: tidak ada perubahan
            $model->remaining_quota = $old->remaining_quota;
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
