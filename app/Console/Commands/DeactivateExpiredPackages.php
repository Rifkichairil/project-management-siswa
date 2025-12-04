<?php

namespace App\Console\Commands;

use App\Models\StudentPackage;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeactivateExpiredPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deactivate-expired-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menonaktifkan paket siswa yang tanggal kadaluarsanya sudah lewat (akhir bulan).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Ambil tanggal hari ini (pukul 00:00:00)
        $today = Carbon::today();

        // 2. Temukan paket yang:
        //    a. end_date kurang dari hari ini (sudah lewat)
        //    b. statusnya masih 'active'
        $expiredPackages = StudentPackage::where('end_date', '<', $today)
            ->where('status', 'active')
            ->get();

        $count = $expiredPackages->count();

        $this->warn("⚠️ Ditemukan {$today} ");

        // 🌟 INFORMASI AWAL (PRE-EXECUTION CHECK)
        if ($count > 0) {
            // Tampilkan peringatan atau informasi sebelum proses update
            $this->warn("⚠️ Ditemukan {$count} paket yang kadaluarsa dan akan dinonaktifkan...");

            // 3. Update status menjadi 'inactive'
            // Menggunakan update() pada Query Builder lebih efisien daripada looping get()
            StudentPackage::whereIn('id', $expiredPackages->pluck('id'))
                ->update(['status' => 'inactive']);

            $this->info("✅ Berhasil menonaktifkan {$count} paket.");

        } else {
            $this->info('🤷‍♂️ Tidak ada paket aktif yang kadaluarsa ditemukan.');
        }

        return 0;
    }
}
