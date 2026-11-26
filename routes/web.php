<?php

use App\Http\Controllers\BiayaController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\JenisPengeluaranController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PerawatanController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekaptipeController;
use App\Http\Controllers\TipeController;
use App\Http\Controllers\TujuanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengeluaranfinController;
use App\Http\Controllers\RekapfinController;
use App\Http\Controllers\TesController;
use App\Http\Controllers\EksternalController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::middleware('auth')->group(function () {
    Route::redirect('/', '/pengiriman');

    Route::get('/driver/data', [DriverController::class, 'data'])->name('driver.data');
    Route::resource('/driver', DriverController::class);

    Route::get('/tujuan/data', [TujuanController::class, 'data'])->name('tujuan.data');
    Route::resource('/tujuan', TujuanController::class);

    Route::get('/tipe/data', [TipeController::class, 'data'])->name('tipe.data');
    Route::resource('/tipe', TipeController::class);

    Route::get('/jenis/data', [JenisPengeluaranController::class, 'data'])->name('jenis.data');
    Route::resource('/jenis', JenisPengeluaranController::class);

    Route::get('/pengiriman/data', [PengirimanController::class, 'data'])->name('pengiriman.data');
    Route::resource('/pengiriman', PengirimanController::class);

    Route::get(
        '/pengiriman/export/bulanan',
        [PengirimanController::class, 'exportBulanan']
    )->name('pengiriman.export.bulanan');

    Route::get('/pengeluaran/data/{pengiriman_id}', [PengeluaranController::class, 'data'])->name('pengeluaran.data');
    Route::get('/pengeluaran/list/{pengiriman_id}', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
    Route::get('/pengeluaran/{pengiriman_id}', [PengeluaranController::class, 'create'])->name('pengeluaran.create');
    Route::post('/pengeluaran/{pengiriman_id}', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
    Route::get('/pengeluaran/detail/{id}', [PengeluaranController::class, 'show'])->name('pengeluaran.show');
    Route::put('/pengeluaran/{id}', [PengeluaranController::class, 'update'])->name('pengeluaran.update');
    Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');

    Route::get('/pengiriman/{id}/export', [PengirimanController::class, 'export'])->name('pengiriman.export');
    Route::get('rekap/export', [RekapController::class, 'export'])->name('rekap.export');

    Route::get('/perawatan/data', [PerawatanController::class, 'data'])->name('perawatan.data');
    Route::resource('/perawatan', PerawatanController::class);
    
    Route::get('/eksternal', [EksternalController::class, 'index'])->name('eksternal.index');
    Route::get('/eksternal/data', [EksternalController::class, 'data'])->name('eksternal.data');
    Route::get('/eksternal/export/bulanan', [EksternalController::class, 'exportBulanan'])->name('eksternal.export.bulanan');

    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/data', [RekapController::class, 'data'])->name('rekap.data');
    Route::get('/rekap/chart-data', [RekapController::class, 'chartData'])->name('rekap.chart-data');
    Route::get('/rekap/detail-jenis', [RekapController::class, 'detailJenis'])->name('rekap.detailJenis');

    Route::get('/rkptipe', [RekaptipeController::class, 'index'])->name('rekap.index');
    Route::get('/rkptipe/tipebulan', [RekaptipeController::class, 'tipebulan'])->name('rekap.tipebulan');
    Route::get('rekap/tipe/export', [RekaptipeController::class, 'export'])->name('rekap.tipe.export');

    Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
    Route::resource('/user', UserController::class);

    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::resource('/dashboard', DashboardController::class);

    Route::get('/cost/data', [CostController::class, 'data'])->name('cost.data');
    Route::resource('/cost', CostController::class);

    Route::get('/biaya/data', [BiayaController::class, 'data'])->name('biaya.data');
    Route::resource('/biaya', BiayaController::class);

    Route::get('/pengeluaranfin/data/{pengiriman_id}', [PengeluaranfinController::class, 'data'])->name('pengeluaranfin.data');
    Route::get('/pengeluaranfin/list/{pengiriman_id}', [PengeluaranfinController::class, 'index'])->name('pengeluaranfin.index');
    Route::get('/pengeluaranfin/{pengiriman_id}', [PengeluaranfinController::class, 'create'])->name('pengeluaranfin.create');
    Route::post('/pengeluaranfin/{pengiriman_id}', [PengeluaranfinController::class, 'store'])->name('pengeluaranfin.store');
    Route::get('/pengeluaranfin/detail/{id}', [PengeluaranfinController::class, 'show'])->name('pengeluaranfin.show');
    Route::put('/pengeluaranfin/{id}', [PengeluaranfinController::class, 'update'])->name('pengeluaranfin.update');
    Route::delete('/pengeluaranfin/{id}', [PengeluaranfinController::class, 'destroy'])->name('pengeluaranfin.destroy');

    Route::get('/lr', [RekapfinController::class, 'index'])->name('rekap.tujuan.index');
    Route::get('/lr/data', [RekapfinController::class, 'data'])->name('rekap.tujuan.data');
    Route::get('/lr/dataJenis', [RekapfinController::class, 'dataJenis'])->name('rekap.dataJenis');
    Route::get('/lr/data-laba', [RekapfinController::class, 'dataLaba'])
        ->name('rekap.dataLaba');
});

Route::resource('/tes', TesController::class);

require __DIR__ . '/auth.php';
