<?php

namespace App\Http\Resources\BookingResource;

use App\Http\Resources\ApartmentResource\ApartmentDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingsForUserResource extends JsonResource
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
            'apartment' => new ApartmentDetailResource($this->apartment),
            'from_date' => $this->from_date->format('Y-m-d'),
            'to_date' => $this->to_date->format('Y-m-d'),
            'status' => $this->status
        ];
    }
}
