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
            'item_id'     => 'nullable|string|numeric',
        ]);

        $orderId = uniqid('ORDER-');

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
                    'id'       => $request->item_id ?? 'custom',
                    'price'    => $request->amount,
                    'quantity' => 1,
                    'name'     => $request->item_name,
                ]
            ],
            'callbacks' => [
                'finish' => 'https://azziyadahklender.id', // GANTI dengan URL React lo
            ]
        ];


        try {
            $snapToken = Snap::getSnapToken($payload);

            return response()->json([
                'snap_token' => $snapToken,
                $orderId = 'ORDER-' . ($request->user_id ?? '0') . '-' . ($request->item_id ?? 'custom') . '-' . uniqid(),
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


        // Inisialisasi ulang config Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            $notif = new Notification();

            $transactionStatus = $notif->transaction_status;
            $paymentType = $notif->payment_type;
            $orderId = $notif->order_id;
            $grossAmount = $notif->gross_amount;
            $transactionTime = $notif->transaction_time;
            $paymentTime = $notif->settlement_time ?? null;
            $statusCode = $notif->status_code;
            $fullResponse = json_encode($notif);
            $orderParts = explode('-', $notif->order_id);
            $santriId = $orderParts[1] ?? null;
            $itemId = $orderParts[2] ?? null;

            // Simpan ke database
            Transaction::create([
                'santri_id' => $santriId,
                'item_id'   => $itemId,
                // 'santri_id'           => null,
                // 'item_id'             => null,
                'transaction_id'      => $notif->transaction_id,
                'midtrans_order_id'   => $orderId,
                'jumlah'              => 1,
                'total_harga'         => $grossAmount,
                'status'              => $transactionStatus,
                'payment_type'        => $paymentType,
                'midtrans_response'   => $fullResponse,
                'transaction_time'    => $transactionTime,
                'payment_time'        => $paymentTime,
            ]);

            return response()->json(['message' => 'Notification handled'], 200);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Notification error'], 500);
        }
    }

    public function getPendingTransactions(Request $request)
    {
        $santriId = $request->user()->santri_id;

        $pendingTransactions = Transaction::with('item')
            ->where('santri_id', $santriId)
            ->whereNotIn('status', ['paid', 'success'])
            ->orderBy('transaction_time', 'desc')
            ->get();

        return response()->json($pendingTransactions);
    }


    public function getUserTransactions(Request $request)
    {
        $santriId = $request->user()->santri_id;
        $transactions = Transaction::with('item')
            ->where('santri_id', $santriId)
            ->orderBy('transaction_time', 'desc')
            ->get();

        return response()->json($transactions);
    }

    public function getUnpaidItems(Request $request)
    {
        $santriId = $request->user()->santri_id;

        $unpaidItems = Item::where('aktif', 1)
            ->whereNotIn('id', function ($query) use ($santriId) {
                $query->select('item_id')
                    ->from('transactions')
                    ->where('santri_id', $santriId);
            })
            ->get();

        return response()->json($unpaidItems);
    }



}
