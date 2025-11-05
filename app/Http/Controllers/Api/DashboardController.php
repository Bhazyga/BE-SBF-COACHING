<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'bulan'); // default per bulan

        if ($filter === 'hari') {
            $labelFormat = 'DATE(transaction_time)';
        } elseif ($filter === 'tahun') {
            $labelFormat = 'YEAR(transaction_time)';
        } else {
            $labelFormat = 'DATE_FORMAT(transaction_time, "%Y-%m")';
        }

        $totalUsers = DB::table('users')->count();

        $paidUsers = DB::table('subscribers')
            ->whereDate('end_date', '>=', now())
            ->count();

        $revenue = DB::table('transactions')
            ->whereIn('status', ['success', 'settlement'])
            ->sum('total_harga');

        $chart = DB::table('transactions')
            ->selectRaw("$labelFormat as label")
            ->selectRaw("COUNT(*) as total_transaksi")
            ->selectRaw("SUM(CASE WHEN status IN ('success','settlement') THEN total_harga ELSE 0 END) as total_pemasukan")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return response()->json([
            'stats' => [
                'totalUsers' => $totalUsers,
                'paidUsers' => $paidUsers,
                'revenue' => $revenue,
            ],
            'chart' => $chart,
        ]);
    }
}
