<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\getProfileController;
use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;


Route::get('/proxy-kemenag', [ProxyController::class, 'searchPegawai']);

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
    Route::get('/wilayah/create', [WilayahController::class, 'create'])->name('wilayah.create');
    Route::post('/wilayah', [WilayahController::class, 'store'])->name('wilayah.store');
    Route::get('/wilayah/{id}/edit', [WilayahController::class, 'edit'])->name('wilayah.edit');
    Route::put('/wilayah/{id}', [WilayahController::class, 'update'])->name('wilayah.update');
    Route::delete('/wilayah/{id}', [WilayahController::class, 'destroy'])->name('wilayah.destroy');

    Route::get('/sakter', [SatkerController::class, 'index'])->name('satker.index');
    Route::get('/sakter/laporan', [SatkerController::class, 'index2'])->name('satker.laporan');
    Route::post('/store', [SatkerController::class, 'store'])->name('satker.store');
    Route::put('/satker/{id}', [SatkerController::class, 'update'])->name('satker.update');
    Route::delete('/satker/{id}', [SatkerController::class, 'destroy'])->name('satker.destroy');
    Route::get('/satker/users/{id}', [SatkerController::class, 'getUsersBySatker'])->name('satker.users');
    Route::get('/satker/generate-code', [SatkerController::class, 'generateCode'])->name('satker.generate-code');

    Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
    Route::post('/jabatan/store', [JabatanController::class, 'store'])->name('jabatan.store');
    Route::put('/jabatan/{id}', [JabatanController::class, 'update'])->name('jabatan.update');
    Route::delete('/jabatan/{id}', [JabatanController::class, 'destroy'])->name('jabatan.destroy');
    Route::get('/jabatan/matriks', [App\Http\Controllers\JabatanController::class, 'getMatriks'])->name('admin.jabatan.matriks');
    Route::post('/jabatan/matriks/save', [App\Http\Controllers\JabatanController::class, 'saveMatriks'])->name('admin.jabatan.matriks.save');
    Route::post('/jabatan/matriks/save-baseline', [App\Http\Controllers\JabatanController::class, 'saveBaselineJenjang'])->name('admin.jabatan.matriks.save-baseline');

    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');

    Route::get('/penugasan', [PenugasanController::class, 'index'])->name('penugasan.index');
    Route::post('/penugasan', [PenugasanController::class, 'store'])->name('penugasan.store');
    Route::put('/penugasan/{id}', [PenugasanController::class, 'update'])->name('penugasan.update');
    Route::delete('/penugasan/{id}', [PenugasanController::class, 'destroy'])->name('penugasan.destroy');
    Route::put('/penugasan/unassign/{id}', [PenugasanController::class, 'unassign'])->name('penugasan.unassign');

    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

    Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
    Route::post('/periode/periode/store', [PeriodeController::class, 'store'])->name('periode.store');
    Route::put('/periode/{id}/update', [PeriodeController::class, 'update'])->name('periode.update');
    Route::delete('/periode/{id}/delete', [PeriodeController::class, 'destroy'])->name('periode.destroy');

    Route::get('/pegawai/search', [getProfileController::class, 'searchByNIP'])->name('pegawai.search');
    Route::post('/pegawai/update-satker', 
    [PegawaiController::class, 'updateSatker'])
    ->name('pegawai.update-satker');
    Route::get('/bulking/list', [PegawaiController::class, 'bulkingList'])
    ->name('bulking.list');

    Route::get('/setting-kode', [\App\Http\Controllers\SettingKodeController::class, 'index'])->name('setting-kode.index');
    Route::post('/setting-kode/rumus', [\App\Http\Controllers\SettingKodeController::class, 'storeRumus'])->name('setting-kode.storeRumus');
    Route::put('/setting-kode/manual/{id}', [\App\Http\Controllers\SettingKodeController::class, 'updateManual'])->name('setting-kode.updateManual');
    Route::post('/setting-kode/update-manual-bulk', [\App\Http\Controllers\SettingKodeController::class, 'updateManualBulk'])->name('setting-kode.updateManualBulk');
    Route::post('/setting-kode/sync', [\App\Http\Controllers\SettingKodeController::class, 'syncAllCodes'])->name('setting-kode.sync');

    Route::put('/setting-kode/rumus/{id}', [\App\Http\Controllers\SettingKodeController::class, 'updateRumus'])->name('setting-kode.updateRumus');
    Route::delete('/setting-kode/rumus/{id}', [\App\Http\Controllers\SettingKodeController::class, 'destroyRumus'])->name('setting-kode.destroyRumus');
    Route::post('/setting-kode/rumus/{id}/apply', [\App\Http\Controllers\SettingKodeController::class, 'applyRumus'])->name('setting-kode.applyRumus');

    Route::post('/setting-kode/jabatan', [\App\Http\Controllers\SettingKodeController::class, 'storeJabatan'])->name('setting-kode.storeJabatan');
    Route::put('/setting-kode/jabatan/{id}', [\App\Http\Controllers\SettingKodeController::class, 'updateJabatan'])->name('setting-kode.updateJabatan');
    Route::delete('/setting-kode/jabatan/{id}', [\App\Http\Controllers\SettingKodeController::class, 'destroyJabatan'])->name('setting-kode.destroyJabatan');

    Route::get('/pegawai/search-local', [App\Http\Controllers\PegawaiController::class, 'searchLocal'])->name('pegawai.search-local');

    Route::resource('role', \App\Http\Controllers\RoleController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('role/penugasan', [\App\Http\Controllers\RoleController::class, 'storePenugasan'])->name('role.penugasan.store');
    Route::delete('role/penugasan/{id}', [\App\Http\Controllers\RoleController::class, 'destroyPenugasan'])->name('role.penugasan.destroy');
    
    Route::resource('regulasi', \App\Http\Controllers\RegulasiController::class)->only(['index', 'update']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
