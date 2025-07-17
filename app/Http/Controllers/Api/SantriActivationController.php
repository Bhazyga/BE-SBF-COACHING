<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\Request;

class SantriActivationController extends Controller
{
    public function activate(Request $request, $id)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
        ]);

        // 1. Cari santri
        $santri = Santri::findOrFail($id);

        // 2. Set grade_id (aktivasi kelas)
        $santri->grade_id = $request->grade_id;
        $santri->save();

        // 3. Update user yang belum punya santri_id
        $user = User::where('email', $santri->email)
            ->whereNull('santri_id')
            ->first();

        if ($user) {
            $user->santri_id = $santri->id;
            $user->save();
        }

        return response()->json([
            'message' => 'Santri berhasil diaktifkan.',
            'santri' => $santri,
            'user' => $user,
        ]);
    }
}
