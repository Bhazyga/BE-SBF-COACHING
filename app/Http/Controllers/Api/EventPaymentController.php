<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventExtraLink;
use Illuminate\Support\Facades\DB;

class EventPaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function getSnapToken(Request $request)
    {
        $request->validate([
            'event_id' => 'required|numeric',
        ]);

        $user  = $request->user();
        $userId = $user->id;
        $user_name = $user->name;
        $user_email = $user->email;

        $event = Event::findOrFail($request->event_id);

        // ============================================================
        // 🔥 ADD: CEK APAKAH SUDAH ADA PAYMENT PENDING UNTUK EVENT INI
        // ============================================================
        $existingPayment = EventPayment::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending']) // hanya pending yg dicegah
            ->first();

        if ($existingPayment) {

            Log::info("Reuse pending payment {$existingPayment->order_id} untuk user $userId");

            // Rebuild payload sesuai order lama
            $payload = [
                'transaction_details' => [
                    'order_id'     => $existingPayment->order_id,
                    'gross_amount' => $existingPayment->amount,
                ],
                'customer_details' => [
                    'first_name' => $user_name,
                    'email'      => $user_email,
                ],
                'item_details' => [
                    [
                        'id'       => $event->id,
                        'price'    => $event->price,
                        'quantity' => 1,
                        'name'     => $event->title,
                    ]
                ],
                'callbacks' => [
                    // prod
                    'finish' => 'https://www.sbf-coaching.com/user/payment-finish',

                    // local
                    // 'finish' => 'https://a2225a04d2ba.ngrok-free.app/payment-finish',
                ]
            ];

            try {
                $snapToken = Snap::getSnapToken($payload);

                $existingPayment->update([
                    'snap_token' => $snapToken
                ]);

                return response()->json([
                    'snap_token' => $snapToken,
                    'order_id'   => $existingPayment->order_id,
                    'status'     => 'reuse_pending',
                ]);

            } catch (\Exception $e) {
                Log::error('Midtrans Error (REUSE EVENT): ' . $e->getMessage());
                return response()->json([
                    'message' => 'Gagal membuat Snap Token dari pending lama',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }

        // ============================================================
        // 🔥 ORIGINAL CODE (TIDAK DIUBAH)
        // ============================================================

        // Order id format mirip subscription
        $orderId = 'ORDER-EVENT-' . $event->id . '-USER-' . $user->id . '-' . uniqid();

        // Simpan ke tabel event_payments
        EventPayment::create([
            'order_id'   => $orderId,
            'user_id'    => $userId,
            'event_id'   => $event->id,
            'amount'     => $event->price,
            'status'     => 'pending',
            'transaction_time' => now(),
        ]);

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $event->price,
            ],
            'customer_details' => [
                'first_name' => $user_name,
                'email'      => $user_email,
            ],
            'item_details' => [
                [
                    'id'       => $event->id,
                    'price'    => $event->price,
                    'quantity' => 1,
                    'name'     => $event->title,
                ]
            ],
            'callbacks' => [
                'finish' => 'https://a2225a04d2ba.ngrok-free.app/payment-finish',
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($payload);

            EventPayment::where('order_id', $orderId)
            ->update(['snap_token' => $snapToken]);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
                'status'     => 'new_payment',
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Error (EVENT): ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat Snap Token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function handleNotification($notif)
    {
        try {
            // ambil data dari SafeNotification wrapper (sudah diparsing di caller)
            $orderId           = $notif->order_id ?? null;
            $transactionId     = $notif->transaction_id ?? null;
            $transactionStatus = $notif->transaction_status ?? null;
            $paymentType       = $notif->payment_type ?? null;
            $grossAmount       = $notif->gross_amount ?? 0;
            $transactionTime   = $notif->transaction_time ?? now();
            $paymentTime       = $notif->settlement_time ?? null;

            // amankan: full response (batas panjang supaya gak overflow kolom)
            $rawResponse = $notif->getStatusResponse();
            $fullResponse = json_encode($rawResponse);
            $maxLen = 65500; // safe margin untuk kolom TEXT / LONGTEXT tergantung DB
            if (strlen($fullResponse) > $maxLen) {
                $fullResponse = substr($fullResponse, 0, $maxLen);
            }

            Log::info("EventNotification received: order={$orderId} status={$transactionStatus} txId={$transactionId}");

            if (!$orderId) {
                Log::warning("Missing order_id in notification payload");
                return response()->json(['message' => 'Invalid notification'], 400);
            }

            // cari payment yang sudah dibuat saat getSnapToken
            $payment = EventPayment::where('order_id', $orderId)->first();

            if (!$payment) {
                Log::error("EventPayment not found for order_id: $orderId");
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Update basic info (non-blocking)
            $payment->update([
                'transaction_id'    => $transactionId,
                'status'            => $transactionStatus,
                'payment_type'      => $paymentType,
                'payment_time'      => $paymentTime,
                'midtrans_response' => $fullResponse,
            ]);

            // SUCCESS (settlement) -> create/update registration & send extra-link email
            if ($transactionStatus === 'settlement') {
                // buat/update registration atomically
                DB::beginTransaction();
                try {
                    $registration = EventRegistration::where('user_id', $payment->user_id)
                        ->where('event_id', $payment->event_id)
                        ->first();

                    if (!$registration) {
                        $registration = EventRegistration::create([
                            'user_id'           => $payment->user_id,
                            'event_id'          => $payment->event_id,
                            'payment_status'    => 'paid',
                            'paid_at'           => now(),
                            'midtrans_order_id' => $orderId,
                            'extra_link_sent'   => false,
                        ]);
                    } else {
                        $registration->update([
                            'payment_status'    => 'paid',
                            'paid_at'           => now(),
                            'midtrans_order_id' => $orderId,
                        ]);
                    }

                    // send email (jangan biarkan error email memblokir notifikasi — tangani secara terpisah)
                    try {
                        $event = Event::find($payment->event_id);
                        $user  = \App\Models\User::find($payment->user_id);

                        if ($event && $user && $user->email) {
                            Mail::to($user->email)->send(new \App\Mail\EventExtraLink($event));

                            // hanya set extra_link_sent = true bila pengiriman berhasil
                            $registration->update(['extra_link_sent' => true]);
                            Log::info("Event extra link email sent to user {$user->id} for event {$event->id}");
                        } else {
                            Log::warning("Cannot send extra link email: missing event or user email. event_id={$payment->event_id}, user_id={$payment->user_id}");
                        }
                    } catch (\Exception $mailEx) {
                        // log error, tapi jangan rollback DB — kita ingin tetap menyimpan bahwa pembayaran sukses
                        Log::error("Failed to send event extra link email: " . $mailEx->getMessage());
                        // optional: simpan kolom flag/kolom error agar bisa retry
                    }

                    // tandai payment sebagai sukses (double-check)
                    $payment->update(['status' => 'success']);

                    DB::commit();
                } catch (\Exception $dbEx) {
                    DB::rollBack();
                    Log::error("DB error while processing event settlement: " . $dbEx->getMessage());
                    // meskipun DB gagal, jangan crash notifikasi Midtrans — return 500 sehingga Midtrans bisa retry
                    return response()->json(['message' => 'DB error while processing notification'], 500);
                }

                Log::info("User {$payment->user_id} sukses bayar event {$payment->event_id}");
            }

            // FAILED states
            if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $payment->update(['status' => 'failed']);
                Log::warning("Event payment failed: $orderId ($transactionStatus)");
            }

            return response()->json(['message' => 'Notification processed'], 200);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification (EVENT) Error: ' . $e->getMessage());
            return response()->json(['message' => 'Notification error'], 500);
        }
    }


    public function getUserPayments(Request $request)
    {
        $userId = $request->user()->id;

        $registrations = EventRegistration::with(['event', 'payment'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reg) {

                // === STATUS EVENT ===
                if ($reg->event->is_paid == false) {
                    // Event gratis
                    $status = "success";
                    $paidAt = $reg->created_at;
                } else {
                    // Event berbayar
                    $status = $reg->payment?->status ?? "pending";
                    $paidAt = $reg->payment?->payment_time;
                }

                return [
                    'id'            => $reg->id,
                    'event_id'      => $reg->event_id,
                    'event'         => $reg->event,
                    'status'        => $status,
                    'paid_at'       => $paidAt,
                    'payment_type'  => $reg->payment?->payment_type,
                    'order_id'      => $reg->payment?->order_id,
                    'is_free'       => !$reg->event->is_paid,
                ];
            });

        return response()->json($registrations);
    }


    public function getPendingPayments(Request $request)
    {
        $payments = EventPayment::with('event')
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->get();

        return response()->json($payments);
    }
}
