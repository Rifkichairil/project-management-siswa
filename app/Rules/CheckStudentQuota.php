<?php

namespace App\Rules;

use App\Models\StudentPackage;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckStudentQuota implements ValidationRule
{
    protected $studentId;
    protected $timeStart;

    public function __construct($studentId, $timeStart)
    {
        $this->studentId = $studentId;
        $this->timeStart = $timeStart;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $newTimeStart = $this->timeStart;
        $newTimeEnd = $value;
        $studentId = $this->studentId;

        // 1. Hitung Durasi Kelas Baru (dalam menit)
        $actualDurationInMinutes = Carbon::parse($newTimeStart)->diffInMinutes(Carbon::parse($newTimeEnd));

        // 2. HITUNG KUOTA YANG DIGUNAKAN (Pembulatan ke kelipatan 60 menit)
        if ($actualDurationInMinutes <= 0) {
            return;
        }
        $quotaMinutesUsed = (int) ceil($actualDurationInMinutes / 60) * 60;

        // 3. Ambil Kuota Siswa (ambil unit kuota dari DB)
        $package = StudentPackage::where('student_id', $studentId)
                                 ->whereNotNull('remaining_quota')
                                 ->where('remaining_quota', '>', 0)
                                 ->orderByDesc('remaining_quota')
                                 ->first();

        if (!$package) {
            $fail('The student does not have an active package with remaining quota.');
            return;
        }

        $remainingUnits = $package->remaining_quota;

        // **KONVERSI:** Unit kuota (misal: 3) dikalikan 60 menit
        $remainingQuotaInMinutes = $remainingUnits * 60;

        // 4. Bandingkan (Menggunakan $remainingQuotaInMinutes)
        if ($quotaMinutesUsed > $remainingQuotaInMinutes) {
            $fail("Failed! The class duration ({$actualDurationInMinutes} minutes) will consume {$quotaMinutesUsed} minutes (rounded). Remaining quota is only {$remainingQuotaInMinutes} minutes (from {$remainingUnits} quota unit(s)).");
        }
    }
}
