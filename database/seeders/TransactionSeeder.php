<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::create([
            'santri_id'           => 1, // pastikan ada data santri_id 1
            'item_id'             => 11, // pastikan ada data item_id 1
            'transaction_id'      => Str::uuid(), // UUID dummy
            'midtrans_order_id'   => 'ORDER-' . Str::random(10),
            'jumlah'              => 1,
            'total_harga'         => 250000,
            'status'              => 'pending',
            'payment_type'        => 'bank_transfer',
            'midtrans_response'   => [
                'transaction_id' => Str::uuid(),
                'order_id'       => 'ORDER-' . Str::random(10),
                'gross_amount'   => 250000,
                'payment_type'   => 'bank_transfer',
                'transaction_status' => 'settlement',
                'va_numbers'     => [['bank' => 'bca', 'va_number' => '1234567890']],
            ],
            'transaction_time'    => Carbon::now(),
            'payment_time'        => Carbon::now()->addMinutes(5),
        ]);
    }
}
