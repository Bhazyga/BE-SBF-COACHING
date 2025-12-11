<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Midtrans\SafeNotification;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;

class MidtransNotificationController extends Controller
{

    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }
    public function handle()
    {
        if (request('order_id') && str_contains(request('order_id'), 'payment_notif_test')) {
            Log::info('📦 Received test notification from Midtrans Dashboard.', request()->all());
            return response()->json(['message' => 'Test notification received'], 200);
        }
        try {
            // === AMBIL DATA NOTIF MIDTRANS SEKALI AJA ===
            $notif = new SafeNotification();
            $orderId = $notif->order_id;

            Log::info("Incoming Midtrans Notification: $orderId");

            // ===========================
            //   ROUTING BERDASARKAN ORDER ID
            // ===========================

            // EVENT PAYMENT FLOW
            if (str_contains($orderId, 'ORDER-EVENT')) {
                Log::info("Routing to EventPaymentController");
                return app(EventPaymentController::class)->handleNotification($notif);
            }

            // SUBSCRIPTION / USER PAYMENT FLOW
            if (str_contains($orderId, 'ORDER-USER')) {
                Log::info("Routing to PaymentController");
                return app(PaymentController::class)->handleNotification($notif);
            }

            // JAGA-JAGA
            Log::warning("Unknown order prefix: $orderId");
            return response()->json(['message' => 'Unknown order type'], 400);

        } catch (\Exception $e) {
            Log::error("Unified Midtrans Handler Error: " . $e->getMessage());
            return response()->json(['message' => 'Notification error'], 500);
        }
    }
}
