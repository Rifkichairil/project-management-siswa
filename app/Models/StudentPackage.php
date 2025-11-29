<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPackageFactory> */
    use HasFactory;

    protected $fillable = ['student_id','package_id','start_date','end_date','total_quota','used_quota','remaining_quota', 'status'];


    protected static function booted()
    {
        /**
         * ==============================
         * 📍 ON PACKAGE CREATE (Topup/Upgrade/Downgrade)
         * ==============================
         */
        static::creating(function ($model) {

            $activePackage = self::where('student_id', $model->student_id)
                ->where('status', 'active')
                ->first();

            $package = Package::find($model->package_id);

            // Jika sudah ada paket aktif sebelumnya → nonaktifkan & carry remaining
            if ($activePackage) {

                $prevRemaining = $activePackage->remaining_quota ?? 0;

                $model->status          = 'active';
                $model->total_quota     = $package->quota_classes;
                $model->remaining_quota = $prevRemaining + $model->total_quota; // merge quota lama + baru
                $model->start_date      = $model->start_date ?? now();
                $model->end_date        = $model->end_date ?? Carbon::parse($model->start_date)->addMonth();

                // nonaktifkan paket sebelumnya 🔥
                $activePackage->update(['status' => 'inactive']);

            } else {

                // First package (tidak ada paket sebelumnya)
                $model->status          = 'active';
                $model->total_quota     = $package->quota_classes;
                $model->remaining_quota = $package->quota_classes;
                $model->start_date      = $model->start_date ?? now();
                $model->end_date        = $model->end_date ?? Carbon::parse($model->start_date)->addMonth();
            }
        });

        static::updating(function ($quota) {

            if ($quota->getOriginal('status') !== 'active') return;

            $old_remaining = $quota->getOriginal('remaining_quota');
            $old_used      = $quota->getOriginal('used_quota');
            $old_package   = $quota->getOriginal('package_id');

            $new_package   = $quota->package_id; // input user
            $package       = Package::find($new_package); // ambil data paket
            $quota_amount  = $package->quota_classes; // quota yang didapat dari 1x pembelian paket baru

            /*
            |----------------------------------------------------------
            | CASE 1 — TOP-UP (paket sama)
            | package_id sama → user membeli ulang paketnya (not upgrade/downgrade)
            |----------------------------------------------------------
            */
            if ($new_package == $old_package) {

                $quota->status = 'inactive'; // matikan record lama

                // buat record baru, total quota = paket quota, sisa lama ikut
                self::create([
                    'student_id'      => $quota->student_id,
                    'package_id'      => $new_package,
                    'start_date'      => now(),
                    'end_date'        => now()->addMonth(),
                    'total_quota'     => $quota_amount, // ✨ otomatis dari package
                    'used_quota'      => 0,
                    'remaining_quota' => $old_remaining + $quota_amount,
                    'status'          => 'active'
                ]);

                return false;
            }

            /*
            |----------------------------------------------------------
            | CASE 2 — PAKET BERBEDA → dianggap upgrade/downgrade
            |----------------------------------------------------------
            | quota baru ditentukan dari package->quota_classes, bukan input
            */
            $new_total = $quota_amount;

            // VALIDASI DOWNSIZE
            if ($new_total < $old_used) {
                throw new \Exception("Gagal downgrade! Usage ($old_used) lebih tinggi dari quota baru ($new_total).");
            }

            // nonaktifkan data lama
            $quota->status = 'inactive';

            // buat quota baru dengan paket berbeda
            self::create([
                'student_id'      => $quota->student_id,
                'package_id'      => $new_package,
                'start_date'      => now(),
                'end_date'        => now()->addMonth(),
                'total_quota'     => $new_total,
                'used_quota'      => 0,
                'remaining_quota' => $new_total + $old_remaining,
                'status'          => 'active'
            ]);

            return false;
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
