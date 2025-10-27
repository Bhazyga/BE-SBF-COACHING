<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SubscriberActivationController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\ArticleController;

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
Route::post('/index', [AuthController::class,'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/users', UserController::class);
    Route::get('/belum-aktif', [UserController::class, 'subscriberBelumAktif']);

    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

// ===============================
// 🧕 SANTRI
// ===============================
// Route::apiResource('/santris', SantriController::class);
// Route::get('/santris/{id}', [SantriController::class, 'biodata']);
// Route::get('/santris/{id}/unpaid-items', [SantriController::class, 'getUnpaidItems']); // Unpaid by Santri ID
// Route::put('/activate-santri/{id}', [SantriActivationController::class, 'activate']);

// ===============================
// 👑 SUBSCRIBER MANAGEMENT (Only Admin)
// ===============================
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/subscribers', [SubscriberController::class, 'index']);
    Route::get('/subscribers/{id}', [SubscriberController::class, 'show']);
    Route::put('/subscribers/{id}', [SubscriberController::class, 'update']);
    Route::delete('/subscribers/{id}', [SubscriberController::class, 'destroy']);

    Route::put('/activate-subscriber/{id}', [SubscriberActivationController::class, 'activate']);
});

Route::post('/transactions/token', [PaymentController::class, 'getSnapToken']);
Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']); // Callback URL

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-transactions', [PaymentController::class, 'getUserTransactions']);
    Route::get('/pending-transactions', [PaymentController::class, 'getPendingTransactions']);
    Route::get('/unpaid-items', [PaymentController::class, 'getUnpaidItems']);
    Route::get('/all-transactions', [TransaksiController::class, 'allTransactions']);
});

Route::get('/transactions/{id}', [TransaksiController::class, 'show']); // detail transaksi

Route::get('/dashboard-stats', [DashboardController::class, 'index']);

Route::apiResource('/items', ItemController::class);

Route::get('/articles', [ArticleController::class, 'index']);          // semua artikel
Route::get('/articles/{id}', [ArticleController::class, 'show']);      // detail by ID
Route::get('/articles/slug/{slug}', [ArticleController::class, 'showBySlug']); // detail by slug
Route::get('/articles/author/{slug}', [ArticleController::class, 'filterByAuthor']); // filter by author

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show', 'showBySlug', 'filterByAuthor']);
});

