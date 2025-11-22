<?php

namespace App\Observers;

use App\Models\ClassSchedule;
use App\Models\StudentPackage;
use Carbon\Carbon;

class ClassScheduleObserver
{
    protected function calculateQuotaUsed(ClassSchedule $schedule): int
    {
        $startTime = Carbon::parse($schedule->time_start);
        $endTime = Carbon::parse($schedule->time_end);
        $actualDurationInMinutes = $startTime->diffInMinutes($endTime);

        if ($actualDurationInMinutes <= 0) {
            return 0;
        }

        $quotaUnits = ceil($actualDurationInMinutes / 60);
        return (int) $quotaUnits * 60;
    }

    public function saving(ClassSchedule $schedule): bool
    {
        // Pengecekan Overlapping Jadwal (Opsional di sini, lebih mudah di Rule/Resource)
        // Kita fokus pada Pengecekan Kuota

        $quotaMinutesUsed = $this->calculateQuotaUsed($schedule);
        $studentId = $schedule->student_id;

        // Ambil Kuota Siswa (dalam unit/jam)
        $package = StudentPackage::where('student_id', $studentId)
                                 ->whereNotNull('remaining_quota')
                                 ->where('remaining_quota', '>', 0)
                                 ->orderByDesc('remaining_quota')
                                 ->first();

        if (!$package) {
            // Jika validasi gagal, kembalikan false
            session()->flash('error', 'Siswa tidak memiliki paket aktif dengan kuota tersisa.');
            return false;
        }

        $remainingUnits = $package->remaining_quota;
        $remainingQuotaInMinutes = $remainingUnits * 60;

        if ($quotaMinutesUsed > $remainingQuotaInMinutes) {
            session()->flash('error', "Gagal! Kelas ini butuh kuota {$quotaMinutesUsed} menit (dibulatkan). Kuota tersisa hanya {$remainingQuotaInMinutes} menit.");
            return false;
        }

        // Jika lolos, lanjutkan proses saving
        return true;
    }

    /**
     * Handle the ClassSchedule "updated" event (dipanggil setelah update).
     * Digunakan untuk pengurangan kuota saat status == 'completed'.
     */
    public function updated(ClassSchedule $schedule): void
    {
        // Hanya potong kuota jika status BARU adalah 'completed' DAN status LAMA bukan 'completed'
        if ($schedule->status === 'completed' && $schedule->getOriginal('status') !== 'completed') {

            $quotaMinutesUsed = $this->calculateQuotaUsed($schedule);
            $quotaUnitsToDecrement = $quotaMinutesUsed / 60;

            // Cari paket siswa
            $package = StudentPackage::where('student_id', $schedule->student_id)
                                     ->whereNotNull('remaining_quota')
                                     ->where('remaining_quota', '>', 0)
                                     ->orderByDesc('remaining_quota')
                                     ->first();

            // Kurangi remaining_quota di DB
            if ($package && $quotaUnitsToDecrement > 0) {
                $package->decrement('remaining_quota', $quotaUnitsToDecrement);
            }
        }
    }

    /**
     * Handle the ClassSchedule "deleted" event.
     */
    public function deleted(ClassSchedule $classSchedule): void
    {
        //
    }

    /**
     * Handle the ClassSchedule "restored" event.
     */
    public function restored(ClassSchedule $classSchedule): void
    {
        //
    }

    /**
     * Handle the ClassSchedule "force deleted" event.
     */
    public function forceDeleted(ClassSchedule $classSchedule): void
    {
        //
    }
}
