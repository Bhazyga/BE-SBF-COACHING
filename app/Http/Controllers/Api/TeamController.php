<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TeamResource::collection(
        Team::query()->orderBy('id')->paginate(20));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request)
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

    // Create Team
    $team = Team::create($data);
    Log::info('Team created:', ['Team' => $team]);
        return response(new TeamResource($team));
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return new TeamResource($team);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team)
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



        $team->update($data);

        return new TeamResource($team);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return response( "", 204);

    }

    public function detailUserBeli(Team $team)
    {
        return new TeamResource($team);
    }
}
