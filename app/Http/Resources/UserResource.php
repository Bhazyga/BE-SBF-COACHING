<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    //nge buka wrap data , tadinya harusnya panggil data tapi pake ini nge unwr4p data jadi ga harus deklarasi dat4 dulu
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "email"=> $this->email,
            "role"=> $this->role,
            "created_at"=> $this->created_at->format("Y-m-d H:i:s"),
        ];
    }
}
