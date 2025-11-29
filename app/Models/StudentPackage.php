<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPackageFactory> */
    use HasFactory;

    protected $fillable = ['student_id','package_id','start_date','end_date','total_quota','used_quota','remaining_quota', 'status' , 'old_package_to_deactivate'];
    protected $old_package_to_deactivated; // bukan attribute database


    protected static function booted()
    {
        // --- 1. LOGIKA SAAT DATA BARU DIBUAT (CREATING/UPSIZE/DOWNSIZE) ---
        static::creating(function ($model) {
            // Cek apakah ada paket aktif sebelumnya untuk siswa yang sama
            $activeOldPackage = StudentPackage::query()
                ->where('student_id', $model->student_id)
                ->where('status', 'active')
                ->first();
            // dd($activeOldPackage);

            $package = Package::whereId($model->package_id)->first();

            // Jika ini adalah paket pertama siswa ATAU penggantian paket
            if (!$activeOldPackage) {
                // Logika Paket Baru (Pembelian Awal)
                $model->remaining_quota = $model->total_quota;
            } else {
                // Logika Penggabungan Kuota (Upsize/Downsize)

                // 1. Ambil Sisa Kuota Lama
                $oldRemainingQuota = $activeOldPackage->remaining_quota;

                // 2. Hitung Kuota Baru (Selalu Ditambah)
                // New Remaining = Total Baru + Sisa Lama
                $model->total_quota     = $package->quota_classes;
                $model->remaining_quota = $model->total_quota + $oldRemainingQuota;
                $model->end_date        = Carbon::parse($model->start_date)->addMonth();

                // 3. Nonaktifkan Paket Lama (Update status)
                // Ini harus dilakukan setelah paket baru berhasil dibuat/disimpan
                // Agar tidak terjadi race condition, kita tandai paket lama untuk di-update.
                // Namun, karena kita ada di event 'creating', kita tunda ke event 'created'.
                $model->old_package_to_deactivated = $activeOldPackage;

                // Set status package baru menjadi ACTIVE
                $model->status = 'active';
            }

            // Atur kuota terpakai menjadi 0 untuk paket yang baru dibuat
            $model->used_quota = 0;

            // Pastikan status default aktif jika belum diatur
            if (empty($model->status)) {
                 $model->status = 'active';
            }
        });

        // Event setelah data baru benar-benar tersimpan di DB
        static::created(function ($model) {
            // Nonaktifkan paket lama yang sudah ditandai
            if (isset($model->old_package_to_deactivated)) {
                $model->old_package_to_deactivated->update([
                    'status' => 'inactive'
                ]);
            }
        });

        // --- 2. LOGIKA SAAT DATA DIUBAH (HANYA KOREKSI/PENGURANGAN KUOTA) ---
        static::updating(function (StudentPackage $package) {
            // Logika untuk memastikan remaining quota terupdate jika total/used diubah
            // Ini biasanya terjadi saat admin mengoreksi data secara manual.

            // Jika Used Quota berubah, hitung ulang Remaining Quota
            if ($package->isDirty('used_quota') || $package->isDirty('total_quota')) {
                 $package->remaining_quota = $package->total_quota - $package->used_quota;
            }
        });
    }
                // dd($originalRemaining, $originalQuota, $package->quota_classes, min($oldRemaining + $newQuota, $newQuota), ($oldRemaining - $newQuota));


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

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($model) {

    //         $isUpdate = $model->exists;

    //         // Ambil data lama jika update
    //         $old = $isUpdate ? StudentPackage::find($model->id) : null;

    //         // Ambil type package sekarang
    //         $package = Package::find($model->package_id);
    //         if (!$package) return;

    //         $packageType = $package->type; // 'monthly' atau 'quota'


    //         // -------------------------------------------------------
    //         // ===============  CREATE LOGIC  ========================
    //         // -------------------------------------------------------
    //         if (!$isUpdate) {
    //             $model->remaining_quota = $model->total_quota ?? 0;
    //             return;
    //         }


    //         // -------------------------------------------------------
    //         // ===============  UPDATE LOGIC  ========================
    //         // -------------------------------------------------------

    //         $packageChanged     = $model->isDirty('package_id');
    //         $startDateChanged   = $model->isDirty('start_date');


    //         // ====== Jika PACKAGE berubah ======
    //         if ($packageChanged) {

    //             // Jika ganti paket → reset remaining seperti paket baru
    //             $model->remaining_quota = $model->total_quota ?? 0;
    //             return;
    //         }


    //         // ====== Jika TYPE = QUOTA → remaining tidak berubah ======
    //         if ($packageType === 'quota') {
    //             // Tidak boleh mengubah remaining quota
    //             // $model->remaining_quota = 100 ;
    //             $oldRemaining = $old->remaining_quota ?? 0;
    //             $model->remaining_quota = $oldRemaining + $model->total_quota;
    //             return;
    //         } else {
    //             if ($model->start_date <= $old->start_date) {
    //                 $model->remaining_quota = $model->total_quota + $old->remaining_quota;
    //                 return;
    //             }

    //             // Jika start_date naik = perpanjangan bulan
    //             $model->remaining_quota = ($old->remaining_quota ?? 0) + ($model->total_quota ?? 0);
    //             return;
    //         }



    //         $model->remaining_quota = $old->remaining_quota;
    //     });
    // }
}
