<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    // Ambil semua data grades
    public function index()
    {
        $grades = Grade::all();
        return response()->json($grades);
    }

    // Simpan grade baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas'   => 'required|string|max:255',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        $grade = Grade::create([
            'nama_kelas'   => $request->nama_kelas,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        return response()->json([
            'message' => 'Kelas berhasil ditambahkan.',
            'grade'   => $grade,
        ], 201);
    }

    // Ambil 1 data grade berdasarkan ID
    public function show($id)
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return response()->json(['message' => 'Kelas tidak ditemukan.'], 404);
        }

        return response()->json($grade);
    }

    // Update grade
    public function update(Request $request, $id)
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return response()->json(['message' => 'Kelas tidak ditemukan.'], 404);
        }

        $request->validate([
            'nama_kelas'   => 'required|string|max:255',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        $grade->update([
            'nama_kelas'   => $request->nama_kelas,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        return response()->json([
            'message' => 'Kelas berhasil diperbarui.',
            'grade'   => $grade,
        ]);
    }

    // Hapus grade
    public function destroy($id)
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return response()->json(['message' => 'Kelas tidak ditemukan.'], 404);
        }

        $grade->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus.']);
    }
}
