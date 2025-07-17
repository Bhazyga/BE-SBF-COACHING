<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class BangunRumahController extends Controller
{
    public function bangunrumah()
    {
        // Validate $type or perform any additional checks as needed

        // Fetch materials from the database by type
        $materials = Material::get();
        return response()->json(['materials' => $materials], 200);
    }
}
