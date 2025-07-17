<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SantriController;
use App\Http\Controllers\Api\SantriActivationController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route untuk API disusun berdasarkan fungsinya:
| - Auth & User
| - Santri (biodata, unpaid items)
| - Payment (Midtrans)
| - Admin & master data (grades, items, teams, dll)
| Semua route yang sensitif pakai middleware auth:sanctum
|--------------------------------------------------------------------------
*/


// ===============================
// 🧑 AUTH & USER
// ===============================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/index', [AuthController::class,'index']); // (??) optional login?

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/users', UserController::class);
    Route::get('/belum-aktif', [UserController::class, 'santriBelumAktif']); // List santri belum aktif
});

// ===============================
// 🧕 SANTRI
// ===============================
Route::apiResource('/santris', SantriController::class);
Route::get('/santris/{id}', [SantriController::class, 'biodata']);
Route::get('/santris/{id}/unpaid-items', [SantriController::class, 'getUnpaidItems']); // Unpaid by Santri ID
Route::put('/activate-santri/{id}', [SantriActivationController::class, 'activate']);

// ===============================
// 💸 PAYMENT (Midtrans)
// ===============================
Route::post('/transactions/token', [PaymentController::class, 'getSnapToken']);
Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']); // Callback URL

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-transactions', [PaymentController::class, 'getUserTransactions']);
    Route::get('/pending-transactions', [PaymentController::class, 'getPendingTransactions']);
    Route::get('/unpaid-items', [PaymentController::class, 'getUnpaidItems']);
    Route::get('/all-transactions', [TransaksiController::class, 'allTransactions']);
});

Route::get('/transactions/{id}', [TransaksiController::class, 'show']); // detail transaksi

// ===============================
// 📊 DASHBOARD
// ===============================
Route::get('/dashboard-stats', [DashboardController::class, 'index']);

// ===============================
// 📦 ITEMS, MATERIALS, TEAMS
// ===============================
Route::apiResource('/items', ItemController::class);
Route::apiResource('/materials', MaterialController::class);
Route::get('/materials/{id}', [MaterialController::class, 'show']);

Route::apiResource('/teams', TeamController::class);
Route::get('/teams/{id}', [TeamController::class, 'show']);

// ===============================
// 🏫 GRADES
// ===============================
Route::prefix('grades')->group(function () {
    Route::get('/', [GradeController::class, 'index']);
    Route::post('/', [GradeController::class, 'store']);
    Route::get('{id}', [GradeController::class, 'show']);
    Route::put('{id}', [GradeController::class, 'update']);
    Route::delete('{id}', [GradeController::class, 'destroy']);
});
