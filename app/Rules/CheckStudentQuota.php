<?php

namespace App\Rules;

use App\Models\ClassSchedule;
use App\Models\StudentPackage;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckStudentQuota implements ValidationRule
{
    protected $studentId;
    protected $timeStart;
    protected $scheduleId;

    public function __construct($studentId, $timeStart, $scheduleId)
    {
        $this->studentId  = $studentId;
        $this->timeStart  = $timeStart;
        $this->scheduleId = $scheduleId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $newTimeStart   = $this->timeStart;
        $newTimeEnd     = $value;
        $studentId      = $this->studentId;

        // 1. Hitung Durasi Kelas Baru (dalam menit)
        $actualDurationInMinutes = Carbon::parse($newTimeStart)->diffInMinutes(Carbon::parse($newTimeEnd));

        // 2. HITUNG KUOTA YANG DIBUTUHKAN (dalam UNIT, pembulatan ke kelipatan 60 menit)
        if ($actualDurationInMinutes <= 0) {
            return;
        }
        // Menghitung Quota Unit yang dibutuhkan: (Durasi/60) dibulatkan ke atas
        $quotaUnitsNeeded = (int) ceil($actualDurationInMinutes / 60);

        // --- LOGIKA ADJUSTMENT/PELEPASAN KUOTA JADWAL LAMA ---
        $quotaUnitsToIgnore = 0;

        // Pengecekan apakah kita sedang dalam mode 'update' atau 'edit'
        if ($this->scheduleId) {
            // PENTING: Ambil data jadwal lama berdasarkan ID yang dikirim
            $existingSchedule = ClassSchedule::find($this->scheduleId);

            if ($existingSchedule) {
                // Hitung durasi jadwal lama (sebelum diedit)
                $oldDuration = Carbon::parse($existingSchedule->time_start)
                ->diffInMinutes(Carbon::parse($existingSchedule->time_end));

                // Hitung Kuota Unit lama yang sudah dibooking oleh ID jadwal tersebut
                $quotaUnitsToIgnore = (int) ceil($oldDuration / 60);

                // Logika: Jika kuota ini (misal: 2 unit) sudah terpakai dan kita sedang mengeditnya,
                // maka kita harus "mengembalikannya" ke sisa kuota untuk validasi.
            }
        }
        // --- AKHIR LOGIKA ADJUSTMENT ---

        // 3. Ambil Kuota Siswa (Paket Aktif dengan Kuota Sisa Terbesar)
        $package = StudentPackage::where('student_id', $studentId)
            ->whereNotNull('remaining_quota')
            ->where('remaining_quota', '>', 0)
            ->orderByDesc('remaining_quota')
            ->first();

        if (!$package) {
            $fail('The student does not have an active package with remaining quota.');
            return;
        }

        $remainingUnits = $package->remaining_quota; // Misal: 8 Unit (Sudah dikurangi 2 jadwal)

        // 4. Hitung Kuota Efektif (Pelepasan Kuota Jadwal yang Diedit)
        // Kuota Sisa (8) + Kuota Lama yang Dilepas (misal: 2) = Kuota Efektif (10)
        $effectiveRemainingUnits = $remainingUnits + $quotaUnitsToIgnore;
        // dd($remainingUnits, $quotaUnitsNeeded, $effectiveRemainingUnits, $quotaUnitsToIgnore);

        // 5. Bandingkan (Kuotanya harus mencukupi total 10 unit)
        if ($quotaUnitsNeeded > $remainingUnits) {
            $fail("Failed! The class duration requires {$quotaUnitsNeeded} unit(s). Effective remaining quota is only {$effectiveRemainingUnits} unit(s) (Original: {$remainingUnits} units).");
        }

        // $newTimeStart   = $this->timeStart;
        // $newTimeEnd     = $value;
        // $studentId      = $this->studentId;

        // // 1. Hitung Durasi Kelas Baru (dalam menit)
        // $actualDurationInMinutes = Carbon::parse($newTimeStart)->diffInMinutes(Carbon::parse($newTimeEnd));

        // // 2. HITUNG KUOTA YANG DIGUNAKAN (Pembulatan ke kelipatan 60 menit)
        // if ($actualDurationInMinutes <= 0) {
        //     return;
        // }
        // $quotaMinutesUsed = (int) ceil($actualDurationInMinutes / 60) * 60;

        // // 3. Ambil Kuota Siswa (ambil unit kuota dari DB)
        // $package = StudentPackage::where('student_id', $studentId)
        // ->whereNotNull('remaining_quota')
        // ->where('remaining_quota', '>', 0)
        // ->orderByDesc('remaining_quota')
        // ->first();

        // if (!$package) {
        //     $fail('The student does not have an active package with remaining quota.');
        //     return;
        // }

        // $remainingUnits = $package->remaining_quota;

        // // **KONVERSI:** Unit kuota (misal: 3) dikalikan 60 menit
        // $remainingQuotaInMinutes = $remainingUnits * 60;
        // dd($quotaMinutesUsed > $remainingQuotaInMinutes, $quotaMinutesUsed, $remainingQuotaInMinutes);

        // // 4. Bandingkan (Menggunakan $remainingQuotaInMinutes)
        // if ($quotaMinutesUsed > $remainingQuotaInMinutes) {
        //     $fail("Failed! The class duration ({$actualDurationInMinutes} minutes) will consume {$quotaMinutesUsed} minutes (rounded). Remaining quota is only {$remainingQuotaInMinutes} minutes (from {$remainingUnits} quota unit(s)).");
        // }
    }
}
