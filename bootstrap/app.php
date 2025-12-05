<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('app:log-every-minute')
                ->runInBackground()
                ->everyMinute();
        $schedule->command('app:deactivate-expired-packages')
                ->runInBackground()
                ->daily()
                ->timezone('Asia/Jakarta');                        // Berjalan pada hari pertama bulan (tanggal 1), pukul 00:00 (tengah malam).
    })
    ->create();
