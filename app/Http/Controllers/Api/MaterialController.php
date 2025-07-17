<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MaterialResource::collection(
        Material::query()->orderBy('id')->paginate(20));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMaterialRequest $request)
    {
        // Retrieve validated form data
    $data = $request->validated();

    // Decode base64 image data
    $base64Image = $request->input('gambar');
    $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
    Log::info('Decoded image data:', ['data' => $decodedImage]);

    // Generate a unique filename for the image
    $filename = uniqid() . '.jpg';
    Log::info('Generated filename:', ['filename' => $filename]);

    // Store the decoded image data in the storage system
    Storage::disk('public')->put('images/' . $filename, $decodedImage);
    Log::info('Image stored at:', ['path' => 'images/' . $filename]);

    // Update the 'gambar' field in the data array with the filename or URL of the stored image
    $data['gambar'] = $filename; // Or you can store the URL, depending on your storage configuration

    // Create Material
    $material = Material::create($data);
    Log::info('Material created:', ['material' => $material]);
        return response(new MaterialResource($material));
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        return new MaterialResource($material);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->validated();


    // Decode base64 image data
    $base64Image = $request->input('gambar');
    $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
    Log::info('Decoded image data:', ['data' => $decodedImage]);

    // Generate a unique filename for the image
    $filename = uniqid() . '.jpg';
    Log::info('Generated filename:', ['filename' => $filename]);

    // Store the decoded image data in the storage system
    Storage::disk('public')->put('images/' . $filename, $decodedImage);
    Log::info('Image stored at:', ['path' => 'images/' . $filename]);

    // Update the 'gambar' field in the data array with the filename or URL of the stored image
    $data['gambar'] = $filename; // Or you can store the URL, depending on your storage configuration



        $material->update($data);

        return new MaterialResource($material);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        $material->delete();

        return response( "", 204);

    }

    public function detailUserBeli(Material $material)
    {
        return new MaterialResource($material);
    }
}
