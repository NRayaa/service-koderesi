<?php
// ROUTE SUPERADMIN

use App\Http\Controllers\admin\web\archive\ArchiveController;
use App\Http\Controllers\admin\web\dashboard\DashboardController as DashboardDashboardController;
use App\Http\Controllers\admin\web\profile\HistoryController;
use App\Http\Controllers\admin\web\profile\ProfileController;
use App\Http\Controllers\admin\web\resilist\ResiAdminController;
use App\Http\Controllers\admin\web\resilist\ResiAdminFilterController;
use App\Http\Controllers\superadmin\dashboard\DashboardController;
use App\Http\Controllers\superadmin\kredit\CreditController;
use App\Http\Controllers\superadmin\pengguna\UserController;
use App\Http\Controllers\superadmin\StatisticController;
use App\Http\Controllers\superadmin\resilist\WaybillController;
use App\Http\Controllers\superadmin\resilist\WaybillFilterController;
use App\Http\Controllers\superadmin\transaksi\TransactionController;


//ROUTE ADMIN


//ROUTE WORDPRESS
use App\Http\Controllers\admin\wordpress\WaybillWordpressController;
use App\Http\Controllers\superadmin\SettCredit\CreditSettController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//AUTH
Route::post('auth/registerFromAdmin', [\App\Http\Controllers\AuthController::class, 'RegisterFromAdmin']);
Route::post('auth/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('auth/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('auth/page', [\App\Http\Controllers\Auth\UserAuthController::class, 'index'])->middleware('auth:sanctum');

// Route::prefix('superadmin')->middleware('auth:sanctum', 'superadmin')->group(function () {
    Route::prefix('superadmin')->group(function () {
    Route::prefix('waybill')->group(function () {
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

    Route::prefix('pengguna')->group(function() {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/store', [UserController::class, 'store']);
        Route::get('/show/{id}', [UserController::class, 'show']);
        Route::put('/update/{id}', [UserController::class, 'update']);
        Route::delete('/destroy/{id}', [UserController::class, 'destroy']);
    });

    Route::prefix('kredit')->group(function () {
        Route::get('/statistik', [StatisticController::class, 'indexHalamanKredit']);
        Route::get('/', [CreditController::class, 'index']);
        Route::get('/{id}', [CreditController::class, 'show']);
        Route::post('/updatekredit/{id}', [CreditController::class, 'updateKredit']);
    });

    Route::prefix('transaksi')->group(function () {
        Route::get('/statistik', [StatisticController::class, 'indexHalamanTransaksi']);
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::post('/store', [TransactionController::class, 'store']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('/total', [DashboardController::class, 'indexTotal']);
        Route::get('/newestuser', [DashboardController::class, 'indexUser']);
        Route::get('/newesttransaction', [DashboardController::class, 'indexTransaction']);
        Route::get('/newestwaybill', [DashboardController::class, 'indexWaybill']);
    });

    Route::prefix('credit')->group(function () {
        Route::get('/', [CreditSettController::class, 'index']);
        Route::post('/store', [CreditSettController::class, 'store']);
        Route::get('/show/{id}', [CreditSettController::class, 'show']);
        Route::put('/update/{id}', [CreditSettController::class, 'update']);
        Route::delete('/destroy/{id}', [CreditSettController::class, 'destroy']);
    });
});

Route::prefix('admin')->middleware('auth:sanctum', 'admin')->group(function () {
    //list route waybill
    Route::prefix('waybill')->group(function () {
        Route::get('/', [ResiAdminController::class, 'index']);
        Route::post('store', [ResiAdminController::class, 'store']);
        Route::get('show/{id}', [ResiAdminController::class, 'show']);
        Route::put('update/{id}', [ResiAdminController::class, 'update']);
        Route::delete('destroy/{id}', [ResiAdminController::class, 'destroy']);
        Route::put('archive/{id}', [ResiAdminController::class, 'archive']);
        //filter
        Route::get('/filter', [ResiAdminFilterController::class, 'filterByStatus']); // URL: /filter?status=delivered
        //search
        Route::get('/search', [ResiAdminController::class, 'search']);

    });

    //list route archive
    Route::prefix('archive')->group(function () {
        Route::get('/', [ArchiveController::class, 'index']);
        Route::put('/unarchive/{id}', [ArchiveController::class, 'unarchive']);
    });

    //list route profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/update-image', [ProfileController::class, 'uploadImage']);
        Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
        Route::delete('/destroy-profile', [ProfileController::class, 'deleteProfile']);

        //history
        Route::get('/history', [HistoryController::class, 'index']);
    });

    //list route dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardDashboardController::class, 'index']);
        Route::get('/statistic', [DashboardDashboardController::class, 'statistic']);
    });

});

Route::prefix('wordpress')->middleware('wordpress')->group(function () {
    Route::prefix('waybill')->group(function () {
        Route::get('/', [WaybillWordpressController::class, 'index']);
        Route::post('store', [WaybillWordpressController::class, 'store']);
        Route::get('show/{id}', [WaybillWordpressController::class, 'show']);
        Route::put('update/{id}', [WaybillWordpressController::class, 'update']);
        Route::get('newmanifest/{id}', [WaybillWordpressController::class, 'edit']);
        Route::post('updatemanifest/{id}', [WaybillWordpressController::class, 'updateManifest']);
    });
});

