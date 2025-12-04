<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $fillable = ['student_id','teacher_id', 'subject_id','date','time_start','time_end','status'];

    // protected $casts = [
    //     // Ini memastikan Laravel memperlakukan kolom sebagai tanggal/waktu,
    //     // sehingga konversi timezone otomatis (UTC <-> Asia/Jakarta) terjadi saat diambil.
    //     'date'       => 'date', // Hanya tanggal
    //     'time_start' => 'time', // Hanya waktu
    //     'time_end'   => 'time', // Hanya waktu
    // ];
    // --- Logika Event (Di dalam booted()) ---
    protected static function booted()
    {
        // 1. DEDUCTION: Pengurangan Kuota saat Jadwal BARU dibuat
        static::created(function (ClassSchedule $schedule) {
            self::deductInitialQuota($schedule);
        });

        // 2. REFUND: Pengembalian Kuota saat Jadwal DIUBAH menjadi 'canceled'
        static::updated(function (ClassSchedule $schedule) {
            self::handleCancellationQuotaReturn($schedule);
        });
    }

    private static function calculateQuotaUnits(string $timeStart, string $timeEnd): int
    {
        $durationInMinutes = Carbon::parse($timeStart)->diffInMinutes(Carbon::parse($timeEnd));
        if ($durationInMinutes <= 0) {
            return 0;
        }
        // Pembulatan ke unit terdekat (kelipatan 60 menit)
        return (int) ceil($durationInMinutes / 60);
    }

    /**
     * Melakukan pengurangan kuota awal saat jadwal dibuat.
     */
    protected static function handleCancellationQuotaReturn(ClassSchedule $schedule): void
    {
        // 1. Hitung Quota yang Dibutuhkan
        $quotaUnitsNeeded = self::calculateQuotaUnits($schedule->time_start, $schedule->time_end);

        if ($schedule->isDirty('status') && $schedule->status === 'cancelled') {
            // 1. Hitung kuota yang terpakai sebelum dibatalkan (menggunakan getOriginal)
            $oldUnits = self::calculateQuotaUnits(
                $schedule->getOriginal('time_start'),
                $schedule->getOriginal('time_end')
            );

            // 2. Ambil paket siswa
            $package = StudentPackage::where('student_id', $schedule->student_id)->first();

            // 3. Kembalikan kuota
            $package->remaining_quota += $oldUnits;
            $package->used_quota -= $oldUnits;
            $package->save();
        }
    }
    /**
     * Melakukan pengurangan kuota awal saat jadwal dibuat.
     */
    protected static function deductInitialQuota(ClassSchedule $schedule): void
    {
        // 1. Hitung Quota yang Dibutuhkan
        $quotaUnitsNeeded = self::calculateQuotaUnits($schedule->time_start, $schedule->time_end);

        if ($quotaUnitsNeeded === 0) {
            return;
        }

        // 2. Ambil Paket Siswa Aktif (dengan sisa kuota terbesar)
        // Kita berasumsi validasi (VR) sudah memastikan kuota ini cukup (> 0).
        $package = StudentPackage::where('student_id', $schedule->student_id)
            ->whereNotNull('remaining_quota')
            ->where('remaining_quota', '>=', $quotaUnitsNeeded) // Hanya ambil jika kuota mencukupi
            ->orderByDesc('remaining_quota')
            ->first();
            if (!$package) {
                // Walaupun sudah divalidasi, ini adalah safety check terakhir
                // (Mungkin perlu throw exception jika paket tidak ditemukan atau kuota kurang,
                // karena ini menunjukkan kegagalan pada VR).
                return;
            }

            // 3. Kurangi dan Simpan
        $package->remaining_quota = $package->remaining_quota - $quotaUnitsNeeded;
        $package->used_quota      = $package->used_quota + $quotaUnitsNeeded;
        // dd($quotaUnitsNeeded, ($package->remaining_quota - $quotaUnitsNeeded), $package->used_quota);
        $package->save();
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
        $startTime               = Carbon::parse($this->time_start);
        $endTime                 = Carbon::parse($this->time_end);
        $actualDurationInMinutes = $startTime->diffInMinutes($endTime);

        if ($actualDurationInMinutes <= 0) {
            return 0;
        }

        // Pembulatan ke kelipatan 60 (1 jam)
        $quotaUnits = ceil($actualDurationInMinutes / 60);

        return (int) $quotaUnits * 60;
    }

    /**
     * Custom method untuk handle complete class + save report
     */
    public function completeClass(array $data, $record)
    {
        return DB::transaction(function () use ($data, $record) {
            // dd($record->status,$record->student->activePackage->id, $data['classReport'] +['student_package_id' =>$record->student->activePackage->id ]);

            // 1. Update status parent
            // $this->update([
            //     'status' => 'completed'
            // ]);

            // 2. Update atau Create report (hanya jika completed)
            if (isset($data['classReport'])) {
                $this->classReport()->updateOrCreate(
                    ['class_schedule_id' => $this->id], // Kunci pencarian
                    $data['classReport'] + ['student_package_id' => $record->student->activePackage->id ] // default add
                );
            }
        });
    }
}
