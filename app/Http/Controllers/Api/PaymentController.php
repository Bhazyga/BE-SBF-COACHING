<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
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
            'user_name'   => 'required|string',
            'user_email'  => 'required|email',
            'amount'      => 'required|numeric|min:1000',
            'item_name'   => 'required|string',
            'item_id'     => 'required|numeric',
            'subscriber_id'   => 'required|exists:subscriber,id',  // validasi subscriber_id
        ]);

        // $subscriberId = $request->user_id ?? null;
        $subscriberId = $request->subscriber_id;  // ambil dari subscriber_id, bukan user_id
        $itemId = $request->item_id;


        $orderId = 'ORDER-' . $subscriberId . '-' . $itemId . '-' . uniqid();

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
                'finish' => 'https://your-react-app-url.com/payment-finish',
            ]
        ];


        try {
            $snapToken = Snap::getSnapToken($payload);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat Snap Token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function handleNotification(Request $request)
    {
        Log::info('Incoming Midtrans Notification Payload:', $request->all());

        // Setup Midtrans config lagi (jaga-jaga kalau belum di-boot)
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

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
            $fullResponse = json_encode($notif);

            // Extract subscriber_id dan item_id dari order_id jika pakai format custom (contoh: INV-123-456)
            $orderParts = explode('-', $orderId);
            $subscriberId = $orderParts[1] ?? null;
            $itemId = $orderParts[2] ?? null;

            // Cek apakah transaksi ini sudah ada (hindari duplikasi)
            $existing = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$existing) {
                Transaction::create([
                    'subscriber_id'         => $subscriberId,
                    'item_id'           => $itemId,
                    'transaction_id'    => $transactionId,
                    'midtrans_order_id' => $orderId,
                    'jumlah'            => 1,
                    'total_harga'       => $grossAmount,
                    'status'            => $transactionStatus,
                    'payment_type'      => $paymentType,
                    'midtrans_response' => $fullResponse,
                    'transaction_time'  => $transactionTime,
                    'payment_time'      => $paymentTime,
                ]);
                Log::info("Transaction successfully stored to DB: OrderID = $orderId");
            } else {
                $existing->update([
                    'status'            => $transactionStatus,
                    'payment_type'      => $paymentType,
                    'payment_time'      => $paymentTime,
                    'midtrans_response' => $fullResponse,
                ]);
                Log::info("Transaction updated: OrderID = $orderId, Status = $transactionStatus");
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
