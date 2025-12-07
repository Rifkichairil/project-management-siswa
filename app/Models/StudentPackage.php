<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class StudentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPackageFactory> */
    use HasFactory;

    protected $fillable = ['student_id','package_id','start_date','end_date','total_quota','used_quota','remaining_quota', 'status' , 'old_package_to_deactivate'];
    protected $old_package_to_deactivated; // bukan attribute database
    protected $skipQuotaRecalculation = false; // bukan attribute database

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

                $oldRemainingQuota = $activeOldPackage->remaining_quota;

                $model->total_quota     = $package->quota_classes;
                $model->remaining_quota = $model->total_quota + $oldRemainingQuota;
                $model->end_date        = Carbon::parse($model->start_date)->addMonth();

                $model->old_package_to_deactivate  = $activeOldPackage->id;
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

            // ini untuk cancel || create
            if ($package->skip_quota_recalculation_cancel === true || $package->skip_quota_recalculation_create === true) {
                $package->end_date        = Carbon::parse($package->start_date)->addMonth();
                return;
            }

            // // skip_quota_recalculation == true biar ga kena disini
            if ($package->isDirty('start_date')) {
                $package->end_date        = Carbon::parse($package->start_date)->addMonth();
            }

        });


    }
    protected $appends = [
        // ... properti appended lainnya
        'skip_quota_recalculation_cancel', // ✅ Gunakan nama snake_case untuk Mutator/Accessor
        'skip_quota_recalculation_create', // ✅ Gunakan nama snake_case untuk Mutator/Accessor
    ];

    public function setSkipQuotaRecalculationCancelAttribute(bool $value)
    {
        $this->attributes['skip_quota_recalculation_cancel'] = $value;
    }

    public function getSkipQuotaRecalculationCancelAttribute(): bool
    {
        return (bool) ($this->attributes['skip_quota_recalculation_cancel'] ?? false);
    }
    public function setSkipQuotaRecalculationCreateAttribute(bool $value)
    {
        $this->attributes['skip_quota_recalculation_create'] = $value;
    }

    public function getSkipQuotaRecalculationCreateAttribute(): bool
    {
        return (bool) ($this->attributes['skip_quota_recalculation_create'] ?? false);
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

    public static function changeTypePaasdckage(StudentPackage $data)
    {
        return DB::transaction(function () use ($data) {

            // Asumsi: Instance StudentPackage $data memiliki properti student_id
            $studentId = $data->student_id;

            $lastPackage = StudentPackage::where('student_id', $studentId)
                ->where('status', 'inactive')
                ->latest() // Mengambil yang paling baru
                ->first(); // ⭐ HARUS ADA: Mengambil hasilnya

            $package = Package::whereId($data->package_id)->first();
            $result = match (true) {
                $package->quota_classes === null => $data->total_quota,
                $package->quota_classes !== null => $package->quota_classes,
                default                          => 0,
            };

            $data->total_quota      = $result;
            $data->remaining_quota  = $result - $lastPackage->used_quota;
            return $data; // Mengembalikan objek Package yang telah diupdate

        });
    }

    public static function changeTypePackage(StudentPackage $data)
    {
        return DB::transaction(function () use ($data) {


            $package = Package::whereId($data->package_id)->first();
            $result = match (true) {
                $package->quota_classes === null => $data->total_quota,
                $package->quota_classes !== null => $package->quota_classes,
                default                          => 0,
            };

            $data->total_quota      = $result;
            $data->remaining_quota  = $result - $data->used_quota;
            return $data; // Mengembalikan objek Package yang telah diupdate
        });
    }
}
