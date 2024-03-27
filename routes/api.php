<?php

use App\Http\Controllers\kredit\CreditController;
use App\Http\Controllers\pengguna\UserController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\resilist\WaybillController;
use App\Http\Controllers\resilist\WaybillFilterController;
use App\Http\Controllers\transaksi\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//AUTH
Route::post('auth/registerFromAdmin', [\App\Http\Controllers\AuthController::class, 'RegisterFromAdmin']);
Route::post('auth/login', [\App\Http\Controllers\AuthController::class, 'login']);

Route::prefix('superadmin/waybill')->group(function () {
    // Routes for filtering waybills
    Route::get('delivered', [WaybillFilterController::class, 'indexDelivered']);
    Route::get('on-progress', [WaybillFilterController::class, 'indexOnProgress']);
    Route::get('return-reject', [WaybillFilterController::class, 'indexReturnReject']);

    // Route for statistics
    Route::get('statistic', [StatisticController::class, 'indexHalamanResiList']);

    // Routes for general waybill operations
    Route::get('/', [WaybillController::class, 'index']);
    Route::post('store', [WaybillController::class, 'store']);
    Route::get('show/{id}', [WaybillController::class, 'show']);
    Route::put('update/{id}', [WaybillController::class, 'update']);
    Route::get('newmanifest/{id}', [WaybillController::class, 'edit']);
    Route::post('updatemanifest/{id}', [WaybillController::class, 'updateManifest']);
});

Route::prefix('superadmin/pengguna')->group(function() {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/store', [UserController::class, 'store']);
});

Route::prefix('superadmin/kredit')->group(function () {
    Route::get('/statistik', [StatisticController::class, 'indexHalamanKredit']);
    Route::get('/', [CreditController::class, 'index']);
    Route::get('/{id}', [CreditController::class, 'show']);
    Route::post('/updatekredit/{id}', [CreditController::class, 'updateKredit']);
});

Route::prefix('superadmin/transaksi')->group(function () {
    Route::get('/statistik', [StatisticController::class, 'indexHalamanTransaksi']);
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/{id}', [TransactionController::class, 'show']);
    Route::post('/store', [TransactionController::class, 'store']);
});

