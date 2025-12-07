<?php

namespace App\Http\Resources\ApartmentResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'governorate' => $this->city->governorate->name,
            'city' => $this->city->name,
            'price' => $this->price,
            'area' => $this->area,
            'rooms' => $this->rooms,
            'description' => $this->description,
            'photo' => $this->photo_url,
        ];
    }
}
