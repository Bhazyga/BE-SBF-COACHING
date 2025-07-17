<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item; // Pastikan model Item sudah ada

class ItemController extends Controller
{
    // Get all items
    public function index()
    {
        $items = Item::all();
        return response()->json($items);
    }

    // Store new item
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_item' => 'required|string|unique:items,kode_item',
            'nama' => 'required|string',
            'harga' => 'required|numeric',
            'tipe' => 'required|string',
            'deskripsi' => 'nullable|string',
            'aktif' => 'nullable|boolean',
        ]);

        $item = Item::create($validated);

        return response()->json($item, 201);
    }

    // Show single item
    public function show($id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'kode_item' => 'required|string|unique:items,kode_item,' . $id,
            'nama' => 'required|string',
            'harga' => 'required|numeric',
            'tipe' => 'required|string',
            'deskripsi' => 'nullable|string',
            'aktif' => 'nullable|boolean',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    // Delete item
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}
