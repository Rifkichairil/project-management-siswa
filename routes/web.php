<?php

use App\Models\StudentPackage;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::redirect('/', '/admin/login');

Route::get('/test-expired-packages', function () {
    // 1. Definisikan nilai $today
    $today = \Carbon\Carbon::today(); // Contoh: 2025-12-04 00:00:00

    // 2. Query untuk mendapatkan paket yang kadaluarsa
    $expiredPackages = StudentPackage::where('end_date', '<', $today)
        ->where('status', 'active')
        ->get();

    $count = $expiredPackages->count();

    // 3. Tampilkan hasil
    if ($count > 0) {
        // Tampilkan jumlah dan daftar paket yang ditemukan dalam format JSON
        return response()->json([
            'message' => "Ditemukan {$count} paket yang akan dinonaktifkan.",
            'today' => $today->toDateString(),
            'expired_packages' => $expiredPackages,
        ]);
    }

    return response()->json([
        'message' => 'Tidak ditemukan paket yang kadaluarsa.',
        'today' => $today->toDateString(),
    ]);

});

