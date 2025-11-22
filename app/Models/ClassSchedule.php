<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $fillable = ['student_id','teacher_id', 'subject_id','date','time_start','time_end','status'];

    // --- Logika Event (Di dalam booted()) ---
    protected static function booted()
    {
        // Event UPDATED: Dijalankan setelah data berhasil disimpan dan ada perubahan.
        static::updated(function (ClassSchedule $schedule) {

            // Logika: Potong kuota hanya jika status BARU adalah 'completed'
            // DAN status LAMA BUKAN 'completed' (Mencegah pemotongan ganda)
            if ($schedule->status === 'completed' && $schedule->getOriginal('status') !== 'completed') {

                // 1. Hitung unit kuota yang akan dipotong
                $quotaMinutesUsed = $schedule->getQuotaMinutesUsed();
                $quotaUnitsToDecrement = $quotaMinutesUsed / 60; // Konversi ke unit/jam (misal: 120 menit -> 2 unit)

                // 2. Cari paket siswa (asumsi ambil kuota terbesar/terbaru)
                $package = StudentPackage::where('student_id', $schedule->student_id)
                                         ->whereNotNull('remaining_quota')
                                         ->where('remaining_quota', '>', 0)
                                         ->orderByDesc('remaining_quota')
                                         ->first();

                // 3. Aksi pada Kuota
                if ($package && $quotaUnitsToDecrement > 0) {

                    // A. Mengurangi remaining_quota (wajib)
                    $package->decrement('remaining_quota', $quotaUnitsToDecrement);

                    // B. MENAMBAHKAN used_quota (Permintaan baru)
                    // Kita asumsikan used_quota juga disimpan dalam satuan Unit/Jam
                    $package->increment('used_quota', $quotaUnitsToDecrement);
                }
            }
        });
    }

    public function student()
    {
    return $this->belongsTo(Student::class);
    }

    public function subject()
    {
    return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
    return $this->belongsTo(Teacher::class);
    }

    public function classReport()
    {
    return $this->hasOne(ClassReport::class);
    }

    public function getQuotaMinutesUsed(): int
    {
        // Hitung durasi aktual dalam menit
        $startTime = Carbon::parse($this->time_start);
        $endTime = Carbon::parse($this->time_end);
        $actualDurationInMinutes = $startTime->diffInMinutes($endTime);

        if ($actualDurationInMinutes <= 0) {
            return 0;
        }

        // Pembulatan ke kelipatan 60 (1 jam)
        $quotaUnits = ceil($actualDurationInMinutes / 60);

        return (int) $quotaUnits * 60;
    }
}
