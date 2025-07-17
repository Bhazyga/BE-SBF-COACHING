<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

     public function toArray(Request $request): array
     {
         return [
             "id"=> $this->id,
             "name"=> $this->name,
             "description"=> $this->description,
             "instalink"=> $this->instalink,
             "facebooklink"=> $this->facebooklink,
             "title"=> $this->title,
             "gambar"=> $this->gambar,
             "created_at"=> $this->created_at->format("Y-m-d H:i:s"),
         ];
    }
}
