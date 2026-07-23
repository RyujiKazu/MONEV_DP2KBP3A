<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ImportDataController;
use App\Http\Controllers\Admin\DataWilayahController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalisisRisikoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
	$credentials = $request->validate([
		'username' => ['required', 'string'],
		'password' => ['required', 'string'],
	]);

	if (! Auth::attempt($credentials, $request->boolean('remember'))) {
		return back()
			->withErrors([
				'username' => 'Username atau kata sandi tidak sesuai.',
			])
			->onlyInput('username');
	}

	$request->session()->regenerate();

	return redirect()->intended('/dashboard');
})->name('login.submit');

Route::middleware('auth')->group(function () {
	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
	Route::get('/analisis-risiko', [AnalisisRisikoController::class, 'index'])->name('analisis-risiko.index');

	Route::get('/admin/pengguna', [UserController::class, 'index'])->name('admin.users.index');
	Route::get('/admin/pengguna/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
	Route::post('/admin/pengguna', [UserController::class, 'store'])->name('admin.users.store');
	Route::put('/admin/pengguna/{user}', [UserController::class, 'update'])->name('admin.users.update');
	Route::delete('/admin/pengguna/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

	Route::get('/admin/import-data', [ImportDataController::class, 'index'])->name('admin.import-data.index');
	Route::post('/admin/import-data', [ImportDataController::class, 'store'])->name('admin.import-data.store');
	Route::get('/admin/import-data/{record}/edit', [ImportDataController::class, 'edit'])->name('admin.import-data.edit');
	Route::put('/admin/import-data/{record}', [ImportDataController::class, 'update'])->name('admin.import-data.update');
	Route::delete('/admin/import-data/{record}', [ImportDataController::class, 'destroy'])->name('admin.import-data.destroy');
	Route::post('/admin/import-data/import', [ImportDataController::class, 'import'])->name('admin.import-data.import');

	Route::get('/admin/data-wilayah', [DataWilayahController::class, 'index'])->name('admin.data-wilayah.index');
	Route::post('/admin/data-wilayah/kecamatan', [DataWilayahController::class, 'storeKecamatan'])->name('admin.data-wilayah.kecamatan.store');
	Route::put('/admin/data-wilayah/kecamatan/{kecamatan}', [DataWilayahController::class, 'updateKecamatan'])->name('admin.data-wilayah.kecamatan.update');
	Route::delete('/admin/data-wilayah/kecamatan/{kecamatan}', [DataWilayahController::class, 'destroyKecamatan'])->name('admin.data-wilayah.kecamatan.destroy');
	Route::post('/admin/data-wilayah/kelurahan', [DataWilayahController::class, 'storeKelurahan'])->name('admin.data-wilayah.kelurahan.store');
	Route::put('/admin/data-wilayah/kelurahan/{kelurahan}', [DataWilayahController::class, 'updateKelurahan'])->name('admin.data-wilayah.kelurahan.update');
	Route::delete('/admin/data-wilayah/kelurahan/{kelurahan}', [DataWilayahController::class, 'destroyKelurahan'])->name('admin.data-wilayah.kelurahan.destroy');

	Route::post('/logout', function (Request $request) {
		Auth::logout();

		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect()->route('login');
	})->name('logout');
});
