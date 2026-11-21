<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CatchDataController;
use App\Http\Controllers\OceanographicParameterController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\FishingGroundController;
use App\Http\Controllers\CoralReefController;
use App\Http\Controllers\FishingGearController;
use App\Http\Controllers\AdminManagementController;

// ================================================================
// RUTE AUTENTIKASI (terbuka untuk publik)
// ================================================================

// Halaman Login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

// Proses Login
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.store');

// Halaman Register
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

// Proses Register
Route::post('/register', [AuthController::class, 'register'])
    ->name('register.store');

// Proses Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// HALAMAN AWAL (LANDING PAGE RESMI SCOMAR)
Route::get('/', function () {
    return view('landing.index');
})->name('landing');

// ================================================================
// RUTE TERPROTEKSI (harus login)
// Semua rute di dalam grup ini memerlukan autentikasi.
// ================================================================
Route::middleware(['auth'])->group(function () {

    // â”€â”€ Dashboard â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // â”€â”€ Profil Pengguna â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('/profile', function () {
        return redirect()->route('dashboard');
    })->name('profile');

    // ── Grafik Hasil Tangkapan (View & Ekspor CSV) ──────────────────────
    Route::get('/charts/catches', [ChartController::class, 'catches'])
        ->name('charts.catches');
    Route::get('/charts/catches/export-csv', [ChartController::class, 'exportCatchesCsv'])
        ->name('charts.catches.export-csv');

    // ── Grafik Parameter Oseanografi (View & Ekspor CSV) ────────────────
    Route::get('/charts/oceanographic', [ChartController::class, 'oceanographic'])
        ->name('charts.oceanographic');
    Route::get('/charts/oceanographic/export-csv', [ChartController::class, 'exportOceanographicCsv'])
        ->name('charts.oceanographic.export-csv');

    // ── Analisis Hubungan Oseanografi dan CPUE (View, Data & Ekspor CSV) ─
    Route::get('/charts/analysis', [App\Http\Controllers\AnalysisController::class, 'index'])
        ->name('charts.analysis');
    Route::get('/charts/analysis/data', [App\Http\Controllers\AnalysisController::class, 'getData'])
        ->name('charts.analysis.data');
    Route::get('/charts/analysis/export-csv', [App\Http\Controllers\AnalysisController::class, 'exportAnalysisCsv'])
        ->name('charts.analysis.export-csv');

    // â”€â”€ Pemindai Kesegaran Ikan â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('/scanner', [ScannerController::class, 'index'])
        ->name('scanner.index');
    Route::post('/scanner/predict', [ScannerController::class, 'predict'])
        ->name('scanner.predict');

    // â”€â”€ Tentang Kami â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('/about', function () {
        return view('about.index');
    })->name('about');

    // ============================================================
    // RUTE OPERASI CRUD DATA (Khusus Admin & Master Admin)
    // Pengguna biasa (role: user) akan ditolak dengan HTTP 403.
    // ============================================================
    Route::middleware(['admin'])->group(function () {

        // CRUD Hasil Tangkapan
        Route::post('/catches', [CatchDataController::class, 'store'])
            ->name('catches.store');
        Route::put('/catches/{catch}', [CatchDataController::class, 'update'])
            ->name('catches.update');
        Route::delete('/catches/{catch}', [CatchDataController::class, 'destroy'])
            ->name('catches.destroy');

        // CRUD Parameter Oseanografi
        Route::post('/oceanographic', [OceanographicParameterController::class, 'store'])
            ->name('oceanographic.store');
        Route::put('/oceanographic/{parameter}', [OceanographicParameterController::class, 'update'])
            ->name('oceanographic.update');
        Route::delete('/oceanographic/{parameter}', [OceanographicParameterController::class, 'destroy'])
            ->name('oceanographic.destroy');

        // CRUD Fishing Ground (Titik Tangkap)
        Route::post('/fishing-grounds', [FishingGroundController::class, 'store'])
            ->name('fishing-grounds.store');
        Route::put('/fishing-grounds/{fishingGround}', [FishingGroundController::class, 'update'])
            ->name('fishing-grounds.update');
        Route::delete('/fishing-grounds/{fishingGround}', [FishingGroundController::class, 'destroy'])
            ->name('fishing-grounds.destroy');

        // CRUD Terumbu Karang (Zona Konservasi/Ekosistem)
        Route::post('/coral-reefs', [CoralReefController::class, 'store'])
            ->name('coral-reefs.store');
        Route::put('/coral-reefs/{coralReef}', [CoralReefController::class, 'update'])
            ->name('coral-reefs.update');
        Route::delete('/coral-reefs/{coralReef}', [CoralReefController::class, 'destroy'])
            ->name('coral-reefs.destroy');

        // CRUD Alat Tangkap (Gear Rekomendasi/Selektif)
        Route::post('/fishing-gears', [FishingGearController::class, 'store'])
            ->name('fishing-gears.store');
        Route::put('/fishing-gears/{fishingGear}', [FishingGearController::class, 'update'])
            ->name('fishing-gears.update');
        Route::delete('/fishing-gears/{fishingGear}', [FishingGearController::class, 'destroy'])
            ->name('fishing-gears.destroy');
    });

    // ============================================================
    // RUTE ADMINISTRASI AKUN (Khusus Master Admin)
    // Admin biasa dan pengguna biasa tidak memiliki akses (HTTP 403).
    // ============================================================
    Route::middleware(['master_admin'])->prefix('master-admin')->name('master-admin.')->group(function () {
        Route::get('/accounts', [AdminManagementController::class, 'index'])
            ->name('accounts.index');
        Route::post('/accounts', [AdminManagementController::class, 'store'])
            ->name('accounts.store');
        Route::put('/accounts/{account}', [AdminManagementController::class, 'update'])
            ->name('accounts.update');
        Route::delete('/accounts/{account}', [AdminManagementController::class, 'destroy'])
            ->name('accounts.destroy');
    });

});




