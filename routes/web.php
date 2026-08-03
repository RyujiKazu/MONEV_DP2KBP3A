<?php

use App\Http\Controllers\Admin\DataWilayahController;
use App\Http\Controllers\Admin\RekapKrsController;
use App\Http\Controllers\Admin\TargetIndikatorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanEvaluasiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.submit');
});

Route::middleware(['auth', 'role:Admin,PKK'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');

    Route::prefix('laporan-evaluasi')->name('laporan.')->group(function (): void {
        Route::get('/', [LaporanEvaluasiController::class, 'index'])->name('index');
        Route::get('/cetak', [LaporanEvaluasiController::class, 'print'])->name('print');
        Route::get('/csv', [LaporanEvaluasiController::class, 'csv'])->name('csv');
        Route::get('/pdf', [LaporanEvaluasiController::class, 'pdf'])->name('pdf');
    });
});

Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::get('/pengguna/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/data-wilayah', [DataWilayahController::class, 'index'])->name('data-wilayah.index');
        Route::post('/data-wilayah/kecamatan', [DataWilayahController::class, 'storeKecamatan'])->name('data-wilayah.kecamatan.store');
        Route::put('/data-wilayah/kecamatan/{kecamatan}', [DataWilayahController::class, 'updateKecamatan'])->name('data-wilayah.kecamatan.update');
        Route::delete('/data-wilayah/kecamatan/{kecamatan}', [DataWilayahController::class, 'destroyKecamatan'])->name('data-wilayah.kecamatan.destroy');
        Route::post('/data-wilayah/kelurahan', [DataWilayahController::class, 'storeKelurahan'])->name('data-wilayah.kelurahan.store');
        Route::put('/data-wilayah/kelurahan/{kelurahan}', [DataWilayahController::class, 'updateKelurahan'])->name('data-wilayah.kelurahan.update');
        Route::delete('/data-wilayah/kelurahan/{kelurahan}', [DataWilayahController::class, 'destroyKelurahan'])->name('data-wilayah.kelurahan.destroy');

        Route::resource('/data-krs', RekapKrsController::class)
            ->parameters(['data-krs' => 'rekapKrs'])
            ->names('rekap-krs');

        Route::resource('/target-indikator', TargetIndikatorController::class)
            ->parameters(['target-indikator' => 'targetIndikator'])
            ->names('target-indikator');
    });

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
