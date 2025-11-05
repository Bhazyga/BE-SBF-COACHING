<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with(['subscriber.user', 'item'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($transactions);
    }


    public function allTransactions()
    {
        $transactions = Transaction::with(['subscriber', 'item'])->get(); // pastikan relasi `item` juga ada kalau kamu butuh
        return response()->json($transactions);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transaction = Transaction::with(['subscriber', 'item'])->findOrFail($id);
        return response()->json($transaction);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
