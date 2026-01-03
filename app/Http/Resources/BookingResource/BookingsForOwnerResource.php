<?php

namespace App\Http\Resources\BookingResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ApartmentResource\ApartmentDetailResource;
use App\Http\Resources\UserResource\UserDetailResource;

class BookingsForOwnerResource extends JsonResource
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
            'user' => new UserDetailResource($this->user),
            'apartment' => new ApartmentDetailResource($this->apartment),
            'from_date' => $this->from_date->format('Y-m-d'),
            'to_date' => $this->to_date->format('Y-m-d'),
            'status' => $this->status,
        ];
    }
}
