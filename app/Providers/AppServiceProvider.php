<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule; // Import class Schedule
use App\Console\Commands\DeactivateExpiredPackages; // Import Command Anda
use App\Console\Commands\LogEveryMinute;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tetap kosong jika tidak ada yang didaftarkan.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pastikan kode scheduler hanya berjalan saat aplikasi berjalan di console
        // (yaitu ketika perintah 'php artisan schedule:run' dieksekusi).
        if ($this->app->runningInConsole()) {

            // Menggunakan booted() untuk mendaftarkan scheduler setelah semua service di-boot.
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);

                // === PENJADWALAN UNTUK AKHIR BULAN ===
                $schedule->command(DeactivateExpiredPackages::class)
                        ->daily()
                        ->timezone('Asia/Jakarta');
                $schedule->command(LogEveryMinute::class)->everyMinute();
            });
        }
    }
}
