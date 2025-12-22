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
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRegistrationController;
use App\Http\Controllers\Api\EventPaymentController;
use App\Http\Controllers\Api\MidtransNotificationController;

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

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/belum-aktif', [UserController::class, 'subscriberBelumAktif']);

    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

// ===============================
// 👑 SUBSCRIBER MANAGEMENT (Only Admin)
// ===============================
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('/users', UserController::class);
    Route::put('/users/{user}/password', [UserController::class, 'adminUpdatePassword']);
    Route::get('/subscribers', [SubscriberController::class, 'index']);
    Route::get('/subscribers/{id}', [SubscriberController::class, 'show']);
    Route::put('/subscribers/{id}', [SubscriberController::class, 'update']);
    Route::delete('/subscribers/{id}', [SubscriberController::class, 'destroy']);
    Route::put('/activate-subscriber/{id}', [SubscriberActivationController::class, 'activate']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/me', [UserController::class, 'updateMe']);
    Route::put('/me/password', [UserController::class, 'updateMyPassword']);
});


Route::middleware('auth:sanctum')->post('/transactions/token', [PaymentController::class, 'getSnapToken']);




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-transactions', [PaymentController::class, 'getUserTransactions']);
    Route::get('/pending-transactions', [PaymentController::class, 'getPendingTransactions']);
    Route::get('/unpaid-items', [PaymentController::class, 'getUnpaidItems']);
    Route::get('/transactions', [TransaksiController::class, 'index']);
    Route::get('/all-transactions', [TransaksiController::class, 'allTransactions']);
    Route::get('/transactions/{id}', [TransaksiController::class, 'show']); // detail transaksi
});


Route::get('/dashboard', [DashboardController::class, 'index']);

Route::apiResource('/items', ItemController::class);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/homepage', [ArticleController::class, 'ArticlesHomeAndHighlight']);

    // ==================== FREE ARTICLES ====================

Route::get('/articles/free', [ArticleController::class, 'freeArticles']);
Route::get('/articles/free/author/{slug}', [ArticleController::class, 'filterFreeByAuthor'])
    ->where('slug', '[A-Za-z0-9-_]+');
Route::get('/articles/free/category/{slug}', [ArticleController::class, 'filterFreeByCategory'])
    ->where('slug', '[A-Za-z0-9-_]+');

    // ==================== Ends FREE ARTICLES ====================


Route::middleware(['auth:sanctum', 'subscriber'])->group(function () {

});

    // ==================== Start Area Teknis Premium Only -> artikel & video Start  ====================

// ---------- VIDEO ----------
Route::prefix('videos')->group(function () {
    Route::get('/', [ArticleController::class, 'videoArticles']);
    Route::get('/author/{slug}', [ArticleController::class, 'filterVideoByAuthor']);
    Route::get('/category/{slug}', [ArticleController::class, 'filterVideoByCategory']);
    Route::get('/{slug}', [ArticleController::class, 'showVideoBySlug']);
});


Route::get('/articles/premium', [ArticleController::class, 'premiumArticles']);
Route::get('/articles/author/{slug}', [ArticleController::class, 'filterByAuthor']);
Route::get('/articles/category/{slug}', [ArticleController::class, 'filterAreaTeknisByCategory'])
    ->where('slug', '[A-Za-z0-9-_]+');

Route::get('/articles/{id}', [ArticleController::class, 'show'])
->where('id', '[0-9]+');
Route::get('/articlespremiumpreview/{slug}', [ArticleController::class, 'showPremiumPreviewBySlug'])
    ->where('slug', '[A-Za-z0-9-_]+');
Route::middleware('auth:sanctum')->get('/articlespremium/{slug}', [ArticleController::class, 'showPremiumBySlug'])
    ->where('slug', '[A-Za-z0-9-_]+');
Route::get('/articles/{slug}', [ArticleController::class, 'showBySlug'])
    ->where('slug', '[A-Za-z0-9-_]+');

        // ==================== Ends Area Teknis -> artikel & video Start  ====================


Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('articles', ArticleController::class)
        ->except(['show']);
});

        // ==================== Start Events Start  ====================


Route::prefix('admin')->group(function () {
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{slug}', [EventController::class, 'show']);
    Route::put('/events/{slug}', [EventController::class, 'update']);
    Route::delete('/events/{slug}', [EventController::class, 'destroy']);
});

// Public events endpoint (tanpa login)
Route::get('/events', [EventController::class, 'publicIndex']);
Route::get('/events/{slug}', [EventController::class, 'detailBySlug']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/event/register', [EventRegistrationController::class, 'register']);
    Route::get('/event/registration-status/{event_id}', [EventRegistrationController::class, 'registrationStatus']);
    Route::post('/event/payment/get-snap-token', [EventPaymentController::class, 'getSnapToken']);
    Route::get('/event/payment/user-payments', [EventPaymentController::class, 'getUserPayments']);
    Route::get('/event/payment/pending', [EventPaymentController::class, 'getPendingPayments']);
});
// Route::post('/event/payment/notification', [EventPaymentController::class, 'handleNotification']); //callback

        // ==================== Ends Events Ends   ====================



Route::get('/user/subscriptions', [SubscriptionController::class, 'index'])->middleware('auth:sanctum');



Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle']); //callback
// Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']); // Callback URL
