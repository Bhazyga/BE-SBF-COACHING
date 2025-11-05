<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Subscriber;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use App\Services\Midtrans\SafeNotification;


class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function getSnapToken(Request $request)
    {
        $request->validate([
            'user_name'  => 'required|string',
            'user_email' => 'required|email',
            'amount'     => 'required|numeric|min:1000',
            'item_name'  => 'required|string',
            'item_id'    => 'required|numeric',
            // hapus 'subscriber_id' dari validator karena ambil dari user login
        ]);

        // $subscriberId = $request->user()->subscriber->id ?? null;

        // if (!$subscriberId) {
        //     return response()->json([
        //         'message' => 'User tidak memiliki subscriber aktif'
        //     ], 400);
        // }

    // Gunakan user ID (bukan subscriber ID) di awal
    $userId = $request->user()->id;
    $itemId = $request->item_id;

    $orderId = 'ORDER-USER-' . $userId . '-ITEM-' . $itemId . '-' . uniqid();

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $request->user_name,
                'email'      => $request->user_email,
            ],
            'item_details' => [
                [
                    'id'       => $itemId,
                    'price'    => $request->amount,
                    'quantity' => 1,
                    'name'     => $request->item_name,
                ]
            ],
            'callbacks' => [
                // 'finish' => 'https://279f4c2849ab.ngrok-free.app/payment-finish',
                'finish' => 'https://www.sbf-coaching.com/payment-finish',
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($payload);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ]);
        } catch (\Exception $e) {
            Log::info('Midtrans Server Key: ' . Config::$serverKey);
            Log::info('Payload: ', $payload);
            return response()->json([
                'message' => 'Gagal membuat Snap Token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function handleNotification()
    {
        try {
            $notif = new SafeNotification();
            $orderId = $notif->order_id ?? null;
            $transactionId = $notif->transaction_id ?? null;
            $transactionStatus = $notif->transaction_status ?? null;
            $paymentType = $notif->payment_type ?? null;
            $grossAmount = $notif->gross_amount ?? 0;
            $transactionTime = $notif->transaction_time ?? now();
            $paymentTime = $notif->settlement_time ?? null;
            $statusCode = $notif->status_code ?? null;
            $fullResponse = json_encode($notif->getStatusResponse());
            $orderParts = explode('-', $orderId);
            $userId = $orderParts[2] ?? null;
            $itemId = $orderParts[4] ?? null;
            $existing = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$existing) {
                Transaction::create([
                    'subscriber_id'    => null,
                    'item_id'          => $itemId,
                    'transaction_id'   => $transactionId,
                    'midtrans_order_id'=> $orderId,
                    'jumlah'           => 1,
                    'total_harga'      => $grossAmount,
                    'status'           => $transactionStatus,
                    'payment_type'     => $paymentType,
                    'midtrans_response'=> $fullResponse,
                    'transaction_time' => $transactionTime,
                    'payment_time'     => $paymentTime,
                ]);

                Log::info("Transaction stored: $orderId");
            } else {
                $existing->update([
                    'status'            => $transactionStatus,
                    'payment_type'      => $paymentType,
                    'payment_time'      => $paymentTime,
                    'midtrans_response' => $fullResponse,
                ]);
                Log::info("Transaction updated: $orderId => $transactionStatus");
            }

            if ($transactionStatus === 'settlement') {
                switch ((int) $itemId) {
                    case 1:
                        $monthsToAdd = 1;
                        $planName = '1_month';
                        break;
                    case 2:
                        $monthsToAdd = 6;
                        $planName = '6_months';
                        break;
                    case 3:
                        $monthsToAdd = 12;
                        $planName = '12_months';
                        break;
                    default:
                        $monthsToAdd = 1;
                        $planName = '1_month';
                        break;
                }

                $subscriber = Subscriber::firstOrCreate(
                    ['user_id' => $userId],
                    [
                        'start_date' => now(),
                        'end_date'   => now()->addMonths($monthsToAdd),
                        'plan'       => $planName,
                    ]
                );

                if (!$subscriber->wasRecentlyCreated) {
                    $baseDate = $subscriber->end_date > now()
                        ? \Carbon\Carbon::parse($subscriber->end_date)
                        : now();

                    $subscriber->update([
                        'start_date' => now(),
                        'end_date'   => $baseDate->addMonths($monthsToAdd),
                        'plan'       => $planName,
                    ]);
                }

                Transaction::where('midtrans_order_id', $orderId)
                    ->update(['subscriber_id' => $subscriber->id]);

                $subscriber->update([
                    'transaction_id' => $transactionId,
                ]);



                Log::info("✅ Subscriber {$subscriber->id} diperpanjang {$monthsToAdd} bulan untuk user {$userId}");
            }

            if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                Log::warning("❌ Payment gagal untuk order {$orderId}, status: {$transactionStatus}");
            }

            return response()->json(['message' => 'Notification handled'], 200);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Notification error'], 500);
        }
    }


    public function getPendingTransactions(Request $request)
    {
        $subscriberId = $request->user()->subscriber_id;

        $pendingTransactions = Transaction::with('item')
            ->where('subscriber_id', $subscriberId)
            ->whereNotIn('status', ['paid', 'success'])
            ->orderBy('transaction_time', 'desc')
            ->get();

        return response()->json($pendingTransactions);
    }


    public function getUserTransactions(Request $request)
    {
        $subscriberId = $request->user()->subscriber_id;
        $transactions = Transaction::with('item')
            ->where('subscriber_id', $subscriberId)
            ->orderBy('transaction_time', 'desc')
            ->get();

        return response()->json($transactions);
    }

    public function getUnpaidItems(Request $request)
    {
        $subscriberId = $request->user()->subscriber_id;

        $unpaidItems = Item::where('aktif', 1)
            ->whereNotIn('id', function ($query) use ($subscriberId) {
                $query->select('item_id')
                    ->from('transactions')
                    ->where('subscriber_id', $subscriberId);
            })
            ->get();

        return response()->json($unpaidItems);
    }
}
